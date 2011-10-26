import logging
from util import util
from db import db

logger = logging.getLogger()

class SigChecker(object):
    def __call__(self, fun):
        def wrapped_fun(*args):
            conf = args[0].conf

            if conf['check_sigs']:
                #check the signature
                sig = args[1]
                client_id = args[2]
                tree_id = args[3]
                #report if wrong
                logger.debug('Checking signature for {0}'.format(fun.__name__))
                key = db.fetch_key(conf, client_id)
                if utils.check_sign(
                

            fun(*args)
        return wrapped_fun
        
