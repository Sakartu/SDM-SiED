#!/usr/bin/python -u
from SimpleXMLRPCServer import SimpleXMLRPCServer
from rpc_handler.handler import SiEDRPCHandler
from db import db
import hashlib
import logging
import os
import sys
from util import util


conf = { #config parameters
        'db_location' : '~/.sied/sied.db',
        'db_conn' : None,
        'check_sigs' : False,
        'debug' : True,
        'consultant_key' : './keys/consultant.pub.pem',
        'host' : '192.168.1.100',
        'port' : 8000,
        #'logfile' : '~/SiED.log',
        }

dry_run = False

def main():
    logger = setup_logging()
    db.initialize(conf)
    
    if not dry_run:
        server = SimpleXMLRPCServer((conf['host'], conf['port']), logRequests=False, allow_none=True)
        server.register_introspection_functions()
        server.register_instance(SiEDRPCHandler(conf))
        logger.info('XMLRPCServer setup, starting...')
        server.serve_forever()
    else:
        s = SiEDRPCHandler(conf)
        ctext = util.encrypt(hashlib.sha512('jemoeder').digest(), 'bla')
        s.testcrypto(ctext)

def setup_logging():
    '''
    A method to setup the logging procedures
    '''
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
    return logging.getLogger()

if __name__ == '__main__':
    main()
