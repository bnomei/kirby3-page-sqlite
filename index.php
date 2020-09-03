<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/page-sqlite', [
    'options' => [
        'cache' => true,
        'wal' => true,
        'file' => function () {
            return \Bnomei\SQLitePagesDatabase::cacheFolder() . '/page-sqlite-v1-0-1.sqlite';
        }
    ],
    'pageMethods' => [
        'isSQLitePage' => function () {
            /** @var $this \Bnomei\SQLitePage */
            return is_a($this, \Bnomei\SQLitePage::class) &&
                $this->isContentSQLite(kirby()->languageCode());
        },
    ],
]);
