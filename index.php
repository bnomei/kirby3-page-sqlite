<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/page-sqlite', [
    'options' => [
        'cache' => true,
        'file' => function () {
            return \Bnomei\SQLitePagesDatabase::cacheFolder() . '/page-sqlite-v1-0-1.sqlite';
        },
        // https://sqlite.org/pragma.html
        'pragmas-construct' => function () {
            $defaults = [
                'PRAGMA main.cache_size = 10000;',
                'PRAGMA case_sensitive_like = false',
                'PRAGMA main.auto_vacuum = INCREMENTAL;',
                'PRAGMA main.locking_mode = EXCLUSIVE;',
                'PRAGMA main.page_size = 4096;',
                'PRAGMA temp_store = MEMORY;',
            ];
            if (SQLite3::version() >= 3007001) {
                return array_merge($defaults, [
                    'PRAGMA main.synchronous = NORMAL;',
                    'PRAGMA main.journal_mode = WAL;',
                ]);
            } else {
                return array_merge($defaults, [
                    'PRAGMA main.synchronous = OFF;',
                    'PRAGMA main.journal_mode = MEMORY;',
                ]);
            }
        },
        'pragmas-destruct' => function () {
            $defaults = [
                'PRAGMA main.incremental_vacuum;',
            ];
            if (SQLite3::version() >= 3007001) {
                return array_merge($defaults, [
                    'PRAGMA main.wal_checkpoint(TRUNCATE);',
                    'PRAGMA main.synchronous = NORMAL;',
                    'PRAGMA main.locking_mode = NORMAL;',
                ]);
            } else {
                return array_merge($defaults, []);
            }
        },
    ],
    'pageMethods' => [
        'isSQLitePage' => function () {
            /** @var $this \Bnomei\SQLitePage */
            return is_a($this, \Bnomei\SQLitePage::class) &&
                $this->isContentSQLite(kirby()->languageCode());
        },
    ],
]);
