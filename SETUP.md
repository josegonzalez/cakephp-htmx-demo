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

