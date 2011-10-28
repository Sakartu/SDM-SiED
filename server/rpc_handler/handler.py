import hashlib
from util import util
from util.sig_checker import SigChecker
from db import db
from db.exceptions import SameKeyException
import logging

logger = logging.getLogger()

class SiEDRPCHandler:
    consultant_checker = SigChecker('consultant_key')
    checker = SigChecker()

    def __init__(self, conf):
        self.conf = conf

    def test(self, a, b):
        return str(a) + str(b)

    @checker
    def testcrypto(self, ctext):
        key = hashlib.sha512('jemoeder').digest()
        logger.info(repr(util.decrypt(key, ctext)))

    @consultant_checker
    def add_pubkey(self, sig, client_id, tree_id, pubkey):
        logger.info('Adding key for client {id}.'.format(id=client_id))
        try:
            db.add_pubkey(self.conf, client_id, tree_id, pubkey)
            return "Added key for client {id}".format(id=client_id)
        except SameKeyException:
            return "Tried to add key for client {id} twice!".format(id=client_id)

    @consultant_checker
    def del_pubkey(self, sig, client_id, tree_id):
        logger.info('Removing key for client {id}.'.format(id=client_id))
        db.del_pubkey(self.conf, client_id, tree_id)
        return "Removed key for client {id}".format(id=client_id)

    @checker
    def fetch_pubkey(self, sig, client_id, tree_id):
        logger.info('Fetching key for client {id}'.format(id=client_id))
        result = db.fetch_pubkey(self.conf, client_id, tree_id)
        return result

    @checker
    def insert(self, sig, client_id, tree_id, encrypted_rows):
        try:
            db.insert_tree(self.conf, tree_id, encrypted_rows)
            return True
        except: #if all went well we return True, else we rollback
            return False

    @checker
    def update(self, sig, client_id, tree_id, pre, value):
        pass

    @checker
    def search(self, sig, client_id, tree_id, query, encrypted_content):
        pass

    #TODO: for debugging purposes only, remove when done.
    def clear_db(self):
        db.initialize(self.conf)

