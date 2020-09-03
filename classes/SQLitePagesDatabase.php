<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cache\FileCache;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Obj;

final class SQLitePagesDatabase
{
    /** @var \SQLite3 */
    private $database;

    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'file' => \option('bnomei.page-sqlite.file'),
        ], $options);

        foreach ($this->options as $key => $call) {
            if (!is_string($call) && is_callable($call) && in_array($key, ['file'])) {
                $this->options[$key] = $call();
            }
        }

        $target = $this->options['file'];
        $this->database = new \SQLite3($target);
        if (\SQLite3::version() >= 3007001 && \option('bnomei.page-sqlite.wal')) {
            $this->database->exec("PRAGMA busy_timeout=1000");
            $this->database->exec("PRAGMA journal_mode = WAL");
        }
        $this->database->exec("CREATE TABLE IF NOT EXISTS pages (id TEXT primary key unique, modified_at INTEGER, data TEXT)");

        if (\option('debug')) {
            $this->flush();
        }
    }

    public function __destruct()
    {
        if (\SQLite3::version() >= 3007001 && \option('bnomei.page-sqlite.wal')) {
            $this->database()->exec('PRAGMA main.wal_checkpoint(TRUNCATE);');
        }
        $this->database()->close();
    }

    public function databaseFile(): string
    {
        return $this->options['file'];
    }

    public function database(): \SQLite3
    {
        return $this->database;
    }

    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $modified): bool
    {
        $this->remove($key);

        $item = new Obj([
            'id' => $key,
            'modified_at' => $modified,
            'data' => json_encode(array_map(function ($value) {
                return $value ? htmlspecialchars(strval($value), ENT_QUOTES) : null;
            }, $value)),
        ]);

        return $this->database->query("
            INSERT INTO pages
            (id, modified_at, data)
            VALUES
            ('{$item->id}', {$item->modified_at}, '{$item->data}')
        ") !== false;
    }

    public function retrieve(string $key): ?Obj
    {
        $results = $this->database->query("SELECT * FROM pages WHERE id = '${key}'")
            ->fetchArray(SQLITE3_ASSOC);

        return $results ? new Obj($results) : null;
    }

    public function get(string $key, int $modified, $default = null)
    {
        // get the Value
        $value = $this->retrieve($key);

        // check for a valid value
        if (!is_a($value, 'Kirby\Toolkit\Obj')) {
            return $default;
        }

        // remove the item if it is expired
        if ($value->modified_at < $modified) {
            $this->remove($key);
            return $default;
        }

        // return the pure value
        return array_map(function ($value) {
            return $value ? htmlspecialchars_decode(strval($value)) : '';
        }, json_decode($value->data, true));
    }

    public function remove(string $key): bool
    {
        $str = "DELETE FROM pages WHERE id = '${key}'";
        return $this->database->exec($str) !== false;
    }

    public function flush(): bool
    {
        return $this->database->exec("DELETE FROM pages WHERE id != ''") !== false;
    }

    public function root(): string
    {
        return realpath(__DIR__ . '/../') . '/cache';
    }

    public static function cacheFolder(): string
    {
        $cache = kirby()->cache('bnomei.page-sqlite');
        if (is_a($cache, FileCache::class)) {
            return A::get($cache->options(), 'root') . '/' . A::get($cache->options(), 'prefix');
        }
        // @codeCoverageIgnoreStart
        return kirby()->roots()->cache();
        // @codeCoverageIgnoreEnd
    }
}
