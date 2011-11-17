<?php

//SdmAsymmetricCrypt::publicDecrypt(1, SdmAsymmetricCrypt::privateEncrypt(1, "encrypt_this"));

class SdmAsymmetricCrypt
{
    
    public static function generateKeys() 
    {
        $privateKey = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));
        
        openssl_pkey_export($privateKey, $result);
        
        openssl_free_key($privateKey);
        return $result;
    }


    public static function privateEncrypt($clientId, $message)
    {
        $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE `id` = :id");
        $qry->execute(array(':id' => $clientId));
        $result = $qry->fetch(PDO::FETCH_ASSOC);
        
        $rsa_keys = $result['rsa_keys'];
        $pKey = openssl_get_privatekey($rsa_keys);
        
        $result = '';
        openssl_private_encrypt($message, $result, $pKey);
        
        //echo "\n".$result."\nlen:".strlen($result);
        
        openssl_free_key($pKey);
        return $result;
    }
    
    public static function publicDecrypt($clientId, $cipher)
    {
        $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE `id` = :id");
        $qry->execute(array(':id' => $clientId));
        $result = $qry->fetch(PDO::FETCH_ASSOC);
        
        $rsa_keys = $result['rsa_keys'];
        $pKey = openssl_get_privatekey($rsa_keys);
        $keyDetails = openssl_pkey_get_details($pKey);
        $publicKey = $keyDetails['key'];
        
        $result = '';
        openssl_public_decrypt($cipher, $result, $publicKey); 
        
        //echo "\n".$result."\nlen:".strlen($result);
        
        openssl_free_key($pKey);
        return $result;
    }
    
    /**
     * Extracts the public key from the RSA PEM format.
     */
    public static function getPublicKey($pemFormat)
    {
            $privateKey = openssl_get_privatekey($pemFormat);
            $keyDetails = openssl_pkey_get_details($privateKey);
            $publicKey = $keyDetails['key'];
            
            openssl_free_key($privateKey);
            return $publicKey;
    }
    
    public static function clientSign($clientId, $message)
    {
        $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE `id` = :id");
        $qry->execute(array(':id' => $clientId));
        $result = $qry->fetch(PDO::FETCH_ASSOC);
        
        $rsa_keys = $result['rsa_keys'];
        $pKey = openssl_get_privatekey($rsa_keys);

        //echo "<pre>".$clientId."\n\n\n".$message."   --  ".$pKey.'  with sha512'."\n";
        
        $result = '';
        openssl_sign($message, $result, $pKey, "sha512");
        $result = base64_encode($result);
        
        //echo "\n".$result."\nlen:".strlen($result)."</pre>";
        
        openssl_free_key($pKey);
        return $result;
    }
    
    public static function consultantSign($message)
    {        
        $fp = fopen($_SESSION['consultant_pem_loc'], "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);

        $pKey = openssl_get_privatekey($priv_key);
        
        // echo "<pre>".$priv_key."\n\n\n".$message."   --  ".$pKey.'  with sha512'."\n";
        
        $result = '';
        openssl_sign($message, $result, $pKey, "sha512");
        $result = base64_encode($result);
        
        // echo "\nRESULT:".$result."\nlen:".strlen($result)."</pre>";

        // free the key from memory
        openssl_free_key($pKey);
        return $result;
    }
    

}

?>