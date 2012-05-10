<?php

require_once('forwardr.php');

//Set the base for all forwards
$f = new Forwardr('www.google.com');

//Set this public property to true if you want the headers from the forward response
//to be returned back to this request
$f->set_headers = True;

//Used in conjuction with set_headers.  If you know the remote mimetype, set it here
//to match in local response
$f->mimetype = 'text/html';

//Set to true to tell the internal curl not to fail on an HTTPError
$f->debug = True;

//Fetch and echo out the response
print_r($f->exec());
