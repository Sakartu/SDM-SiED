<?php

/**
 * Usage:
 *   $crypt = new SdmCrypt($key128, $iv128);
 *   echo $crypt->decrypt($crypt->encrypt("sup?"));
 * Output will be the original string: "sup?"
 */


class SdmSymmetricCrypt
{
    private $cipher;
    private $key128;
    private $iv128;


    public static function simpleClientEncrypt($clientId, $plainText)
    {
        $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE `id` = :id");
        $qry->execute(array(':id' => $clientId));
        
        $result = $qry->fetch(PDO::FETCH_ASSOC);
        
        $crypt = new SdmSymmetricCrypt(base64_decode($result['encryption_key']));
        return $crypt->encrypt($plainText);
    }
    
    /**
     * Returns an array containing:
     * ['ciphertext'] = E(plaintext) with key $encryption_key
     * ['element_hashkey'] = f(Li) with key $hash_key
     */
    public static function encryptXpathTerm($raw_encryption_key, $raw_hash_key, $plainText)
    {
        // First encrypt plaintext
        $crypt = new SdmSymmetricCrypt($raw_encryption_key);
        $enc_plaintext = $crypt->encrypt($plainText, false);
        
        // Split the encrypted plaintext into two parts: Xi = <Li, Ri>
        // We know we are dealing with a multiple of 16, so just split it in half:
        $l_enc = substr($enc_plaintext, 0, (strlen($enc_plaintext)/2));
        $r_enc = substr($enc_plaintext, (strlen($enc_plaintext)/2));
        
        // Generate this element's unique hash-key based on the left part of the encrypted plaintext
        $crypt = new SdmSymmetricCrypt($raw_hash_key);
        $element_hashkey = $crypt->encrypt($l_enc, false);

        // Each element's hash-key should be 16 bytes wide, so take the last 16 bytes of the encryption.
        $element_hashkey = substr($element_hashkey, strlen($element_hashkey)-16);
        
        $result = array();
        $result[] = base64_encode($enc_plaintext);         // index[0]
        $result[] = base64_encode($element_hashkey);       // index[1]

        return $result;
    }

    public static function decryptSearchResults($encryption_key, $hash_key, $base64_cipherText, $preValue)
    {
        $debug = false;
        $dmsg = '<pre>';
        $dmsg .= "CALL: decryptSearchResults(".$encryption_key.', '.$hash_key.', '.$base64_cipherText.', '.$preValue.")\n";
        
        
        $cipherText = base64_decode($base64_cipherText);
        $lengthM = strlen($cipherText)/2;
        
        // 1) Create the random value Si
        $seedString = hash('sha512',$encryption_key.$preValue, true);
        
        $charcodesum = strSumCharcodes($seedString);
        $dmsg .= 'hash input: '.$encryption_key.'  and  '.$preValue."\n";
        $dmsg .= 'hash output: '.$seedString."\n";
        $dmsg .= 'hash output SUMS TO '.$charcodesum."\n";
        
        mt_srand($charcodesum);
        
        $random_l = '';
        for($i=0; $i < $lengthM; $i++)
        {
            $randval = chr(mt_rand(0, 255));
            $random_l .= $randval;
        }

        $dmsg .= $random_l.'   strlen:'.strlen($random_l)."\n";
        $dmsg .= strToHex($random_l)."\n";
        
        
        // 2) XOR the cipherText with Si to recover the left part of the encrypted plaintext, Li
        $l_enc = $cipherText ^ $random_l;
        
        // 3) Generate the element-specific hash-key
 
        // Generate this element's unique hash-key based on the left part of the encrypted plaintext
        // ki = f(Li)   where f is a hash function keyed with $hash_key
        // Each element's hash-key should be 16 bytes wide, so take the last 16 bytes of the encryption.
        $crypt = new SdmSymmetricCrypt($hash_key);
        $element_hashkey = $crypt->encrypt($l_enc, false);
        
        $dmsg .= "\nelement_hashkey width: ".strlen($element_hashkey)." -> ".$element_hashkey."!!!!\n\n";
        
        $element_hashkey = substr($element_hashkey, strlen($element_hashkey)-16);
         
         
         
        // 4) Generate the complete search-string, by hashing $random_l
        $crypt = new SdmSymmetricCrypt($element_hashkey);
        $random_r = $crypt->encrypt($random_l, false);

        $dmsg .= 'random_r: '.$random_r."\n";
        $dmsg .= 'random_r_strlen: '.strlen($random_r)."\n";
        
        $random_r = substr($random_r, strlen($random_r)-strlen($random_l));

        $dmsg .= 'random_r: '.$random_r."\n";
        $dmsg .= 'random_r_strlen: '.strlen($random_r)."\n";
        
        $searchtext = $random_l.$random_r;

        $enc_plaintext = $cipherText ^ $searchtext;
        $dmsg .= '$cipherText ^ $searchtext == $enc_plaintext'."\n";
        $dmsg .= $cipherText.' ^ '.$searchtext.' == '.$enc_plaintext."\n";
        
        $crypt = new SdmSymmetricCrypt($encryption_key);
        $plaintext = $crypt->decrypt(base64_encode($enc_plaintext), false);
        
        $dmsg .= 'plaintext: '.$plaintext."\n";
        $dmsg .= '</pre>';
        if ($debug)
        {
            echo $dmsg;
        }
                
        
        return $plaintext;
    }

