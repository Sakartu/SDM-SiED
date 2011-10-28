<?php

require_once(dirname(__FILE__).'/../lib/sdm_util.php');
require_once(dirname(__FILE__).'/../lib/sdm_symmetric_crypt.php');
require_once(dirname(__FILE__).'/../lib/sdm_asymmetric_crypt.php');
require_once(dirname(__FILE__).'/../lib/sdm_xmlsql_converter.php');
require_once(dirname(__FILE__).'/../lib/sdm_xmlrpc_stubs.php');

/*******************************************************************/

$redirect = false;

if (isset($_POST['client']))
{
    $client_id = intval($_POST['client']);
	$_SESSION['client_id'] = $client_id;

	$redirect = true;
}



else if (isset($_POST['xml'])) 
{
	// 
	$xmlstr = utf8_encode($_POST['xml']);
	
	libxml_use_internal_errors(true);
    
	$xml = simplexml_load_string($xmlstr);
	if ($xml === false) 
	{
	    $errorMsg = "Failed loading XML: <ul>";
	    foreach(libxml_get_errors() as $error) 
	    {
	        $errorMsg .= "<li>".$error->message."</li>";
	    }
		$errorMsg .= '</ul>';
		addNotification($errorMsg);
	}
	else 
    {
        if (!isset($_SESSION['client_id']))
        {
            addNotification("Error: Client not set.");
        }
        else 
        {
    	    echo '<pre>';
    	    // $xml contains SimpleXMLElement object
    		echo htmlentities($xml->asXML())."\n\n<br/>";
            
            $convert = new SdmXmlSqlConvert();
            $nodeRowArray = $convert->convertToRows($xml);
            
            XmlRpcStubs::insertAndReplace($_SESSION['client_id'], $nodeRowArray);
            
            
    		echo '</pre>';
        }        
	}
   
	die();
	$redirect = true;
}



else if (isset($_POST['query']))
{
    // XPath query submitted, we need to secure it and send it off to the server.

    echo "<pre>";
    print_r($_SESSION);
    echo '</pre>';
    // Check if the client identity is set
    if (isset($_SESSION['client_id']))
    {
        $qry = $db->prepare("SELECT * FROM clients WHERE id = :client_id");
        $qry->execute(array(':client_id' => $_SESSION['client_id']));

        $client_info = $qry->fetch(PDO::FETCH_ASSOC);

    	// calculate the treeID by encrypting the username with the key
        $key128 = base64_decode($client_info['key']);
        $username = "harriehenkharriehenk";//$client_info ['username'];
        $crypt = new SdmCrypt($key128);

        $treeId = $crypt->encrypt($username);
        echo $treeId." == base64 ciphertext!<br/>";
        echo $crypt->decrypt($treeId)." == plaintext<br/>";
        echo "plaintext length: ".strlen($crypt->decrypt($treeId))."<br/>";
        echo "byte-array ciphertext length: ".strlen(base64_decode($treeId))."<br/>";
        echo "byte-array ciphertext: ".base64_decode($treeId)."<br/>";
        echo "hex2 ciphertext: ".strToHex(base64_decode($treeId));
		
		// Encrypt the XPath query using the method described in 
		// "Efficient Tree Search in Encrypted Data"
		// R. Brinkman, L. Feng, J. Doumen, P.H. Hartel and W. Jonker
		
		
		
        die();
    }
	$redirect = true;
}


if ($redirect)
{
    header("Location:index.php?page=client");
    exit;
}
?>