===============
HTTP Server API
===============

The predicates can be defined with the ``when*()`` and ``andWhen*()`` methods: ``when*()`` methods create a new sublayer and
add the predicate to it; ``andWhen*()`` methods add the predicate to the current layer without creating a new one. These
methods are simple shortcuts to the predicate classes in the ``Jfalque\HttpMock\Predicate`` namespace.

The ``return()`` method defines the response of the current layer.

The ``end()`` method closes the current layer and returns its parent.

For API usage examples, see the `examples page <examples.rst>`_.

- Predicates

  - `when() / andWhen()`_
  - `whenBody() / andWhenBody()`_
  - `whenFragment() / andWhenFragment()`_
  - `whenHeaders() / andWhenHeaders()`_
  - `whenHost() / andWhenHost()`_
  - `whenMethod() / andWhenMethod()`_
  - `whenPath() / andWhenPath()`_
  - `whenPort() / andWhenPort()`_
  - `whenProtocolVersion() / andWhenProtocolVersion()`_
  - `whenQuery() / andWhenQuery()`_
  - `whenScheme() / andWhenScheme()`_
  - `whenUri() / andWhenUri()`_

- `return()`_
- `end()`_
- `handle()`_

return()
========

Defines the response of the current layer when it matches the request.

The response can be either:

- a ``Psr\Http\Message\ResponseInterface`` instance;
- a callable that takes a ``Psr\Http\Message\RequestInterface`` instance as first parameter and returns a response.

Parameters
  **response**: the reponse to return.

Return value
  The current layer.

Examples
  .. code-block:: php

     $server->return(new Response('Foo'));
     $server->return(function (RequestInterface $request) {
         return new Response('Request URI was '.$request->getUri());
     });

end()
=====

Returns the parent layer if any, ``null`` otherwise (root layer).

With the fluent API, this method can be used to stop defining the current layer and get back to its parent.

Return value
  The parent layer, or ``null``.

handle()
========

Sends the request to the server and returns the response that matches it, if any.

Parameters
  **request**: the request to send to the server.

Return value
  A ``Psr\Http\Message\ResponseInterface`` instance that matches the request if any, ``null`` otherwise.

when() / andWhen()
==================

``when()`` creates a new layer that matches requests against a custom callable.

``andWhen()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **predicate**: a callable that accepts a ``Psr\Http\Message\RequestInterface`` instance as first parameter and returns
  a boolean.

Return value
  The created layer for ``when()``; the current one for ``andWhen()``.

Examples
  .. code-block:: php

     $server->when(function (RequestInterface $request): bool {
         return 'POST' == $request->getMethod() && 'http://foo' == $request->getUri();
     });

whenBody() / andWhenBody()
==========================

``whenBody()`` creates a new layer that matches requests against an expected body.

The expected body can be either the exact contents or a `PCRE pattern`_.

``andWhenBody()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **body**: the expected body;

  **regexp**: boolean, if ``true``, the expected body is a `PCRE pattern`_ (defaults to ``false``).

Return value
  The created layer for ``whenBody()``; the current one for ``andWhenBody()``.

Examples
  .. code-block:: php

     $server->whenBody('FooBar');
     $server->whenBody('/Foo/', true);

whenFragment() / andWhenFragment()
==================================

``whenFragment()`` creates a new layer that matches requests against an expected fragment.

The expected fragment can be either the exact value or a `PCRE pattern`_.

``andWhenFragment()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **fragment**: the expected fragment;

  **regexp**: boolean, if ``true``, the expected fragment is a `PCRE pattern`_ (defaults to ``false``).

Return value
  The created layer for ``whenFragment()``; the current one for ``andWhenFragment()``.

Examples
  .. code-block:: php

     $server->whenFragment('foo-bar');
     $server->whenFragment('/foo/', true);

whenHeaders() / andWhenHeaders()
================================

``whenHeaders()`` creates a new layer that matches requests against a list of header values.

The expected headers list matches when it is a subset of the request's headers (or the same list). Headers order does
not matter but the order of their values does.

``andWhenHeaders()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **headers**: the expected headers.

Return value
  The created layer for ``whenHeaders()``; the current one for ``andWhenHeaders()``.

Examples
  .. code-block:: php

     $server->whenHeaders(['X-Foo' => 'foo']);
     $server->whenHeaders(['X-Foo' => ['foo', 'bar']]);

whenHost() / andWhenHost()
==========================

``whenHost()`` creates a new layer that matches requests against an expected hostname.

The expected hostname can be either the exact value or a `PCRE pattern`_.

