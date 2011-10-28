<?php

/* 
 *  Install module php5-xmlrpc
 *  API: http://xmlrpc-epi.sourceforge.net/main.php?t=php_api
 * 
 */ 
 
 $test = base64_encode("teststring");
 xmlrpc_set_type($test, "base64");
print_r($test);

$par1 = "jemoeder";
$par2 = "jemoeder2";
xmlrpc_set_type($par1, "base64");
xmlrpc_set_type($par2, "base64");

$f= xmlrpc_encode_request('test', array($par1, $par2));

echo "<pre>------request------\n\n";

echo htmlentities($f) ."\n\n";

$context = stream_context_create(array('http' => array(
    'method' => "POST",
    'header' => "Content-Type: text/xml",
    'content' => $f
)));

$file = file_get_contents("http://127.0.0.1:8000/", false, $context);

echo "<br/>---reponse----<br/>";
echo htmlentities($file);


$response = xmlrpc_decode($file);

if (is_array($response) && xmlrpc_is_fault($response)) {
    trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
} else {
    echo "\n\ndecoded response:\n";
    print_r($response);
    echo "\n\nvar_dump():\n";
    var_dump($response);
}

echo "</pre>";


?>
