import hashlib
from util import xpath, util, constants
from util.sig_checker import SigChecker
from db import db
from base64 import b64decode, b64encode
from operator import itemgetter
from db.exceptions import SameKeyException
import logging

logger = logging.getLogger()

class SiEDRPCHandler(object):
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
        except Exception, e: # if all went well we return True, else we rollback
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
            return []

        (tokens, sep, right) = query.partition('[')
        tokens = tokens.split('/')
        if sep and right:
            tokens.append(sep + right)

        if tokens[0] == '': # we had a slash in front
            tokens = tokens[1:]

        #        "bla/bla2//bla3[bla4=bla5]"
        #                    V
        # ['bla', 'bla2', '', 'bla3', '[bla4=bla5]']
        records = db.fetch_tree(self.conf, tree_id)
        all_records = records[:]
        # filter records according to query
        i = 0
        while i < len(tokens):
            token = tokens[i]
            if token == '':
                # so we had a //, consume the next token as well
                # we first retrieve the corresponding encrypted_content:
                i += 1
                token = tokens[i]
                node = int(token)
                xi = b64decode(encrypted_content[node][0])
                ki = b64decode(encrypted_content[node][1])
                # now we look for every node that matches among all descendants
                descendants = xpath.get_all_descendants(all_records, records)
                records = util.matching(descendants, xi, ki, constants.DB.TREE_CTAG)
                print 'got //, records are', records
                i += 1
            elif not '[' in token:
                # so a normal node
                # we first retrieve the corresponding encrypted_content:
                node = int(token)
                xi = b64decode(encrypted_content[node][0])
                ki = b64decode(encrypted_content[node][1])
                # now we look for every node that matches a child of the given roots
                children = xpath.get_all_children(all_records, records)
                records = util.matching(children, xi, ki, constants.DB.TREE_CTAG)
                print 'got normal node, records are', records
                i += 1
            else:
                # so an attribute, we have to check both tagname and value
                nodes = token.split('=')
                tag_node = int(nodes[0][1:]) # to strip off the leading [
                tag_xi = b64decode(encrypted_content[tag_node][0])
                tag_ki = b64decode(encrypted_content[tag_node][1])
                records = util.matching(records, tag_xi, tag_ki, constants.DB.TREE_CTAG)
                val_node = int(nodes[1][:-1]) # to strip off the trailing ]
                val_xi = b64decode(encrypted_content[val_node][0])
                val_ki = b64decode(encrypted_content[val_node][1])
                records = util.matching(records, val_xi, val_ki, constants.DB.TREE_CVAL)
                # we have a list of matching attribute nodes, now find their parents
                records = xpath.get_parents(all_records, records)
                print 'done parsing, results are', records
                i += 1
        # we have a list of matching roots, now retrieve entire subtree for each root
        if records:
            result = [db.fetch_subtree(self.conf, x) for x in records]
            # sort records list based on pre values.
            print sorted(result, key=lambda x : x[0][1])
            return sorted(result, key=lambda x : x[0][1])
        else:
            return []

    # TODO: for debugging purposes only, remove when done.
    def clear_db(self):
        db.initialize(self.conf)

