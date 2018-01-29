<?php
// As we can't return any content to Xero, this PHP script outputs the request to a text file this helps us debug any issues
// Based on the script found here:
// https://gist.github.com/magnetikonline/650e30e485c0f91f2f40

//everything we want to see gets written to $data
		
//headers
$data = sprintf(
	"%s %s %s\n\nHTTP headers:\n",
	$_SERVER['REQUEST_METHOD'],
	$_SERVER['REQUEST_URI'],
	$_SERVER['SERVER_PROTOCOL']
);
foreach (getHeaderList() as $name => $value) {
	$data .= $name . ': ' . $value . "\n";
}

//get the payload
$payload = file_get_contents('php://input');

$data .= "\nRequest body:\n";
$data .= $payload . "\n";

//calculate our signature
$hookkey = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; //INSERT WEBHOOK KEY HERE
$data .= "\nOur Signature:\n";
$calculatedhash = base64_encode(hash_hmac('sha256',$payload,$hookkey,true));
$data .= $calculatedhash;

//display what Xero has sent
$data .= "\nXero Sig:\n";
$xerohash = $_SERVER['HTTP_X_XERO_SIGNATURE'];
$data .= $xerohash;
		
//see if they match
$data .= "\nMatch?:\n";
if (hash_equals($calculatedhash,$xerohash)) {
	$data .= "Yes";
	http_response_code(200);
} else {
	$data .= "No";
	http_response_code(401);
}

//format filename
$fn = microtime();
$fn = substr($fn,11) . substr($fn,2,8);
		
//output to file
file_put_contents('./'.$fn.'.txt',$data);
			
function getHeaderList() {
	$headerList = [];
	foreach ($_SERVER as $name => $value) {
		if (preg_match('/^HTTP_/',$name)) {
			$headerList[$name] = $value;
		}
	}
	return $headerList;
}
?>