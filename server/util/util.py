from M2Crypto import EVP

def decrypt(key, data):
    """ 
    A decryption function which takes a keyfile and a ciphertext and returns
    the AES decrypted plaintext of this ciphertext
    """
    cipher = EVP.Cipher(alg='aes_128_cbc', key=key, iv='\0' * 16, padding=False, op=0)
    dec = cipher.update(data)
    dec += cipher.final()
    return dec.rstrip("\0")

def encrypt(key, data):
    """ 
    An encryption function which takes a keyfile and a plaintext and returns
    the AES encrypted ciphertext of this plaintext
    """
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