    public static function encryptForSearching($encryption_key, $hash_key, $plainText, $preValue)
    {
        $debug = false;
        $dmsg = '<pre>';
        $dmsg .= "SdmSymmetricCrypt::encryptForSearching(".base64_encode($encryption_key)."\n, ".base64_encode($hash_key)."\n, ".$plainText."\n, ".$preValue.")\n\n";
        
        // First encrypt plaintext to Xi
        $crypt = new SdmSymmetricCrypt($encryption_key);
        $enc_plaintext = $crypt->encrypt($plainText, false);
        
        $dmsg .= "enc_plaintext = ".base64_encode($enc_plaintext).' (rawlength='.strlen($enc_plaintext).")\n";
        
        // We use AES (block size 128, key size 128),so strlen($enc_plaintext) is a multiple of 16 bytes.
         
        
        // Split the encrypted plaintext into two parts: Xi = <Li, Ri>
        // We use n = 2m, so both parts are of equal length. 
        // We know we are dealing with a multiple of 16, so just split it in half:
        $l_enc = substr($enc_plaintext, 0, (strlen($enc_plaintext)/2));
        $r_enc = substr($enc_plaintext, (strlen($enc_plaintext)/2));
        
        //$dmsg .= htmlentities('Encrypted plaintext: '.base64_encode($enc_plaintext).' split into "'.base64_encode($l_enc).'" and "'.base64_encode($r_enc).'"'."\n"."   length N = ".strlen($enc_plaintext));

        // Generate this element's unique hash-key based on the left part of the encrypted plaintext
        // ki = f(Li)   where f is a hash function keyed with $hash_key
        // Each element's hash-key should be 16 bytes wide, so take the last 16 bytes of the encryption.
        $crypt = new SdmSymmetricCrypt($hash_key);
        $element_hashkey = $crypt->encrypt($l_enc, false);
        
        $element_hashkey = substr($element_hashkey, strlen($element_hashkey)-16);
        
        $dmsg .= "element_hashkey: ".base64_encode($element_hashkey).' (rawlength='.strlen($element_hashkey).")\n";
        /* 
         * Then generate Si based on the pre value, and calculate F(Si) where F is a hash function keyed with ki
         * 
         * Does it matter if random value Si is predictable, i.e. direct hash of pre value?
         * Yes, because then pre=1 in each tree would use the same random value. And so if the server learns (because of a query)
         * the ki associated with pre=1, this ki can be reused in other trees with the same encryption key. 
         * 
         * The pseudorandom value Si should be of length m, and F(Si)should also be of length m.
         * They are concatenated to form <Si, F(Si)>, of length n.
         * 
         * We create Si by seeding the random generator with pre+encryption_key, and extracting the required amount of bytes.
         * To create the seed from pre+encryption_key, sum up all the bytes values of the sha512 hash over the concatenation
         * of encryption_key and preValue: bytes_sum(sha512($encryption_key.$preValue))
         */
        // Use hash(algo, data, true) to return the raw binary values.
        $seedString = hash('sha512',$encryption_key.$preValue, true);
        
        $charcodesum = strSumCharcodes($seedString);

        //$dmsg .= 'hash input: '.$encryption_key.'  and  '.$preValue."\n";
        //$dmsg .= 'hash output: '.$seedString."\n";
        //$dmsg .= 'hash output SUMS TO '.$charcodesum."\n";
        
        mt_srand($charcodesum);
        
        $random_l = '';
        for($i=0; $i < strlen($l_enc); $i++)
        {
            $randval = chr(mt_rand(0, 255));
            //echo 'generate:'.$randval."\n";
            $random_l .= $randval;
            
        }
        
        $dmsg .= 'random_left_part = '.base64_encode($random_l).'   (rawlength='.strlen($random_l).")\n";

        // Now hash the left pseudorandom part to get the right part. 
        // The F-hash is actually AES128 encryption with key: $element_haskey
        // The output of the encryption is truncated to fit in n/2 (the width of Li and Ri)
        // (the last n/2 bytes of the encrypted value are used) 
        $crypt = new SdmSymmetricCrypt($element_hashkey);
        $random_r = $crypt->encrypt($random_l, false);
        $dmsg .= "ki = ".base64_encode($element_hashkey).",   sp1 = ".base64_encode($random_l).",   e(ki, sp1) = ".base64_encode($random_r)."\n";

        $dmsg .= 'full_right_part: '.base64_encode($random_r)."   (rawlength=".strlen($random_r).")\n";
        
        $random_r = substr($random_r, strlen($random_r)-strlen($random_l));

        $dmsg .= 'right_part = (random_left_part encrypted with element_hashkey) =  '.base64_encode($random_r).'   (rawlength='.strlen($random_r).")\n";
        
        $searchtext = $random_l.$random_r;
        
        $dmsg .= 'searchtext = concat(random_left_part, right_part) = '.base64_encode($searchtext).'   (rawlength='.strlen($searchtext).")\n";
        
        // Finally XOR the encrypted plaintext and the search-text
        // $encrypted_plaintext XOR ($random_l.$random_r)
        // Length of both these strings should be equal (N)
        (strlen($enc_plaintext) == strlen($searchtext)) or die("Encryption XOR FAILED!");
        
        
        $result = $enc_plaintext ^ $searchtext;
        $dmsg .= 'enc_plaintext ^ searchtext == ciphertext'."\n";
        $dmsg .= base64_encode($enc_plaintext)."\n^\n".base64_encode($searchtext)."\n == \n".base64_encode($result)."\n\n\n";
        
        $result = base64_encode($result);
        
        $dmsg .= '</pre>';
        if ($debug)
        {
            echo $dmsg;
        }

        return $result; 
    }


