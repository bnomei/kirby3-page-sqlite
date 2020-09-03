<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;

class SQLitePage extends Page
{
    private static $singleton;
    public static function singleton(): ?SQLitePagesDatabase
    {
        if (!self::$singleton) {
            self::$singleton = new SQLitePagesDatabase();
        }

        return self::$singleton;
    }

    public function isContentSQLite(string $languageCode = null): bool
    {
        return $this->readContentCache($languageCode) !== null;
    }

    public function sqliteKey(string $languageCode = null): string
    {
        $key = $this->cacheId('sqlite');
        if (!$languageCode) {
            $languageCode = kirby()->languages()->count() ? kirby()->language()->code() : null;
            if ($languageCode) {
                $key = $languageCode . '.' . $key;
            }
        }

        return md5(kirby()->roots()->index() . $key);
    }

    public function readContent(string $languageCode = null): array
    {
        // read from sqlite if exists
        $data = $this->readContentCache($languageCode);

        // read from file and update sqlite
        if (! $data) {
            $data = parent::readContent($languageCode);
            $this->writeContentCache($data, $languageCode);
        }

        return $data;
    }

    /**
     * @internal
     */
    public function readContentCache(string $languageCode = null): ?array
    {
        $cache = static::singleton();
        if (! $cache) {
            return null;
        }
        return $cache->get(
            $this->sqliteKey($languageCode),
            $this->sqliteModified(),
            null
        );
    }

    public function writeContent(array $data, string $languageCode = null): bool
    {
        // write to file and sqlite
        return parent::writeContent($data, $languageCode) &&
            $this->writeContentCache($data, $languageCode);
    }

    /**
     * @internal
     */
    public function writeContentCache(array $data, string $languageCode = null): bool
    {
        $cache = static::singleton();
        if (! $cache) {
            return true;
        }
        return $cache->set(
            $this->sqliteKey($languageCode),
            $data,
            $this->sqliteModified()
        );
    }

    public function delete(bool $force = false): bool
    {
        $cache = static::singleton();
        if ($cache) {
            foreach (kirby()->languages() as $language) {
                $cache->remove(
                    $this->sqliteKey($language->code())
                );
            }
            $cache->remove(
                $this->sqliteKey()
            );
        }

        return parent::delete($force);
    }

    private function sqliteModified(): int
    {
        return function_exists('modified') ? \modified($this) : $this->modified();
    }
}
