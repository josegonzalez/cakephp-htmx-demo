# Infinite scroll demo

Start with generating a database:

```shell
bin/cake bake migration CreateArticles title:string content:text photo_url:string
```

Next, lets migrate our database:

```shell
bin/cake migrations migrate
```

I'll create an articles seed:

```shell
bin/cake bake seed Articles
```

I'll also require fakerphp/faker to generate seed data:

```shell
composer require fakerphp/faker
```

The code for generating data looks a bit like this:

```php
foreach (range(1, 100) as $i) {
    $faker = Factory::create();
    $data[] = [
        'title' => $faker->sentence(),
        'content' => $faker->paragraphs(5, true),
        'photo_url' => "https://picsum.photos/seed/{$i}/600"
    ];
}
```

And then run that seed:

```shell
bin/cake migrations seed
```

Now that we have some dummy data, lets generate some code. I'll start with the model:

```shell
bin/cake bake model articles
```

Now lets generate a controller with just an index action:

```shell
bin/cake bake controller --actions index articles
```

And create a file containing the index template

```shell
touch templates/Articles/index.php
```

I'll show off the contents of that file next:

```php
<h1>Articles</h1>
<div id="articles">
    <?php foreach ($articles as $i => $article) : ?>
        <div class="article">
            <h2><?= $article->title ?></h2>
            <a class="image-container" href="<?= $article->photo_url ?>">
                <img width="300" height="300" src="<?= $article->photo_url ?>" />
            </a>

            <p><?php $article->content ?></p>

            <?php if ($this->Paginator->hasNext() && $i === count($articles) - 1) : ?>
                <span hx-get="<?= $this->Paginator->generateUrl(['page' => $this->Paginator->current() + 1]) ?>" hx-swap="beforeend" hx-target="#articles" hx-select=".article" hx-trigger="revealed">
                </span>
            <?php endif ?>

        </div>
    <?php endforeach; ?>
</div>
```

What happens here is that when the last article in the list is displayed, we will also render a special span. When that span is revealed - or shown in the browser viewport - it will trigger an `hx-get`. The response of the hx-get is added to the end - via `hx-swap` - of the `hx-target` specified, or `#articles`. In this particular case, we're also selecting _just_ the `.article` elements from the response to append vs the entire response.

Additionally, lets simplify our default layout. I've included a vendored `htmx.min.js`:

```php
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <title>HTMX Demo</title>
    <?= $this->Html->css(['infinite-style']) ?>
    <?= $this->Html->script(['htmx.min.js']) ?>
</head>
<body>
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
</body>
</html>
```

If you browse to the page, it should show infinite scrolling - so long as there are elements available.

That said, we still display the containing layout, which can be frustrating. Rather than do this, we can disable the layout automatically for all `htmx` requests by including the following snippet in the `ArticlesController:index()` action:

```shell
if ($this->request->getHeaderLine('HX-Request') === "true") {
    $this->viewBuilder()->disableAutoLayout();
}
```

The above could also be added to `AppController::initialize()` if you'd like it to apply everywhere.

---

How does this change with the `cake-htmx` plugin?

First, lets install and load the plugin:

```shell
composer require zunnu/cake-htmx
bin/cake plugin load CakeHtmx
```

This plugin provides a component for the majority of user interactions with the plugin. We'll load that up in our `AppController:initialize()`:

```php
$this->loadComponent('CakeHtmx.Htmx');
```

As far as changes to the controller, rather than checking a a request header line, the plugin adds a request detector for htmx which we can use:

```shell
if ($this->getRequest()->is('htmx')) {
    $this->viewBuilder()->disableAutoLayout();
}
```

The other interesting thing here is that the plugin proposes using View Blocks for managing what gets displayed for various responses. I think this is pretty neat, though I'll show an alternative in a second. This will allow you to reuse content without needing to make major changes to your plugin. We'll start by specifying the block to show:

```php
$this->Htmx->setBlock("articles");
```

The `articles` block here corresponds with the `hx-target` we previously specified. If we know that the only items shown are the items within the `hx-target`, we could instead just respect the specified `hx-target`:

```php
$this->Htmx->setBlock($this->Htmx->getTarget());
```

Finally, we would wrap our previous foreach loop with view blocks:

```php
<?php $this->start('articles'); ?>
<?php foreach ($articles as $i => $article) : ?>
    <div class="article">
    ...
    </div>
<?php endforeach; ?>
<?php $this->end(); ?>
<?= $this->fetch('articles'); ?>
```

Note that we need to print out the articles. And thats it, the demo is exactly the same.

---

An alternative to viewblocks - which I think is a bit heavy-weight - would be to use elements for rendering the view. Lets start by separating out the guts of what we want to show into an element:

```shell
touch templates/element/htmx/articles/index.php
```

The contents of this file are the for-loop within the initial `index.php` template:

