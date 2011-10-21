import hashlib
import util.util as util

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

    #__insert(treeID, EncryptedRows[])__
    def insert(tree_id, encrypted_rows):
        pass

    #__update(treeID, int pre, byte[] value)__
    def update(tree_id, pre, value):
        pass

    #__search(treeID, XPathEncrypted)__
    def search(tree_id, encrypted_xpath):
        pass

