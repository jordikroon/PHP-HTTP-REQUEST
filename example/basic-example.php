<?php

include_once '../src/httprequest.class.php';

$_POST['postvalue1'] = 'something';
$_POST['postvalue2'] = 'something else';

$request = new HTTPRequest();

$request->setURL('http://example.com/demo.php');

$request->method = 'POST'; //POST or GET
$request->postFields = $_POST; // POST array or Associative array
$request->forceOneConnection = false; // True = always 1 connection
$request->requestType = 1; // 1: CURL 2: Content request

$request->userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko/20100101 Firefox/16.0'; // useragent
$request->timeout = 10; // timeout after 10 seconds 
$request->cookiedir = '/path/to/tmp'; // path to cookie storage folder
 
if($request->send()) { 
	echo $request->getResult();
}  else {
	echo $request->getErrorMessage();
}

?>