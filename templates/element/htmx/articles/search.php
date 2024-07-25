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