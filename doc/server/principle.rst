================
HTTP Server mock
================

The ``Jfalque\HttpMock\Server`` class provides a fluent API to create a simple HTTP server mock:

.. code-block:: php

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

The server works by defining layers with predicates. Predicates are functions that return a boolean depending on whether
a request match some criteria and are used to determine the matching response. When handling a request, a layer passes
it to its predicates and if all predicates match (return ``true``), the response will be:

1. the response returned by sublayers, if any matches;
2. the response of the current layer, if defined;
3. ``null``.

If any predicate does not match (returns ``false``), the current layer does not return a response and the matching
process continues with subsequent layers.

See the `API documentation <api.rst>`_ for more details.