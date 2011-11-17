<?php

require_once(dirname(__FILE__)."/../lib/sdm_asymmetric_crypt.php");
require_once(dirname(__FILE__)."/../model/noderow.php");

class XmlRpcStubs
{
    
    /**
     * Send the prepared XMLRPC request $request to the server
     * Returns the decoded response.
     */
    public static function doCall($request)    
    {
        // Enable to show request/response
        $debug = false;

        $dmsg = "<pre>------request------\n\n";
        $dmsg .= htmlentities($request) ."\n\n";
        
        $context = stream_context_create(array('http' => array(
            'method' => "POST",
            'header' => "Content-Type: text/xml",
            'content' => $request
        )));
        
        $server_location = $_SESSION['xmlrpc_server'];
        $dmsg .= 'SENDING REQUEST TO '.$server_location."<br/>\n";

        // Do the actual XMLRPC call
        // The server location should be stored in the session:
        if (isset($_SESSION['xmlrpc_enabled']) && ($_SESSION['xmlrpc_enabled'] == true))
        { 
            $response = file_get_contents($server_location, false, $context);
        }
        
        $dmsg .=  "<br/>---reponse----<br/>";
        $dmsg .=  htmlentities($response);
        
        $decoded_response = xmlrpc_decode($response);
        
        if (is_array($decoded_response) && xmlrpc_is_fault($decoded_response)) {
            trigger_error("xmlrpc: $decoded_response[faultString] ($decoded_response[faultCode])");
        } else {
            $dmsg .=  "\n\ndecoded response:\n";
            $dmsg .= print_r($decoded_response, true);
            $dmsg .=  "\n\nvar_dump():\n";
            $dmsg .= var_export($decoded_response, true);
        }
        
        $dmsg .= '</pre>';
        
        if ($debug) {
            echo $dmsg;
            die();
        }
        
        return $decoded_response;
    }
    
    public static function updateRow($client_id, $treeId, $pre, $tag, $value)
    {
        // sign with client key. the string to be signed is: function name and arguments concatenated
        //TODO: what to sign?
        $sign_string = 'update';
        $par_sig = SdmAsymmetricCrypt::clientSign($clientId, $sign_string);
        
        // Send the public key in $pubkey to the server
        $request= xmlrpc_encode_request('update', array($par_sig, $client_id, $treeId, $pre, $tag, $value));
        
        $response = XmlRpcStubs::doCall($request);
        
        return $response;
        
        
    }


    /**
     * Add a public key, or die if the server is unreachable.
     */
    public static function addPublicKey($client_id, $tree_id, $pubkey)
    {
        // sign with CONSULTANT key: API function name and arguments concatenated
        //TODO: what to sign??
        $sign_string = 'add_pubkey';
        $par_sig = SdmAsymmetricCrypt::consultantSign($sign_string);
        
        // Send the public key in $pubkey to the server
        $request= xmlrpc_encode_request('add_pubkey', array($par_sig, $client_id, $tree_id, $pubkey));
        
        $response = XmlRpcStubs::doCall($request);
        
        return $response;
    }


    /**
     * Clears the public keys...
     */
    public static function clearPublicKeys()
    {
        // sign with CONSULTANT key: API function name and arguments concatenated
        //TODO: what to sign??
        $sign_string = 'clear_keys';
        $par_sig = SdmAsymmetricCrypt::consultantSign($sign_string);
        
        $request= xmlrpc_encode_request('clear_keys', array($par_sig));
        
        $response = XmlRpcStubs::doCall($request);
        
        return $response;
    }
    
    
    /**
     * Send a query to the server...
     */
    public static function sendQuery($clientId, $treeId, $queryString, $queryTerms)
    {
        // API: search(base64 sig, int client_id, base64 tree_id, string query, base64[] encrypted_content)
        // sign with client key: function name and arguments concatenated
        //TODO: how to sign an array?
        $sign_string = 'search';
        $par_sig = SdmAsymmetricCrypt::clientSign($clientId, $sign_string);
        
        $request= xmlrpc_encode_request('search', array($par_sig, $clientId, $treeId, $queryString, $queryTerms));
        
        $response = XmlRpcStubs::doCall($request);
        
        return $response;        
    }


    
    /**
     * insertAndReplace
     * 
     *
     * @param rowObjects contains NodeRow objects
     */
    public static function insertAndReplace($client_id, $rowObjects)
    {
        //API: insert(base64 sig, int client_id, base64 tree_id, string[] EncryptedRows)
        //TODO: what to sign?
        $sign_string = 'insert';
        $par_sig = SdmAsymmetricCrypt::clientSign($client_id, $sign_string);


        $par_client_id = $client_id;

        // Tree ID is encrypted client_id        
        $par_tree_id = SdmSymmetricCrypt::simpleClientEncrypt($client_id, $client_id);        
        
        // Encode the NodeRow objects in $rowObjects into an array suitable for sending
        $par_encrypted_rows = array();
        foreach ($rowObjects as $row)
        {
            $currentRow = $row->toEncryptedArray($client_id);
            
            $par_encrypted_rows[] = $currentRow;
        }
        
        // Set the types of the singular arguments
        xmlrpc_set_type($par_sig, "string");
        xmlrpc_set_type($par_client_id, "int");
        xmlrpc_set_type($par_tree_id, "string");

        //echo "\n\npar_encrypted_rows ==  ";
        //var_dump($par_encrypted_rows);
        //echo "\nXMLRPC-STUB CALLED: insert(".$par_sig.", ".$par_client_id.", ".$par_tree_id.", "."array())";
        
        $request= xmlrpc_encode_request('insert', array($par_sig, $par_client_id, $par_tree_id, $par_encrypted_rows));

        $response = XmlRpcStubs::doCall($request);
        
        addNotification("Success: Tree inserted.");
        
        return $response;
    }

}


?>
