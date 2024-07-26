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