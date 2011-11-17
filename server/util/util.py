from M2Crypto import EVP, RSA, BIO
import constants
import os
from base64 import b64decode
from binascii import hexlify

# AES Encryption stuff
def decrypt(key, data):
    ''' 
    A decryption function which takes a keyfile and a ciphertext and returns
    the AES decrypted plaintext of this ciphertext
    '''
    cipher = EVP.Cipher(alg='aes_128_cbc', key=key, iv='\0' * 16, padding=True, op=0)
    dec = cipher.update(data)
    dec += cipher.final()
    return dec

def encrypt(key, data):
    ''' 
    An encryption function which takes a keyfile and a plaintext and returns
    the AES encrypted ciphertext of this plaintext
    '''
    cipher = EVP.Cipher(alg='aes_128_cbc', key=key, iv='\0' * 16, padding=True, op=1)
    enc = cipher.update(data)
    enc += cipher.final()
    return enc


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
        bio = BIO.MemoryBuffer(str(keystring))
    #assume key is a keystring
    signEVP = EVP.load_key_bio(bio)
    signEVP.reset_context(md='sha512')
    signEVP.sign_init()
    d = "".join(str(a) for a in data)
    signEVP.sign_update(d)
    sig = signEVP.sign_final()
    return sig

def check_sign(keystring, sig, is_file=False, *data):
    '''
    This helper method verifies the signature over the string representation of
    an arbitrary number of arguments. The key string can either be a
    stringrepresentation of a public key or the path to a .pem file
    '''
    #print "keystring: ", keystring
    #print "sig: ", sig
    #print "data: ", str(data), type(data)
    if is_file:
        bio = BIO.openfile(keystring)
    else:
        bio = BIO.MemoryBuffer(str(keystring))
    rsa = RSA.load_pub_key_bio(bio)
    pubkey = EVP.PKey(md='sha512')
    pubkey.assign_rsa(rsa)

    pubkey.verify_init()
    d = "".join(str(a) for a in data)
    pubkey.verify_update(d)
    if pubkey.verify_final(sig) == 1:
        return True
    else:
        return False

def digest(*data):
    d = "".join(str(a) for a in data)
    dgst = EVP.MessageDigest(constants.SIG_HASH_ALG)
    dgst.update(d)
    return dgst.digest()


# filesystem stuff
def preparse_path(path):
    return os.path.expanduser(os.path.expandvars(path))


# paper algo stuff
def matching(rows, xi, ki, index):
    result = []
    for row in rows:
        check_val = b64decode(row[index])
        tp = string_xor(check_val, xi)
        print "check_val:", hexlify(check_val)
        print "xi:", hexlify(xi)
        print "tp:", hexlify(tp)
        sp1 = tp[:int(len(tp)*constants.Algo.SPLIT_FACTOR)]
        sp2 = tp[int(len(tp)*constants.Algo.SPLIT_FACTOR):]
        print "sp1, sp2:", hexlify(sp1), hexlify(sp2)
        print "enc(sp1):", hexlify(encrypt(ki, sp1))
        # assuming sp2 and encrypt(ki, sp1) are of equal length, if not, don't
        # append
        if sp2 == encrypt(ki, sp1)[len(sp2):]:
            result.apped(row)

def string_xor(a, b):
    return ''.join(chr(ord(x) ^ ord(y)) for (x,y) in zip(a,b))

