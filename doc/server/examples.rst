====================
HTTP Server examples
====================

Using nested predicates:

.. code-block:: php

   (new Server())
       ->whenHost('foo')
           ->whenPath('/')
               ->return(new Response('Foo homepage'))
           ->end()

           ->whenPath('/bar')
           ->andWhenQuery(['baz' => 1])
               ->return(new Response('Foo bar 1'))
           ->end()

           ->return(new Response('Foo 404'))
       ->end()

       ->whenHost('bar')
           ->return(new Response('Bar'))
       ->end()
   ;
