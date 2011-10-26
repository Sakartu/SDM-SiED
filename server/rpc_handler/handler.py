import hashlib
import util.util as util
from util.sig_checker import SigChecker
import db.db as db
import logging

logger = logging.getLogger()



class SiEDRPCHandler:
    checker = SigChecker()
    def __init__(self, conf):
        self.conf = conf

    def test(self, a, b):
        print type(a), type(b)
        return str(a) + str(b)

    @checker
    def testcrypto(self, ctext):
        key = hashlib.sha512('jemoeder').digest()
        logger.info(repr(util.decrypt(key, ctext)))

    @checker
    def add_pubkey(self, sig, client_id, tree_id, pubkey):
        logger.info('Adding key for client {id}.'.format(id=client_id))
        db.add_pubkey(self.conf, client_id, tree_id, pubkey)
        return "Added key for client {id}".format(id=client_id)

    @checker
    def insert(self, sig, client_id, tree_id, encrypted_rows):
        pass

    @checker
    def update(self, sig, client_id, tree_id, pre, value):
        pass

    @checker
    def search(self, sig, client_id, tree_id, query, encrypted_content):
        pass