```php
<?php foreach ($articles as $i => $article) : ?>
    <div class="article">
        <h2><?= $article->title ?></h2>
        <a class="image-container" href="<?= $article->photo_url ?>">
            <img width="300" height="300" src="<?= $article->photo_url ?>" />
        </a>

        <p><?php $article->content ?></p>

        <?php if ($this->Paginator->hasNext() && $i === count($articles) - 1) : ?>
            <span hx-get="<?= $this->Paginator->generateUrl(['page' => $this->Paginator->current() + 1]) ?>" hx-swap="beforeend" hx-target="#articles" hx-select=".article" hx-trigger="revealed">
            </span>
        <?php endif ?>
    </div>
<?php endforeach; ?>
```

Our index file becomes the following:

```php
<h1>Articles</h1>
<div id="articles">
    <?= $this->element('htmx/articles/index') ?>
</div>
```

And now we can change the template path and set the template to use to our new element instead of specifying a view block:

```php
$this->viewBuilder()->setTemplatePath('element');
$this->viewBuilder()->setTemplate('htmx/articles/index');
```

This method would allow us to bypass the rest of the original template, which can be helpful in cases where we have more complex templates. The nice thing about this is that we can now reuse that element in other places, or if our controller logic has a of unrelated logic, use a separate action altogether that _only_ renders the htmx code.

---

One common feature folks build into their applications is search. Typically you're searching a database, filtering results and returning relevant results to users. We'll start by updating the default index page to add search.

Since I am building on the existing demo codebase, I'll use the name `search` for my action. I'll add the following function there.

```php
public function search()
{
    $query = $this->Articles->find();
    $articles = $this->paginate($query);

    $this->set(compact('articles'));
}
```

The default index page just paginates, but we want to involve some form of search. I could create a custom finder, but for now, I’ll modify the query object directly to do the query.

```php
$query = $this->Articles->find();
$q = $this->request->getQuery('q');
if (!empty($q)) {
    $query = $query->where([
        'title LIKE' => '%' . $q . '%',
    ]);
}
$articles = $this->paginate($query);
```

I’m also going to set the layout to the old, default layout. This is purely a cosmetic change. The only addition I have to it is including the htmx library.

```php
$this->viewBuilder()->setLayout('old_default');
```

Finally, I use the detector to toggle the htmx request, disable the layout, and use an element for the template.

```php
if ($this->request->is('htmx')) {
    $this->viewBuilder()->disableAutoLayout();
    $this->viewBuilder()->setTemplatePath('element');

    $elements = $this->request->getHeader('Cake-Element');
    $element = count($elements) > 0 ? $elements[0] : 'search';
    $this->viewBuilder()->setTemplate('htmx/articles/' . $element);
}
```

A difference here from our previous method is that I’m respecting the Cake-Element header for selecting the template. HTMX allows users to add extra headers to their requests, and we’re abusing that to decide what to display for the user. You could add any headers you want to the request being made.

One possible improvement would be to automatically disable the layout and set the template path in the `AppController::initialize()` function. This assumes you always want to support htmx in this sort of way.

To skip a bunch of work, I’ll be baking the search action and then customizing it. For our demo, I’ll use the `articles#index` action, but of course If you have another pre-existing template, feel free to modify that.

```shell
bin/cake bake template articles index search
```

I'll add a search input control to the top of the page. In the previous demo, we used a trigger that occured when the element was `revealed`. In this case, we want to only trigger a search on `keyup`, when the input was changed, with a delay of 500 milliseconds. This will make the page a bit more responsive, as otherwise we'll trigger search requests constantly.

Additionally, I've added custom headers with the `hx-headers` property. This takes a json object, and injects any specified headers on the request. To expand on the element selection approach, if your page has a few different htmx-related actions that can be performed, you _could_ abuse this as a way to key into different code paths in your action. The alternative of course is to use different actions completely, but the path you choose is up to you.

We also want to update the url bar to include the query via the `hx-replace-url` trigger. This provides users of our application the ability to save search requests so they can share them. You could also use `hx-push-url` to construct a new history entry in the browser history if thats desirable.

```php
<?= $this->Form->control('q', [
    'label' => false,
    'placeholder' => 'Search...',
    'hx-get' => $this->Url->build(['action' => 'search']),
    'hx-target' => 'tbody',
    'hx-trigger' => 'keyup changed delay:500ms',
    'hx-headers' => json_encode(['Cake-Element' => 'search']),
    'hx-replace-url' => 'true',
]) ?>
```

Similar to our previous version, I'll use an element to store the actual code I want to render for the htmx request.

```php
<?= $this->element('htmx/articles/search') ?>
```

```php
<?php foreach ($articles as $article) : ?>
    <tr>
        <td><?= $this->Number->format($article->id) ?></td>
        <td><?= h($article->title) ?></td>
        <td><?= h($article->photo_url) ?></td>
        <td class="actions">
            <?= $this->Html->link(__('View'), ['action' => 'view', $article->id]) ?>
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $article->id]) ?>
            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $article->id], ['confirm' => __('Are you sure you want to delete # {0}?', $article->id)]) ?>
        </td>
    </tr>
<?php endforeach; ?>
```

