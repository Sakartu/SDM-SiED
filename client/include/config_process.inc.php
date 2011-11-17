<?php
require_once(dirname(__FILE__).'/../lib/sdm_util.php');
require_once(dirname(__FILE__).'/../lib/sdm_symmetric_crypt.php');
require_once(dirname(__FILE__).'/../lib/sdm_asymmetric_crypt.php');
require_once(dirname(__FILE__).'/../lib/sdm_xmlrpc_stubs.php');

$redirect = false;

if (isset($_POST['submit']))
{
    if (isset($_POST['xmlrpc_server'])) {
	    $_SESSION['xmlrpc_server'] = fixEncoding($_POST['xmlrpc_server']);
	    
	    addNotification("Success: Location saved.");
	    
	    $redirect = true;
    }
}
else if (isset($_POST['reset']))
{
    // Throws away all session data, it will be restored to defaults on the next page load.
    session_destroy();
        
    $redirect = true;
}
else if (isset($_POST['add']))
{
	$success = true;
	// Add client to the database 
    if (isset($_POST['username'])) 
    {
    	$username = fixEncoding($_POST['username']);
    	$encryption_key = trim(fixEncoding($_POST['encryption_key']));
        $hash_key = trim(fixEncoding($_POST['hash_key']));

        
        if (strlen($username) < 3)
        {
            $success = false;
            addNotification("Error: Username must be at least 3 characters.");
        } else {
            // Check if username is unique
            if (!uniqueCheck("clients", "username", $username))
            {
                $success = false;
                addNotification("Error: username is not unique.");
            }
        }
    } 
    else {
        $success = false;
        addNotification("Error: Enter a username and password.");
    }
    
        
    // If the username was valid, we will now check the encryption key
    if ($success)
    {
        if (strlen($encryption_key) == 0)
        {
            // Generate a 128 bit encryption key
            $length = 16;
            $safe = true;
            $tmp = openssl_random_pseudo_bytes($length, $safe);
            $encryption_key = base64_encode($tmp);
        }
        else 
        {
            // Call base64_decode with strict=true, so invalid base64 results in return value 'false'
            $decoded_encryption_key = base64_decode($encryption_key, true);
        
            // Check if key is correct
            if (strlen($decoded_encryption_key ) != 16)
            {
                $success = false;
                addNotification("Error: Encryption key length invalid or not base64.");
            }
            else 
            {
                // Check if key is unique
                if (!uniqueCheck("clients", "encryption_key", $encryption_key))
                {
                    $success = false;
                    addNotification("Error: Encryption key is not unique.");
                }
            }
        }
    }


    // If the encryption key was valid, we will now check the hash key
    // Lots of code duplication, because the constraints are the same as for the encryption key at the moment!
    if ($success)
    {
        if (strlen($hash_key) == 0)
        {
            // Generate a 128 bit encryption key
            $length = 16;
            $safe = true;
            $tmp = openssl_random_pseudo_bytes($length, $safe);
            $hash_key = base64_encode($tmp);
        }
        else 
        {
            // Call base64_decode with strict=true, so invalid base64 results in return value 'false'
            $decoded_hash_key = base64_decode($hash_key, true);
        
            // Check if key is correct
            if (strlen($decoded_hash_key ) != 16)
            {
                $success = false;
                addNotification("Error: Hash key length invalid or not base64.");
            }
            else 
            {
                // Check if key is unique
                if (!uniqueCheck("clients", "hash_key", $hash_key))
                {
                    $success = false;
                    addNotification("Error: Hash key is not unique.");
                }
            }
        }
    }
    
    // If the hash key was valid, generate rsa key information
    if ($success) 
    {
        $rsa_keys = SdmAsymmetricCrypt::generateKeys();
    }
    
    // If everything checked out, enter the client into the DB.
    if ($success) 
    {
        $qry = SqliteDb::getDb()->prepare("INSERT INTO clients (username, encryption_key, hash_key, rsa_keys) VALUES (:username, :encryption_key, :hash_key, :rsa_keys)");
        $qry->execute(array(':username' => $username, ':encryption_key' => $encryption_key, ':hash_key' => $hash_key, ':rsa_keys' => $rsa_keys));
        addNotification("Success: Client added to database.");
        
        // Re-sync the public keys to the server
        syncKeys();
    }
    
    
    	
    $redirect = true;
}


else if (isset($_GET['action'])) 
{
	if ($_GET['action'] == "delclient" && isset($_GET['id']))
	{
	   $client_id = intval($_GET['id']);
	   
	   $sql = 'DELETE FROM clients WHERE id= :client_id';
	   $qry = SqliteDb::getDb()->prepare($sql);
	   $qry->execute(array(':client_id' => $client_id));

       addNotification('Success: Client information deleted.');
	   
       // Re-sync the database keys
       syncKeys();
       
	   $redirect = true;
	}
    else if ($_GET['action'] == "sync")
    {
        syncKeys();

        $redirect = true;
    }
}



function syncKeys()
{
    XmlRpcStubs::clearPublicKeys();
    
    $qry = SqliteDb::getDb()->query("SELECT * FROM clients ORDER BY id");

    while ($result = $qry->fetch(PDO::FETCH_ASSOC))
    {
        // Each tree_id is the ID of a client, encrypted with that client's encryption_key.
        $tree_id = SdmSymmetricCrypt::simpleClientEncrypt($result['id'], $result['id']);
        
        $pubkey = SdmAsymmetricCrypt::getPublicKey($result['rsa_keys']);

        XmlRpcStubs::addPublicKey($result['id'], $tree_id, $pubkey);
    }

    addNotification("Success: Public keys synced.");
}



if ($redirect)
{    
    header("Location:index.php?page=config");
    exit;
}

?>