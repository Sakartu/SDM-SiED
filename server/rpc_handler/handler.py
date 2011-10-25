import hashlib
import util.util as util
import util.constants as constants

class SiEDRPCHandler:
    def __init__(self, conf):
        self.conf = conf

    def test(self, a, b):
        print type(a), type(b)
        print dir(a)
        print str(a.data)
        print str(a.encode)
        print str(a.decode)
        return str(a) + str(b)

    def testcrypto(self, ctext):
        key = hashlib.sha512('jemoeder').digest()
        print repr(util.decrypt(key, ctext))

    # __add_pubkey(base64 sig, base64 pubkey)__
    def add_pubkey(sig, pubkey):
        #first we check the validity of the query using sig
        pass

    # __insert(base64 sig, base64 treeID, string[] EncryptedRows)__
    def insert(sig, treeID, encrypted_rows):
        pass

    # __update(base64 sig, base64 treeID, int pre, base64 value)__
    def update(sig, treeID, pre, value):
        pass

    # __search(base64 sig, base64 treeID, string query, base64[] encrypted_content)__
    def search(sig, treeID, query, encrypted_content):
        pass

