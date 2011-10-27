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
    if 'debug' in conf and conf['debug']:
        try:
            os.remove(db_path)
        except:
            pass

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

def add_pubkey(conf, client_id, tree_id, pubkey):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        try:
            c.execute('INSERT INTO pubkeys VALUES (?, ?, ?)', (client_id, tree_id, pubkey))
        except sqlite3.IntegrityError:
            logger.warn('Tried to insert pubkey for client {id} twice!'.format(id=client_id))
            raise SameKeyException


def fetch_key(conf, client_id, tree_id):
    with conf[constants.Conf.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('SELECT pubkey FROM pubkeys WHERE client_id = ? AND tree_id = ?', client_id, tree_id)

