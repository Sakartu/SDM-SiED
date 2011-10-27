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
        pass

    @checker
    def fetch_pubkey(self, sig, client_id, tree_id):
        pass

    @checker
    def insert(self, sig, client_id, tree_id, encrypted_rows):
        pass

    @checker
    def update(self, sig, client_id, tree_id, pre, value):
        pass

    @checker
    def search(self, sig, client_id, tree_id, query, encrypted_content):
        pass

    def clear_db(self, password):
        if password == self.conf['clear_db']:
            db.initialize(self.conf)

