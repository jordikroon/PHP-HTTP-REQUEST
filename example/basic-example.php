<?php

include_once '../src/httprequest.class.php';

$_POST['postvalue1'] = 'something';
$_POST['postvalue2'] = 'something else';

$request = new HTTPRequest('http://example.com/demo.php');

$request->method = 'post'; 
$request->postFields = $_POST;

$request->userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko/20100101 Firefox/16.0';
$request->timeout = 10; // timeout after 10 seconds 
$request->cookiedir = 'tmp';

if($request->send()) { 
	echo $request->getResult();
}  else {
	echo $request->getErrorMessage();
}

?>