<?php
    
    // This PHP code snippet provides a basic understanding of 
    // PHP's AES encryption.

    // The first thing to understand is the meaning of these constants:
    // MCRYPT_RIJNDAEL_128
    // MCRYPT_RIJNDAEL_192
    // MCRYPT_RIJNDAEL_256
    // You would think that MCRYPT_RIJNDAEL_256 specifies 256-bit encryption,
    // but that is wrong.  The three choices specify the block-size to be used
    // with Rijndael encryption.  They say nothing about the key size (i.e. strength)
    // of the encryption.  (Read further to understand how the strength of the
    // AES encryption is set.)
    //
    // The Rijndael encyrption algorithm is a block cipher.  It operates on discrete 
    // blocks of data.  Padding MUST be added such that
    // the data to be encrypted has a length that is a multiple of the block size.
    // (PHP pads with NULL bytes)
    // Thus, if you specify MCRYPT_RIJNDAEL_256, your encrypted output will always
    // be a multiple of 32 bytes (i.e. 256 bits).  If you specify MCRYPT_RIJNDAEL_128,
    // your encrypted output will always be a multiple of 16 bytes.
    //
    // Note: Strictly speaking, AES is not precisely Rijndael (although in practice 
    // they are used interchangeably) as Rijndael supports a larger range of block 
    // and key sizes; AES has a fixed block size of 128 bits and a key size of 
    // 128, 192, or 256 bits, whereas Rijndael can be specified with key and block 
    // sizes in any multiple of 32 bits, with a minimum of 128 bits and a maximum of 
    // 256 bits.
    // In summary: If you want to be AES compliant, always choose MCRYPT_RIJNDAEL_128.
    //
    // So the first step is to create the cipher object:
    $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
    
    // We're using CBC mode (cipher-block chaining).  Block cipher modes are detailed
    // here: http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation
    
    // CBC mode requires an initialization vector.  The size of the IV (initialization
    // vector) is always equal to the block-size.  (It is NOT equal to the key size.)
    // Given that our block size is 128-bits, the IV is also 128-bits (i.e. 16 bytes).
    // Thus, for AES encryption, the IV is always 16 bytes regardless of the 
    // strength of encryption.
    // 
    // Here's some PHP code to verify our IV size:
    $iv_size = mcrypt_enc_get_iv_size($cipher);
    printf("iv_size = %d\n",$iv_size);
    
    // How do you do 256-bit AES encryption in PHP vs. 128-bit AES encryption???
    // The answer is:  Give it a key that's 32 bytes long as opposed to 16 bytes long.
    // For example:
    $key256 = '12345678901234561234567890123456';
    $key128 = '1234567890123456';
    
    // Here's our 128-bit IV which is used for both 256-bit and 128-bit keys.
    $iv =  '1234567890123456';
    
    printf("iv: %s\n",bin2hex($iv));
    printf("key256: %s\n",bin2hex($key256));
    printf("key128: %s\n",bin2hex($key128));
    
    // This is the plain-text to be encrypted:
    $cleartext = 'The quick brown fox jumped over the lazy dog';
    printf("plainText: %s\n\n",$cleartext);
        
    // The mcrypt_generic_init function initializes the cipher by specifying both
    // the key and the IV.  The length of the key determines whether we're doing
    // 128-bit, 192-bit, or 256-bit encryption.  
    // Let's do 256-bit encryption here:
    if (mcrypt_generic_init($cipher, $key256, $iv) != -1)
    {
        // PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
        $cipherText = mcrypt_generic($cipher,$cleartext );
        mcrypt_generic_deinit($cipher);
        
        // Display the result in hex.
        printf("256-bit encrypted result:\n%s\n\n",bin2hex($cipherText));
    }
    
    // Now let's do 128-bit encryption:
    if (mcrypt_generic_init($cipher, $key128, $iv) != -1)
    {
        // PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
        $cipherText = mcrypt_generic($cipher,$cleartext );
        mcrypt_generic_deinit($cipher);

        // Display the result in hex.
        printf("128-bit encrypted result:\n%s\n\n",bin2hex($cipherText));
    }
    
    // Let's decrypt the 128-bit cipherText.
    if  (mcrypt_generic_init($cipher, $key128, $iv) != -1)
    {
        $plainText = mdecrypt_generic($cipher, $cipherText);
        printf("128-bit decrypted result: \n%s\n\n",$plainText);
        mcrypt_generic_deinit($cipher);
        echo strToHex(trim($plainText));
    }

    
    // -------
    // Results
    // -------
    // You may use these as test vectors for testing your AES implementations...
    // 
    // ------------------------
    // 256-bit key, CBC mode
    // ------------------------
    // IV = '1234567890123456'  
    //  (hex: 31323334353637383930313233343536)
    // Key = '12345678901234561234567890123456'  
    //  (hex: 3132333435363738393031323334353631323334353637383930313233343536)
    // PlainText:
    //  'The quick brown fox jumped over the lazy dog'
    // CipherText(hex):
    //  2fddc3abec692e1572d9b7d629172a05caf230bc7c8fd2d26ccfd65f9c54526984f7cb1c4326ef058cd7bee3967299e3

    // 
    // ------------------------
    // 128-bit key, CBC mode
    // ------------------------
    // IV = '1234567890123456'  
    //  (hex: 31323334353637383930313233343536)
    // Key = '1234567890123456'  
    //  (hex: 31323334353637383930313233343536)
    // PlainText:
    //  'The quick brown fox jumped over the lazy dog'
    // CipherText(hex):
    //  f78176ae8dfe84578529208d30f446bbb29a64dc388b5c0b63140a4f316b3f341fe7d3b1a3cc5113c81ef8dd714a1c99
    
?>