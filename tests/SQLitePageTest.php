<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Bnomei\SQLitePage;

final class SQLitePageTest extends TestCase
{
    public function testConstructs()
    {
        $this->assertInstanceOf(SQLitePage::class, page('home'));
    }

    public function testModified()
    {
        /** @var SQLitePage $home */
        $home = page('home');
        $key = $home->sqliteKey();

        $before = \Bnomei\SQLitePage::singleton()->retrieve($key);
        kirby()->impersonate('kirby');
        $home->update(['title' => 'Home ' . time()]);
        $after = \Bnomei\SQLitePage::singleton()->retrieve($key);

        $this->assertTrue($before->modified_at < $after->modified_at);
    }

    public function testUnknown()
    {
        /** @var SQLitePage $home */
        $home = page('home');
        $key = $home->sqliteKey();

        \Bnomei\SQLitePage::singleton()->remove($key);
        $obj = \Bnomei\SQLitePage::singleton()->get($key, time());
        $this->assertNull($obj);
    }

    public function testReadContent()
    {
        /** @var SQLitePage $home */
        $home = page('home');
        $key = $home->sqliteKey();

        \Bnomei\SQLitePage::singleton()->remove($key);
        $cache = $home->readContentCache();
        $this->assertNull($cache);

        \Bnomei\SQLitePage::singleton()->remove($key);
        $data = $home->readContent();
        $this->assertNotNull($data);
        $cache = $home->readContentCache();
        $this->assertNotNull($cache);
    }
}
