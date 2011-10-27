import logging
import util
import traceback
from base64 import b64decode
from db import db

logger = logging.getLogger()

class SigChecker(object):
    def __init__(self, allowed=[]):
        self.allowed = allowed

    def __call__(self, fun):
        def wrapped_fun(*args):
            try:
                conf = args[0].conf #args[0] is self

                if conf['check_sigs']:
                    #check the signature
                    sig = args[1]
                    client_id = args[2]
                    tree_id = args[3]
                    #report if wrong
                    logger.debug('Checking signature for {0}'.format(fun.__name__))

                    if self.allowed:
                        key = "".join(open(conf[self.allowed], 'r').readlines())
                    else:
                        key = db.fetch_key(conf, client_id, tree_id)

                    logger.debug('Keys found, continuing...')
                    if util.check_sign(key, b64decode(sig), False, *args[2:]): 
                        logger.debug('Signature matched, calling function...')
                        return fun(*args)
                    else:
                        logger.warn('Signature didn\'t match for {0}'.format(fun.__name__))
            except:
                traceback.print_exc()
        return wrapped_fun
        