    // this gets called when class is instantiated
    public function __construct($key128, $iv128="\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0")
    {
        if(extension_loaded('mcrypt') === FALSE)
        {
            die('The Mcrypt module could not be loaded.');
        }

        $this->cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv_size = mcrypt_enc_get_iv_size($this->cipher);

        if (!isset($key128) || (strlen($key128) != 16) || !isset($iv128) || (strlen($iv128) != $iv_size))
        {
            die('The key or IV size was not correct: "'.$key128. '" "'.$iv128.'"');
        }
        //echo "\nSDMSymmetricCrypt CONSTRUCT with key == ".base64_encode($key128)."\n";

        $this->key128 = $key128;
        $this->iv128 = $iv128;
    }
    
    public function encrypt($plainText, $base64=true)
    {
        $result = "";

        // do 128-bit AES encryption:
        if (mcrypt_generic_init($this->cipher, $this->key128, $this->iv128) != -1)
        {
            $plain = trim($plainText);
            if (empty($plain))
            {
                $plain = "\0";
            }
            // PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
            
            // but we want to do EXPLICIT PKCS5 PADDING to block size of 16 bytes
            $plain = pkcs5_pad($plain, 16);             
            
            $cipherText = mcrypt_generic($this->cipher,$plain);
            //echo "mcrypt_generic(".base64_encode($this->key128).", ".$plain.") b64plain == ".base64_encode($plain)."\n";

            // Display the result in b64.
            //echo (sprintf("128-bit encrypted result: %s\n",base64_encode($cipherText)));

            if ($base64)
            {
                $result = base64_encode($cipherText);
            }
            else 
            {
                $result = $cipherText;
            }
            mcrypt_generic_deinit($this->cipher);
        }

        return $result;
    }

    public function decrypt($b64_cipherText)
    {
        $result = "";

        // Let's decrypt the 128-bit cipherText.
        if (mcrypt_generic_init($this->cipher, $this->key128, $this->iv128) != -1)
        {
            $cipherText = base64_decode($b64_cipherText);
            $plainText = mdecrypt_generic($this->cipher, $cipherText);

            //unpad PKCS5
            $plainText = pkcs5_unpad($plainText);
            
            //echo (sprintf("128-bit decrypted result: \n%s\n\n",$plainText));

            $result = trim($plainText);
            mcrypt_generic_deinit($this->cipher);
        }

        return $result;
    }

}

?>