``andWhenHost()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **host**: the expected hostname;

  **regexp**: boolean, if ``true``, the expected hostname is a `PCRE pattern`_ (defaults to ``false``).

Return value
  The created layer for ``whenHost()``; the current one for ``andWhenHost()``.

Examples
  .. code-block:: php

     $server->whenHost('foo-bar');
     $server->whenHost('/foo/', true);

whenMethod() / andWhenMethod()
==============================

``whenMethod()`` creates a new layer that matches requests against a list of HTTP methods.

``andWhenMethod()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **method**: the expected method or a list of expected methods.

Return value
  The created layer for ``whenMethod()``; the current one for ``andWhenMethod()``.

Examples
  .. code-block:: php

     $server->whenMethod('GET');
     $server->whenMethod(['POST', 'PUT']);

whenPath() / andWhenPath()
==========================

``whenPath()`` creates a new layer that matches requests against an expected path.

The expected path can be either the exact value or a `PCRE pattern`_. With exact value, empty path ``""`` and absolute
path ``"/"`` are considered equals and match.

``andWhenPath()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **path**: the expected path;

  **regexp**: boolean, if ``true``, the expected path is a `PCRE pattern`_ (defaults to ``false``).

Return value
  The created layer for ``whenPath()``; the current one for ``andWhenPath()``.

Examples
  .. code-block:: php

     $server->whenPath('');
     $server->whenPath('/');
     $server->whenPath('/foo-bar');
     $server->whenPath('~/foo~', true);

whenPort() / andWhenPort()
==========================

``whenPort()`` creates a new layer that matches requests against a list of port numbers.

``andWhenPort()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **port**: the expected port number or a list of expected port numbers.

Return value
  The created layer for ``whenPort()``; the current one for ``andWhenPort()``.

Examples
  .. code-block:: php

     $server->whenPort(80);
     $server->whenPort([80, 443]);

whenProtocolVersion() / andWhenProtocolVersion()
================================================

``whenProtocolVersion()`` creates a new layer that matches requests against a protocol version.

``andWhenProtocolVersion()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **version**: the expected version number.

Return value
  The created layer for ``whenProtocolVersion()``; the current one for ``andWhenProtocolVersion()``.

Examples
  .. code-block:: php

     $server->whenProtocolVersion('1.1');
     $server->whenProtocolVersion(1.1);
     $server->whenProtocolVersion(1.0);
     $server->whenProtocolVersion(1);

whenQuery() / andWhenQuery()
============================

``whenQuery()`` creates a new layer that matches requests against an expected query string or a list of query string
parameters.

As a string, the expected query string can be either the exact value or a `PCRE pattern`_.

As an array, the expected parameters list can be either a subset of the request's query string or the exact list. In
both cases, the order of the values matters.

``andWhenQuery()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **query**: the expected path;

  **regexp**: boolean, if ``true``, the expected path is a `PCRE pattern`_ or an array that contains a subset of the
  query string parameters (defaults to ``false``).

Return value
  The created layer for ``whenQuery()``; the current one for ``andWhenQuery()``.

Examples
  .. code-block:: php

     $server->whenQuery('foo=foo&bar=bar');
     $server->whenQuery('/foo=/', true);
     $server->whenQuery([
         'foo' => 'foo',
         'bar' => 'bar',
     ]);
     $server->whenQuery(['foo' => 'foo'], true);

whenScheme() / andWhenScheme()
==============================

``whenScheme()`` creates a new layer that matches requests against a case-insensitive scheme.

``andWhenScheme()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **scheme**: the expected scheme.

Return value
  The created layer for ``whenScheme()``; the current one for ``andWhenScheme()``.

Examples
  .. code-block:: php

     $server->whenScheme('https');

whenUri() / andWhenUri()
========================

``whenUri()`` creates a new layer that matches requests against a full URI (scheme, hostname, port number, path, query string and
fragment).

The expected URI can be either the exact value or a `PCRE pattern`_. With exact value, empty path ``""`` and absolute
path ``"/"`` are considered equals and match.

``andWhenUri()`` is similar but applies to the current layer instead of creating a new one.

Parameters
  **uri**: the expected URI;

  **regexp**: boolean, if ``true``, the expected URI is a `PCRE pattern`_ (defaults to ``false``).

Return value
  The created layer for ``whenUri()``; the current one for ``andWhenUri()``.

Examples
  .. code-block:: php

     $server->whenUri('http://foo');
     $server->whenUri('http://foo/');
     $server->whenUri('http://foo?foo=foo#foo');

.. _PCRE Pattern: http://php.net/manual/en/pcre.pattern.php
