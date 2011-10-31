from M2Crypto import RSA

SIG_HASH_ALG = 'sha512'
SIG_PAD_ALG = RSA.pkcs1_padding

class DB:
    KEY_TABLE = 'keys'

class Conf:
    DB_CONN = 'db_conn'

class Algo:
    SPLIT_FACTOR = 0.5
