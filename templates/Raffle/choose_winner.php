<?php if (!empty($winner)) : ?>
    <h2>The winner is attendee #<?= $winner ?></h2>
<?php else : ?>
    <h3>No winner yet, keep waiting</h3>
<?php endif; ?>