We could have also used a View Block or avoided both by using the `hx-select`  attribute to only include certain elements in our swap and performing the logic entirely on the client-side. I recommend weighing the complexity of each method as you use HTMX, but generally standardizing on one in order to make it easy to work across your codebase.

---

A common use-case in an application is to run some long-running process and keep a user up to date on the status. Avoiding this in the browser is important, as we don't want timeouts to occur or tie the actual work to the remote user's browser not being closed. A common use-case is running said work in a queue, which we can the CakePHP Queue plugin for. Work would get processed on some message bus, and then status displayed back to users in some fashion.

For this following demo, we won't be building a queue job, but we will be polling the CakePHP app for status of something. In fact, we'll be polling via HTMX - hurray - and showing off the results in the UI. We will be building the polling site I showed off for the raffle. To start, lets create our `RaffleController` via bake:

```shell
# make an empty controller
bin/cake bake controller --no-actions raffle
```

Next, we'll add a simple index action. For our demo, we'll specify using the `old_default` layout, but otherwise it's empty.

```php
public function index()
{
    $this->viewBuilder()->setLayout('old_default');
}
```

Our view for it is pretty straightforward. It's essentially a form:

```php
<div class="raffle view content">
    <h3>Raffle Time</h3>
    <?= $this->Form->create(null, [
        'hx-post' => $this->Url->build(['action' => 'start_picker']),
        'hx-target' => '.winner',
        'hx-swap' => 'innerHTML',
    ]) ?>
    <fieldset>
        <legend><?= __('Choose a minimum number') ?></legend>
        <?= $this->Form->control('min', ['label' => false, 'placeholder' => 'Minimum number']) ?>
        <legend><?= __('Choose a maximum number') ?></legend>
        <?= $this->Form->control('max', ['label' => false, 'placeholder' => 'Maximum number']) ?>
    </fieldset>
    <?= $this->Form->button(__('Select a winner')) ?>
    <?= $this->Form->end() ?>
    <div class="winner">
    </div>
</div>
```

This form allows us to pick a minimum and maximum number to randomly choose from. It gets submitted as a POST request by specifying the url as `hx-post`. The response is written to the div with the `.winner` class on it. HTMX provides a variety of ways to swap the contents of the response with the target - we previously specified `beforeend`, but if left unspecified, the default is `innerHTML`.

Other than not having a `url`, this form is more or less the same as any other form created by the `FormHelper`.

Our `start_picker` action is also pretty straightforward. It essentially will respond with the html we need and nothing else. I'll disable the autolayout, though we could have also disabled it in our controller whenever we detect an `htmx` request.

One other thing it will do is set the posted min/max values as values for the template.

```php
public function startPicker()
{
    $this->viewBuilder()->disableAutoLayout();
    $this->set('min', $this->request->getData('min'));
    $this->set('max', $this->request->getData('max'));
}
```

In our template, we will use long-polling as a mechanism for checking for a raffle winner. Long-polling is a very old method of fetching results from a webserver. Alternatives that HTMX supports are Server Sent Events or Websockets. While we can build a CakePHP app that supports these, they aren't trivial to do, and so I'll rely on long-polling for now.

```php
<?= $this->Html->div('no-winner', sprintf('Polling every 2 seconds for users between %s and %s', $min, $max), [
    'hx-get' => $this->Url->build(['action' => 'choose_winner']),
    'hx-vals' => json_encode([
        'min' => $min,
        'max' => $max
    ]),
    'hx-trigger' => 'every 2s',
]); ?>
```

Our template file spits out a div that triggers a GET request to our `choose_winner` action. We've added extra querystring arguments via `hx-vals`. While these could have been added to the `hx-get` url directly, I display this method here to show that HTMX has a method of adding query arguments built into the framework. Thats important as you can _also_ use javascript - though you don't need to! - in order to build more dynamic query arguments.

The trigger in this case is every two seconds. HTMX triggers are numerous - this is the third one I've shown in this demo - and cover a wide variety of common use cases. If you have something that isn't covered, you can again fallback to javascript.

Finally, we'll put together our `choose_winner` action:

```php
public function chooseWinner()
{
    $this->viewBuilder()->disableAutoLayout();
    $this->set('min', $this->request->getQuery('min'));
    $this->set('max', $this->request->getQuery('max'));

    if (rand(0, 1)) {
        $winner = rand(
            (int)$this->request->getQuery('min'),
            (int)$this->request->getQuery('max')
        );
        $this->set('winner', $winner);
        $this->Htmx->stopPolling();
    }
}
```

In this case, we'll set the min/max as view variables, and also randomly pick a winner in half of the cases. HTMX long-polling can be stopped server-side by setting a specific header on the response, which we do here using the htmx plugin's `HtmxComponent::stopPolling()` method.

The template file is also pretty simple. If there is a winner, we display it, and otherwise tell folks we're still polling:

```php
<?php if (!empty($winner)) : ?>
    <h2>The winner is attendee #<?= $winner ?></h2>
<?php else : ?>
    <h3>No winner yet, keep waiting</h3>
<?php endif; ?>
```
