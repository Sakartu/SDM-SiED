#!/usr/bin/env python
from SimpleXMLRPCServer import SimpleXMLRPCServer
from req_handler.request_handler import RequestHandler

conf = { #config parameters

        }


def main():
    server = SimpleXMLRPCServer(("wlan235233.mobiel.utwente.nl", 8000))
    server.register_introspection_functions()
    server.register_function(pow)
    server.serve_forever()

if __name__ == '__main__':
    main()
