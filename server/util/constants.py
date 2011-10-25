from M2Crypto import RSA
SIG_HASH_ALG = 'sha1'
SIG_PAD_ALG = RSA.pkcs1_padding

class DB:
    KEY_TABLE = 'keys'
