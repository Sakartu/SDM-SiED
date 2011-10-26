#!/usr/bin/env python
from SimpleXMLRPCServer import SimpleXMLRPCServer
from rpc_handler.handler import SiEDRPCHandler
from db import db
import hashlib
import logging
import os
import sys
import util.util as util


conf = { #config parameters
        'db_location' : '~/.sied/sied.db',
        'db_conn' : None,
        'check_sigs' : False,
        'debug' : False,
        #'logfile' : '~/SiED.log',
        }

dry_run = True
debug = True


def main():
    setup_logging()
    logger = logging.getLogger()
    logger.info('bla2')
    db.initialize(conf)
    
    if not dry_run:
        server = SimpleXMLRPCServer(("wlan235233.mobiel.utwente.nl", 8000))
        server.register_introspection_functions()
        server.register_instance(SiEDRPCHandler())
        server.serve_forever()
    else:
        s = SiEDRPCHandler(conf)
        ctext = util.encrypt(hashlib.sha512('jemoeder').digest(), 'bla')
        s.testcrypto(ctext)

def setup_logging():
    #if we're in debugging mode we use loglevel DEBUG, otherwise ERROR              
    level = None                                                                    
    if conf['debug']:                                                             
        level = logging.DEBUG                                                       
    else:                                                                           
        level = logging.INFO                                                        
    format = '%(asctime)s : %(message)s'                                            
    dateformat = '%d/%m/%Y %H:%M:%S'                                                
    formatter = logging.Formatter(fmt=format, datefmt=dateformat)            
    logging.getLogger().setLevel(level)                                      

    #then we initialize the logging functionality                                   
    if 'logfile' in conf:                                                           
        path = os.path.expanduser(conf['logfile'])                                  

        if not os.path.exists(os.path.dirname(path)):                               
            try:                                                                    
                os.makedirs(os.path.dirname(path))                                  
            except:                                                                 
                print('Could nog create logfile or dirs, exitting')              
                sys.exit(2)                                                      
        handler = logging.FileHandler(path)
        handler.setFormatter(formatter)
        logging.getLogger().addHandler(handler)
    else:
        logging.basicConfig(format=format, datefmt=dateformat, level=level)


if __name__ == '__main__':
    main()
