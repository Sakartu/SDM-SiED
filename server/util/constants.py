from M2Crypto import RSA

SIG_HASH_ALG = 'sha512'
SIG_PAD_ALG = RSA.pkcs1_padding

class DB:
    KEY_TABLE = 'keys'
    TREE_TREE_ID = 0
    TREE_PRE = 1
    TREE_POST = 2
    TREE_PARENT = 3
    TREE_CTAG = 4
    TREE_CVAL = 5

class Conf:
    DB_CONN = 'db_conn'

class Algo:
    SPLIT_FACTOR = 0.5
