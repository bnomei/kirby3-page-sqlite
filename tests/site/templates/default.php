<?php
/** @var DefaultPage $page */

?><h1><?= get_class($page) ?>: <?= $page->title() ?></h1>
<?= $page->text()->kirbytext() ?>
<?= $page->isContentSQLite() ? 'yes' : 'no' ?>

<time><?= date('c'); ?></time>
