<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cache\FileCache;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Obj;
use SQLite3;

final class SQLitePagesDatabase
{
    /** @var \SQLite3 */
    private $database;
    private $transactionsCount = 0;
    private $options;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
        $this->loadDatabase();

        $this->database->exec("CREATE TABLE IF NOT EXISTS pages (id TEXT primary key unique, modified_at INTEGER, data TEXT)");

        if ($this->options['debug']) {
            $this->flush();
        }

        $this->beginTransaction();
    }

    public function __destruct()
    {
        $this->endTransaction();
        $this->applyPragmas('pragmas-destruct');
        $this->database->close();
    }

    private function loadDatabase()
    {
        $file = $this->options['file'];
        try {
            $this->database = new SQLite3($file);
        } catch (\Exception $exception) {
            F::remove($file);
            F::remove($file . '-wal');
            F::remove($file . '-shm');
            $this->database = new SQLite3($file);
            throw new \Exception($exception->getMessage());
        }
    }

    private function applyPragmas(string $pragmas)
    {
        foreach ($this->options[$pragmas] as $pragma) {
            $this->database->exec($pragma);
        }
    }

    private function beginTransaction()
    {
        $this->database->exec("BEGIN TRANSACTION;");
        $this->transactionsCount++;
    }

    private function endTransaction()
    {
        $this->database->exec("END TRANSACTION;");
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

    private function setOptions(array $options): void
    {
        $this->options = array_merge([
            'file' => \option('bnomei.page-sqlite.file'),
            'debug' => \option('debug'),
            'pragmas-construct' => \option('bnomei.page-sqlite.pragmas-construct'),
            'pragmas-destruct' => \option('bnomei.page-sqlite.pragmas-destruct'),
        ], $options);

        foreach ($this->options as $key => $call) {
            if (!is_string($call) && is_callable($call) && in_array($key, [
                    'file',
                    'pragmas-construct',
                    'pragmas-destruct',
                ])) {
                $this->options[$key] = $call();
            }
        }
    }
}
