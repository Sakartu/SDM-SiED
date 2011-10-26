#!/usr/bin/python -u
from xmlrpclib import ServerProxy, Error
import util.util as util

server = ServerProxy("http://localhost:8000")

print "Connectivity test:"
a = 'woei'
b = 'woeiwoei'
print "this should produce {0}{1}".format(a, b)
print server.test(a, b)
print ""

print "add_pubkey test:"
client_id = 1
tree_id = util.digest(client_id)
server.add_pubkey(
