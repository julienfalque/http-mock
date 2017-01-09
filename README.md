# HttpMock

[![Latest Stable Version](https://poser.pugx.org/jfalque/http-mock/v/stable)](https://packagist.org/packages/jfalque/http-mock)
[![License](https://poser.pugx.org/jfalque/http-mock/license)](https://packagist.org/packages/jfalque/http-mock)

A HTTP server mock for automated tests.

Testing code that makes HTTP calls is not simple: you either need to setup an actual HTTP server that provides the exact
responses your tests require, or create mocks with complex assertions and/or expectations.

This package provides a server mock that can handle [PSR-7](http://www.php-fig.org/psr/psr-7/) HTTP requests. It also
provides a [stream wrapper](http://php.net/manual/en/book.stream.php) that integrates the server into PHP's filesystem functions
like `file_get_contents()`.

```php
<?php

use Jfalque\HttpMock\Server;

$server = (new Server())
    ->whenUri('http://foo')
        ->return($foo = new Response())
    ->end()
    ->whenUri('http://bar')
        ->return($bar = new Response())
    ->end()
;

$response = $server->handle(new Request('http://foo')); // $foo
$response = $server->handle(new Request('http://bar')); // $bar
$response = $server->handle(new Request('http://baz')); // null

```

## Installation

Run the following [Composer](https://getcomposer.org) command:

`$ composer require --dev jfalque/http-mock`

## More information

Read the [documentation](doc/index.rst) for more information.
