from M2Crypto import EVP, RSA, BIO
import constants


# AES Encryption stuff
def decrypt(key, data):
    ''' 
    A decryption function which takes a keyfile and a ciphertext and returns
    the AES decrypted plaintext of this ciphertext
    '''
    cipher = EVP.Cipher(alg='aes_128_cbc', key=key, iv='\0' * 16, padding=False, op=0)
    dec = cipher.update(data)
    dec += cipher.final()
    return dec.rstrip("\0")

def encrypt(key, data):
    ''' 
    An encryption function which takes a keyfile and a plaintext and returns
    the AES encrypted ciphertext of this plaintext
    '''
    cipher = EVP.Cipher(alg='aes_128_cbc', key=key, iv='\0' * 16, padding=False, op=1)
    dec = cipher.update(padr(data,256/8))
    dec += cipher.final()
    return dec

# Padding
def paddedlength(data,n):
    if len(data) % n == 0:
        return len(data)
    return len(data) + (n - (len(data) % n))

def padr(data,n,c='\0'):
    return data.ljust(paddedlength(data,n),c)

def padl(data,n,c='\0'):
    return data.rjust(paddedlength(data,n),c)

def chunks(data,n):
    return [data[i:i+n] for i in range(0, len(data), n)]


# RSA signature stuff
def sign(keystring, is_file=False, *data):
    '''
    This helper method signs the string representation of an arbitrary number of
    arguments with the given private key string. The key string can either be a
    stringrepresentation of a private key or the path to a .pem file.
    '''
    if is_file:
        bio = BIO.openfile(keystring)
    else:
        bio = BIO.MemoryBuffer(keystring)
    #assume key is a keystring
    signEVP = EVP.load_key_bio(bio)
    signEVP.sign_init()
    signEVP.sign_update(digest(data))
    return signEVP.sign_final()

def check_sign(keystring, sig, is_file=False, *data):
    '''
    This helper method verifies the signature over the string representation of
    an arbitrary number of arguments. The key string can either be a
    stringrepresentation of a public key or the path to a .pem file
    '''
    if is_file:
        bio = BIO.openfile(keystring)
    else:
        bio = BIO.MemoryBuffer(keystring)
    rsa = RSA.load_pub_key_bio(bio)
    pubkey = EVP.PKey()
    pubkey.assign_rsa(rsa)

    pubkey.verify_init()
    pubkey.verify_update(digest(data))
    if pubkey.verify_final(sig) == 1:
        return True
    else:
        return False

def digest(*data):
    d = "".join(str(a) for a in data[0])
    dgst = EVP.MessageDigest(constants.SIG_HASH_ALG)
    dgst.update(d)
    return dgst.digest()
