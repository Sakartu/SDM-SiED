import hashlib
from util import xpath, util, constants
from util.sig_checker import SigChecker
from db import db
from base64 import b64decode, b64encode
from binascii import hexlify
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

        logger.info('Handling query "{0}".'.format(query))
        logger.info('Using mapping:')
        for (k, v) in dict(enumerate(encrypted_content)).items():
            logger.info('\t\t{k} : {v}'.format(k=str(k), v=str(v)))
        (tokens, sep, right) = query.partition('[')
        tokens = tokens.split('/')
        if sep and right:
            tokens.append(sep + right)

        # if we have a slash in front, just discard it, we only do absolute paths
        if tokens[0] == '': 
            tokens = tokens[1:]

        #        "bla/bla2//bla3[bla4=bla5]"
        #                    V
        # ['bla', 'bla2', '', 'bla3', '[bla4=bla5]']
        all_records = db.fetch_tree(self.conf, tree_id)

        # start with a virtual rootnode that's the parent of the first node
        records = [(None, -1, len(all_records) + 1, -1, None, None)]

        # filter records according to query
        i = 0
        while i < len(tokens):
            token = tokens[i]
            if token == '':
                logger.info('Fetching descendants and looking for match...')
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
                logger.info('Found {0} matching descendants, continuing!'.format(len(records)))
                logger.debug('Got token //, records are: ' + str(records))
                i += 1
            elif not '[' in token:
                logger.info('Fetching children and looking for match...')
                # so a normal node
                # we first retrieve the corresponding encrypted_content:
                node = int(token)
                xi = b64decode(encrypted_content[node][0])
                ki = b64decode(encrypted_content[node][1])
                # now we look for every node that matches a child of the given roots
                children = xpath.get_all_children(all_records, records)
                records = util.matching(children, xi, ki, constants.DB.TREE_CTAG)
                logger.info('Found {0} matching children, continuing!'.format(len(records)))
                logger.debug('Got normal node (/), records are: ' + str(records))
                i += 1
            else:
                logger.info('Fetching attribute names and looking for match...')
                # so an attribute, we have to check both tagname and value
                nodes = token.translate(None, '["]').split('=') #strip off crap
                tag_node = int(nodes[0])
                tag_xi = b64decode(encrypted_content[tag_node][0])
                tag_ki = b64decode(encrypted_content[tag_node][1])
                # we need to match the attributes, so we first retrieve those
                children = xpath.get_all_children(all_records, records)
                records = util.matching(children, tag_xi, tag_ki, constants.DB.TREE_CTAG)
                logger.info('Found {0} tag matching records, filtering...'.format(len(records)))
                val_node = int(nodes[1])
                val_xi = b64decode(encrypted_content[val_node][0])
                val_ki = b64decode(encrypted_content[val_node][1])
                children = xpath.get_all_children(all_records, records)
                records = util.matching(children, val_xi, val_ki, constants.DB.TREE_CVAL)
                logger.info('Found {0} val matching records, returning!'.format(len(records)))
                # we have a list of matching attribute nodes, now find the
                # corresponding nodes
                records = xpath.get_all_parents(all_records, records)
                records = xpath.get_all_parents(all_records, records)
                logger.debug('Done parsing, results are: ' + str(records))
                i += 1
        # we have a list of matching roots, now retrieve entire subtree for each root
        if records:
            result = [db.fetch_subtree(self.conf, x) for x in records]
            # sort records list based on pre values.
            return [sorted(x, key=lambda x : x[1]) for x in result]
        else:
            return []

    # TODO: for debugging purposes only, remove when done.
    def clear_db(self):
        db.initialize(self.conf)

