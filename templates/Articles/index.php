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