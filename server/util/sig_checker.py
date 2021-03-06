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

                    if len(args) > 3:
                        client_id = args[2]
                        tree_id = args[3]

                    if conf['debug'] and len(args) > 3:
                        print sig
                        print client_id
                        print tree_id

                    #report if wrong
                    logger.debug('Checking signature for {0}'.format(fun.__name__))

                    if self.allowed:
                        key = "".join(open(conf[self.allowed], 'r').readlines())
                    elif len(args) > 3:
                        key = db.fetch_pubkey(conf, client_id, tree_id)
                    if not key:
                        logger.warn('Couldn\'t find key for {id}'.format(id=client_id))
                        return None

                    logger.debug('Keys found, continuing...')
                    signargs = fun.__name__
                    #signargs.append(args[2:])
                    if util.check_sign(str(key), b64decode(sig), False, *signargs): 
                        logger.debug('Signature matched, calling function...')
                        return fun(*args)
                    else:
                        logger.warn('Signature didn\'t match for {0}'.format(fun.__name__))
                else:
                    return fun(*args)
            except:
                traceback.print_exc()
        return wrapped_fun
        
