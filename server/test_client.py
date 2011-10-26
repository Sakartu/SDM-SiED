#!/usr/bin/python -u
from xmlrpclib import ServerProxy, Error
import util.util as util
from base64 import b64encode, b64decode

server = ServerProxy("http://localhost:8000", allow_none=True)

print "Connectivity test:"
a = 'woei'
b = 'woeiwoei'
print "this should produce {0}{1}".format(a, b)
print server.test(a, b)
print ""

print "add_pubkey test:"
# build params
client_id = 1
tree_id = util.digest(client_id)
consultant_privkey = './keys/consultant.pem'
client_pubkey = "".join(open('./keys/client1.pub.pem').readlines())
sig = util.sign(consultant_privkey, True, client_id, tree_id, client_pubkey)
#call the server
print server.add_pubkey(b64encode(sig), client_id, b64encode(tree_id), client_pubkey)
