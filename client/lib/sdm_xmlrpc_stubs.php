<?php

require_once(dirname(__FILE__)."/../model/noderow.php");

class XmlRpcStubs
{
    /**
     * @param rowObjects contains NodeRow objects
     */
    public static function insertAndReplace($client_id, $rowObjects)
    {
        //API: insert(base64 sig, int client_id, base64 tree_id, string[] EncryptedRows)
        $tree_id = "BoomIdentiteit";
        
        $par_sig = "nog_geen_sig";
        $par_client_id = $client_id;
        $par_tree_id = $tree_id;
        
        echo "XMLRPC Stub: insert(".$par_sig.", ".$par_client_id.", ".$par_tree_id.", "."array())<br/>";
        
        xmlrpc_set_type($par_sig, "base64");
        xmlrpc_set_type($par_client_id, "int");
        xmlrpc_set_type($par_tree_id, "base64");
        
        $par_encrypted_rows = array();
        foreach ($rowObjects as $row)
        {
            $currentRow = $row->toEncryptedArray($tree_id);
            xmlrpc_set_type($currentRow, "base64");
            $par_encrypted_rows[] = $currentRow;
        }
        
        xmlrpc_set_type($par_encrypted_rows, "base64");
        echo "par_encrypted_rows ==  ";
        var_dump($par_encrypted_rows);
        
        
        echo "XMLRPC Stub: insert(".$par_sig->scalar.", ".$par_client_id.", ".$par_tree_id->scalar.", "."array())<br/>";
        
        $request= xmlrpc_encode_request('insert', array($par_sig, $par_client_id, $par_tree_id, $par_encrypted_rows));

        echo "<pre>------request------\n\n";
        echo htmlentities($request) ."\n\n";
        
        $context = stream_context_create(array('http' => array(
            'method' => "POST",
            'header' => "Content-Type: text/xml",
            'content' => $request
        )));
        
        $server_location = $_SESSION['xmlrpc_server'];
        echo 'SENDING REQUEST TO '.$server_location."<br/>\n";

        // Do the actual XMLRPC call
        // The server location should be stored in the session:
        $response = file_get_contents($server_location, false, $context);
        
        echo "<br/>---reponse----<br/>";
        echo htmlentities($response);
        
        $decoded_response = xmlrpc_decode($response);
        
        if (is_array($decoded_response) && xmlrpc_is_fault($decoded_response)) {
            trigger_error("xmlrpc: $decoded_response[faultString] ($decoded_response[faultCode])");
        } else {
            echo "\n\ndecoded response:\n";
            print_r($decoded_response);
            echo "\n\nvar_dump():\n";
            var_dump($decoded_response);
        }
        
        echo "</pre>";
        
    }    
}


/*
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
*/


?>
