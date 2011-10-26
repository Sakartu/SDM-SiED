from util import constants

def initialize(conf):
    pass

def add_pubkey(conf, client_id, tree_id, pubkey):
    with conf[constants.DB_CONN] as conn:
        c = conn.cursor()
        c.execute('INSERT INTO pubkeys VALUES (?, ?, ?)', (client_id, tree_id, pubkey))

