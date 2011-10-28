<?php


/*
$privateKey = openssl_pkey_new(array(
    'private_key_bits' => 1024,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
)); 
 
echo "<pre>";
print_r($privateKey);
echo "--done--";
echo "</pre>";
die();


$crypt = new SdmAsymmetricCrypt();
$res = $crypt->generateKeys();

echo "<pre>";
openssl_pkey_export($res['private'], $outt);
print_r($outt);

        
        $keyDetails = openssl_pkey_get_details($privateKey);
        $publicKey = $keyDetails['key'];
        
        $result = array();
        $result['private'] = $privateKey;
        $result['public'] = $publicKey;
        
 * 
 * 
 * 
 * 
echo "<br/>";
$p1 = openssl_get_privatekey($outt);
$p2 = openssl_pkey_get_details($p1);
echo($p1."<br/>".$p2['key']);
//print_r($res);
//get_class($res['private']);
//get_class_vars($res['private']);
echo "</pre>";
*/


class SdmAsymmetricCrypt
{

    public static function generateKeys() 
    {
        $privateKey = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));
        
        openssl_pkey_export($privateKey, $result);
        
        return $result;
    }


    public static function encrypt($clientId, $message)
    {
        //openssl_private_encrypt()
    }
    
    public static function decrypt($clientId, $message)
    {
        
    }
    
    public static function sign()
    {
    /*
    The list of Signature Algorithms (constants) is very limited! Fortunately the newer versions of php/openssl allow you to specify the signature algorithm as a string.
    
    You can use the 'openssl_get_md_methods' method to get a list of digest methods. Only some of them may be used to sign with RSA private keys.
    
    Those that can be used to sign with RSA private keys are: md4, md5, ripemd160, sha, sha1, sha224, sha256, sha384, sha512
    
    Here's the modified Example #1 with SHA-512 hash:
    <?php
    // $data is assumed to contain the data to be signed
    
    // fetch private key from file and ready it
    $fp = fopen("/src/openssl-0.9.6/demos/sign/key.pem", "r");
    $priv_key = fread($fp, 8192);
    fclose($fp);
    $pkeyid = openssl_get_privatekey($priv_key);
    
    // compute signature with SHA-512
    openssl_sign($data, $signature, $pkeyid, "sha512");
    
    // free the key from memory
    openssl_free_key($pkeyid);
    */
        
    }
    

}

?>