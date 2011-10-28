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

        $this->key128 = $key128;
        $this->iv128 = $iv128;
    }


    public function encrypt($plainText)
    {
        $result = "";

        // Now let's do 128-bit encryption:
        if (mcrypt_generic_init($this->cipher, $this->key128, $this->iv128) != -1)
        {
            // PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
            $cipherText = mcrypt_generic($this->cipher,trim($plainText));

            // Display the result in hex.
            //echo (sprintf("128-bit encrypted result:\n%s\n\n",bin2hex($cipherText)));

            $result = base64_encode($cipherText);
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

            //echo (sprintf("128-bit decrypted result: \n%s\n\n",$plainText));

            $result = trim($plainText);
            mcrypt_generic_deinit($this->cipher);
        }

        return $result;
    }

}

?>