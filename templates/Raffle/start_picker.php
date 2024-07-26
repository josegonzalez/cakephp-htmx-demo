<?= $this->Html->div('no-winner', sprintf('Polling every 2 seconds for users between %s and %s', $min, $max), [
    'hx-get' => $this->Url->build(['action' => 'choose_winner']),
    'hx-vals' => json_encode([
        'min' => $min,
        'max' => $max
    ]),
    'hx-trigger' => 'every 2s',
]); ?>
