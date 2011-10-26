import hashlib
import util.util as util
import db.db as db
import logging

logger = logging.getLogger()

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
        logger.info(repr(util.decrypt(key, ctext)))

    def add_pubkey(self, sig, client_id, tree_id, pubkey):
        #first we check the validity of the query using sig
        if self.conf['check_sigs'] and not self.__check_sig(sig, pubkey):
            logger.warn('Received command for which signature doesn\'t match, ignoring!')
            return
        #then we add the pubkey to the database
        db.add_pubkey(self.conf, client_id, tree_id, pubkey)

    def insert(self, sig, treeID, encrypted_rows):
        pass

    def update(self, sig, treeID, pre, value):
        pass

    def search(self, sig, treeID, query, encrypted_content):
        pass

    def __check_sig(self, sig, *args):
        pass

