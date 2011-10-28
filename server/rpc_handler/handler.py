import hashlib
from util import xpath, util
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
    def clear_keys(self, sig):
        logger.info('Clearing all keys!.')
        try:
            db.clear_keys(self.conf)
            return True
        except:
            return False


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
            logger.info('Inserting new rows for {id}.'.format(id=client_id))
            db.insert_tree(self.conf, tree_id, encrypted_rows)
            return True
        except Exception, e: #if all went well we return True, else we rollback
            logger.error(e)
            return False

    @checker
    def update(self, sig, client_id, tree_id, pre, ctag, cvalue):
        try:
            db.update_tree(self.conf, tree_id, pre, ctag, cvalue)
            return True
        except:
            return False

    @checker
    def search(self, sig, client_id, tree_id, query, encrypted_content):
        if not query:
            return False

        tokens = query.split('/')
        if tokens[0] == '':
            tokens = tokens[1:]

        #>>> "bla/bla2//bla3[bla4=bla5]".split('/')
        #['bla', 'bla2', '', 'bla3[bla4=bla5]']
        records = db.fetch_tree(self.conf, tree_id)

        for token in tokens:
            if token == '':
                #so we had a //
                pass
            elif not '[' in token:
                pass
                #so a normal node
            else:
                pass
                #so an attribute


        pass

    #TODO: for debugging purposes only, remove when done.
    def clear_db(self):
        db.initialize(self.conf)

