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
        // $xml contains SimpleXMLElement object

        if (!isset($_SESSION['client_id']))
        {
            addNotification("Error: Client not set.");
        }
        else 
        {
    	    echo '<pre>';
    		echo htmlentities($xml->asXML())."\n\n<br/>";
            
            $cid = $_SESSION['client_id'];
            $treeId = SdmSymmetricCrypt::simpleClientEncrypt($cid, $cid);
            $convert = new SdmXmlSqlConvert();
            
            $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE id = :client_id");
            $qry->execute(array(':client_id' => $cid));
    
            $client_info = $qry->fetch(PDO::FETCH_ASSOC);
            $encryption_key = base64_decode($client_info['encryption_key']);
            $hash_key = base64_decode($client_info['hash_key']);
            $nodeRowArray = $convert->convertToRows($encryption_key, $hash_key, $cid, $treeId, $xml);
            
            XmlRpcStubs::insertAndReplace($_SESSION['client_id'], $nodeRowArray);
            
    		echo '</pre>';
        }        
	}
   
	$redirect = true;
}



else if (isset($_POST['query']))
{
    // XPath query submitted, we need to secure it and send it off to the server.

    // Check if the client identity is set
    if (!isset($_SESSION['client_id']))
    {
        addNotification("Error: Client not set.");
    }    
    else
    {
        // First check if the query is valid:
        
        //preg_match expects foreslashes ('/') around the actual regex pattern:
        $pattern = '/^((\/|\/\/)(@?[a-zA-Z0-9]+(\[@[a-zA-Z0-9]+="[a-zA-Z0-9]+"\])?))+$/';
        $match = preg_match($pattern, $_POST['query']);
        
        if ($match == 0)
        {
            addNotification("Error: Invalid XPath query.");
        }
        else 
        {
            $query = $_POST['query'];
            
            // Then retrieve the encryption keys for the client:
            $cid = $_SESSION['client_id'];
            $treeId = SdmSymmetricCrypt::simpleClientEncrypt($cid, $cid);
            
            $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE id = :client_id");
            $qry->execute(array(':client_id' => $cid));
    
            $client_info = $qry->fetch(PDO::FETCH_ASSOC);
            $raw_encryption_key = base64_decode($client_info['encryption_key']);
            $raw_hash_key = base64_decode($client_info['hash_key']);
    
    		// Encrypt the XPath query using the method described in 
    		// "Efficient Tree Search in Encrypted Data"
    		// R. Brinkman, L. Feng, J. Doumen, P.H. Hartel and W. Jonker
    		
    		// We first need to convert:    /a//b/c[d="e"]
    		// to: /0//1/2[3=4]    
    		// where the numbers are indices in the terms array:
    		// $terms[0][0] == E('a')
    		// $terms[0][1] == the elemen-specific-hashkey (ki) for this ciphertext
    		
    		
    		// 1) Split up the Xpath query into the terms between the foreslashes
    		$term_array = explode('/', $query);
            
            // The first term is always empty, because all queries start with '/', so remove the first term:
            unset($term_array[0]);

            //echo '<pre>';
            //echo 'QUERY: '.$query."\n";
            //print_r($term_array);
            
            // 2) Make one pass over the split terms, generating the query string and the terms array.
            $newQuery = '';
            $newTerms = array();
            foreach ($term_array as $term)
            {
                if (empty($term))
                {
                    // Empty term, inbetween double foreslashes '//'
                    $newQuery .= '/';
                }
                else 
                {
                    // Non-empty term, check if it includes a predicate
                    $newQuery .= '/';
                    
                    if (strpos($term, '[') === false)
                    {
                        // String does not contains a predicate, so it simply looks like: 'a'
                        $searchTerm = SdmSymmetricCrypt::encryptXpathTerm($raw_encryption_key, $raw_hash_key, $term);
                        $newTerms[] = $searchTerm;
                        $newQuery .= count($newTerms)-1;
                    }
                    else 
                    {
                        // String contains a predicate, term looks like: 'a[b="c"]'
                        // Extract all three terms:
                        $predStart = strpos($term, '[');
                        $firstTerm = substr($term, 0, $predStart);

                        $searchFirstTerm = SdmSymmetricCrypt::encryptXpathTerm($raw_encryption_key, $raw_hash_key, $firstTerm);
                        $newTerms[] = $searchFirstTerm;
                        $newQuery .= count($newTerms)-1;

                        $newQuery .= '[';

                        $predString = substr($term, $predStart);
                        $predSep = strpos($predString, '=');
                        $predTag = substr($predString, 1, $predSep-1);
                        $predVal = substr($predString, $predSep+1, strlen($predString)-($predSep+2));
                        $predVal = str_replace('"', '', $predVal);
                        
                        $searchPredTag = SdmSymmetricCrypt::encryptXpathTerm($raw_encryption_key, $raw_hash_key, $predTag);
                        $newTerms[] = $searchPredTag;
                        $newQuery .= count($newTerms)-1;
                        
                        $newQuery .= '="';
                        
                        $searchPredVal = SdmSymmetricCrypt::encryptXpathTerm($raw_encryption_key, $raw_hash_key, $predVal);
                        $newTerms[] = $searchPredVal;
                        $newQuery .= count($newTerms)-1;
                        
                        $newQuery .= '"]';
                    }
                }
                
            }
            //echo $newQuery."\n";
            //print_r($newTerms);
    		
    		
    		// Let's send the query to the server!
    		$response = XmlRpcStubs::sendQuery($cid, $treeId, $newQuery, $newTerms);
            
            //echo "\n\n--------DECODED RESPONSE-------\n";
            //print_r($response);
            if (is_array($response) && (count($response) > 0))
            {
                $nodeRowArrays = array();
                //foreach ($result as $resultInstance) {
                //$response will in the future contain an array of results, right now it contains the rows for 1 result
                    
                    $nodeRows = array();
                    foreach ($response as $row)
                    {
                        //$row contains the encoded rows:
                        //[0] == treeId
                        //[1] == pre
                        //[2] == post
                        //[3] == parent
                        //[4] == tag
                        //[5] == value
                        
                        //NodeRow constructor ($encryption_key, $hash_key, $treeId, $pre, $post, $parent, $tag, $val, $tagAndValEncrypted = false)
                        $nodeRow = new NodeRow($raw_encryption_key, $raw_hash_key, $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], true);
                        $nodeRows[] = serialize($nodeRow);
                    }
                    $nodeRowArrays[] = $nodeRows;
                // }
                
                
                $resultArray = array();
                $resultArray['rowArrays'] = $nodeRowArrays;
                $resultArray['clientId'] = $cid;
                $resultArray['treeId'] = $treeId;
                $resultArray['query'] = $query;
                $resultArray['newQuery'] = $newQuery;
                $resultArray['newTerms'] = $newTerms;
                
                $_SESSION['result'] = $resultArray;
                
                header("Location:index.php?page=result");
                exit;

                

            }
            // If we reach this point we did not get a proper response.
            addNotification("Error: No rows returned, insert a tree first!");
            
        }
    }
	$redirect = true;
}


if ($redirect)
{
    // The operation was not a query, and might invalidate any current results, so clear them.
    unset($_SESSION['result']);
        
    header("Location:index.php?page=query");
    exit;
}
?>