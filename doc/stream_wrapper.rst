==============
Stream wrapper
==============

``Jfalque\HttpMock\StreamWrapper`` is a `PHP stream`_ implementation that replaces the native HTTP wrapper to use a mock
server, allowing you to test HTTP calls through PHP's filesystem functions like ``file_get_contents()``.

To use the wrapper, register it at the beginning of your tests with the ``register()`` method:

.. code-block:: php

   <?php

   use Jfalque\HttpMock\Server;
   use Jfalque\HttpMock\StreamWrapper;

   StreamWrapper::register($server = new Server());

   $server->whenUri('http://foo')->return(new Response('Foo'));

   file_get_contents('http://foo'); // "Foo"

At the end of your tests, you can restore the native HTTP wrapper with ``unregister()``.

.. warning::
   Registering the wrapper with ``stream_wrapper_register()`` won't work as it requires a ``Server`` instance which can
   only be passed with ``StreamWrapper::register()``.

.. _PHP Stream: http://php.net/manual/en/book.stream.php