# Kirby 3 Page SQLite

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-page-sqlite?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-page-sqlite?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-page-sqlite)](https://travis-ci.com/bnomei/kirby3-page-sqlite)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-page-sqlite)](https://coveralls.io/github/bnomei/kirby3-page-sqlite) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-page-sqlite)](https://codeclimate.com/github/bnomei/kirby3-page-sqlite) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Kirby 3 Plugin to cache the content file using SQLite

## Commercial Usage

This plugin is free (MIT license) but if you use it in a commercial project please consider to
- [make a donation 🍻](https://www.paypal.me/bnomei/5) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-page-sqlite/archive/master.zip) as folder `site/plugins/kirby3-page-sqlite` or
- `git submodule add https://github.com/bnomei/kirby3-page-sqlite.git site/plugins/kirby3-page-sqlite` or
- `composer require bnomei/kirby3-page-sqlite`

### Usage

To use this plugin create [Page-Models](https://getkirby.com/docs/guide/templates/page-models) and extend the `\Bnomei\SQLitePage` class. This will read and write a **copy** of your Content-File to and from a SQLite database. The plugin will automatically keep track of the modified timestamp.

**site/models/example.php**
```php
<?php

class ExamplePage extends \Bnomei\SQLitePage
{
    // that's it. all done. 👍
}
```

> TIP: If you set Kirbys global debug option to `true` all cached Content-Files will be flushed.

**site/templates/example.php**
```php
<?php
/** @var ExamplePage $page */
$checkIfContentFileIsSQLiteCached = $page->isSQLitePage(); // bool
```

## Settings

| bnomei.page-sqlite.            | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| file | `callback` |  |
| wal | `true` | sqlite WAL for faster IO |


## Dependencies

- PHP SQLite extension

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-page-sqlite/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
