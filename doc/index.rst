========
HttpMock
========

HttpMock is a simple HTTP server mock for automated tests.

Testing code that makes HTTP calls is not simple: you either need to setup an actual HTTP server that provides the exact
responses your tests require, or create mocks with complex assertions and/or expectations.

HttpMock provides a `server mock <server/principle.rst>`_ that can handle PSR-7_ HTTP requests. It also provides a `stream wrapper <stream_wrapper.rst>`_
that integrates the server into PHP's filesystem functions like ``file_get_contents()``.

Table of contents
=================

#. Server mock

   a. `Principle <server/principle.rst>`_
   b. `API <server/api.rst>`_
   c. `Examples <server/examples.rst>`_

#. `Stream wrapper <stream_wrapper.rst>`_

.. _PSR-7: http://www.php-fig.org/psr/psr-7/