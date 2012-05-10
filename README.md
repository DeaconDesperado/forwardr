# Forwardr  

Forwardr is a simple HTTP endpoint catchall that can be used in conjuction with RESTful web services.

Commonly, a REST service can reside on a different (sub)domain from the client-side frontend.  Therefore it
becomes necessary to implement jsonp or another similiar approach that can get arround Cross-Domain Origin Policy.

An alternative is to simply have an endpoint within the client domain that can forward any requests, complete with their params,
over to the REST domain.  This is the problem Forwardr is intended to solve.

To set up an endpt for forwarding, simply instantiate with the base destination url and call exec().

    $f = new Forwardr('data.test.com');
    $f->exec();

Calling the exec method like this will simply use the the url path after your listening endpt to determine the remote path.
IE client.test.com/index.php/data/goes/here will be remotely forwarded to data.test.com/data/goes/here.  Any parameters from the $_GET
or $_POST superglobals will be handed off to the remote request transparently, allowing parameters specified in AJAX to forward appropriately.

You can pass a static path to exec() to force all requests to the same uri at the remote domain.

    $f = new Forwardr('data.test.com');
    $f->exec('/have/a/nice/day'); 

The constructor to Forwardr can also optionally also take an associative array of params to be embedded into every request, which
works great for hmacs, tokens or other auth credentials.

    $params = array('app_secret'=>'somehash');
    $f = new Forwardr('data.test.com',$params);
    $f->exec();

You can use these methods along with URL rewriting to make a 'middleman' for your web service.

There are three public properties that can change response behavior

* $debug - If true, will prevent the request from failing and always return a response body
* $set_headers - If true, response status codes from the remote endpt will be embedded into the local response
* $mimetype - Used in conjunction with set_headers, this can set the mimetype for the local response
