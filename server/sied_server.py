#!/usr/bin/env python
from SimpleXMLRPCServer import SimpleXMLRPCServer
from rpc_handler.handler import SiEDRPCHandler
from db import db
import hashlib
import util.util as util


conf = { #config parameters
        'db_location' : '~/.sied/sied.db',
        'db_conn' : None,
        }

dry_run = True
debug = True


def main():
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


if __name__ == '__main__':
    main()
