from util import constants, util
import sqlite3
import os
import logging
from exceptions import SameKeyException

logger = logging.getLogger()

def initialize(conf):
    # setup db
    logger.info('Setting up database...')
    db_path = util.preparse_path(conf['db_location'])

    #remove db if we're debugging
    #if 'debug' in conf and conf['debug']:
    #    try:
    #        os.remove(db_path)
    #    except:
    #        pass

    if not os.path.exists(os.path.dirname(db_path)):
        os.makedirs(os.path.dirname(db_path))
    conn = sqlite3.connect(db_path)
    conf[constants.Conf.DB_CONN] = conn

    # init tables
    with conn:
        logger.info('Building tables...')
        c = conn.cursor()
        c.execute('''CREATE TABLE IF NOT EXISTS pubkeys(client_id int, tree_id
                text, pubkey text)''')
        c.execute('''CREATE UNIQUE INDEX IF NOT EXISTS pubkey_index ON
                pubkeys(client_id, tree_id)''')
        c.execute('''CREATE TABLE IF NOT EXISTS trees(tree_id text, pre int,
                post int, parent int, ctag BLOB, cval BLOB)''')
        c.execute('''CREATE UNIQUE INDEX IF NOT EXISTS tree_index ON
                trees(tree_id, pre, post, parent)''')

def add_pubkey(conf, client_id, tree_id, pubkey):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        try:
            c.execute('INSERT INTO pubkeys VALUES (?, ?, ?)', (client_id, tree_id, pubkey))
        except sqlite3.IntegrityError:
            logger.warn('Tried to insert pubkey for client {id} twice!'.format(id=client_id))
            raise SameKeyException

def clear_keys(conf):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('DELETE FROM pubkeys')

def del_pubkey(conf, client_id, tree_id):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('DELETE FROM pubkeys WHERE client_id = ? AND tree_id = ?', (client_id, tree_id))

def fetch_pubkey(conf, client_id, tree_id):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('SELECT pubkey FROM pubkeys WHERE client_id = ? AND tree_id = ?', (client_id, tree_id))
        try:
            return c.fetchone()[0]
        except:
            return None

def insert_tree(conf, tree_id, encrypted_rows):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        # first we delete all the required rows:
        c.execute('DELETE FROM trees WHERE tree_id = ?', (tree_id,))
        # then we reinsert
        c.executemany('INSERT INTO trees VALUES (?, ?, ?, ?, ?, ?)', encrypted_rows)

def update_tree(conf, tree_id, pre, ctag, cval):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('''UPDATE trees SET ctag = ?, cval = ? WHERE tree_id = ? and
                pre = ?''', (ctag, cval, tree_id, pre,))

def fetch_tree(conf, tree_id):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('''SELECT * FROM trees WHERE tree_id = ?''', (tree_id,))
        return c.fetchall()

def fetch_descendants(conf, root, tree_id):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('''SELECT * FROM TREES WHERE pre > ? AND post < ? AND tree_id = ?''', (root[constants.DB.TREE_PRE], root[constants.DB.TREE_POST], tree_id))
        return c.fetchall()

def fetch_subtree(conf, root, tree_id):
    desc = fetch_descendants(conf, root, tree_id)
    desc.append(root)
    return desc

