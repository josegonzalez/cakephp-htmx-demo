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