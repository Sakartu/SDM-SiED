#!/usr/bin/python -u
from xmlrpclib import ServerProxy
import util.util as util
from base64 import b64encode
import unittest


class Tests(unittest.TestCase):
    def setUp(self):
        self.server = ServerProxy("http://localhost:8000", allow_none=True)

    def tearDown(self):
        print dir(self.server)

    def test_conn(self):
        a = 'woei'
        b = 'woeiwoei'
        self.assertEqual(str(a) + str(b), self.server.test(a, b))

    def test_pubkey_add(self):
        client_id = 1
        tree_id = util.digest(client_id)
        consultant_privkey = './keys/consultant.pem'
        client_pubkey = "".join(open('./keys/client1.pub.pem').readlines())
        sig = util.sign(consultant_privkey, True, client_id, b64encode(tree_id), client_pubkey)
        #call the server
        expected = "Added key for client {0}".format(client_id)
        result = self.server.add_pubkey(b64encode(sig), client_id, b64encode(tree_id), client_pubkey)
        self.assertEqual(expected, result)

    def test_pubkey_add_twice(self):
        client_id = 1
        tree_id = util.digest(client_id)
        consultant_privkey = './keys/consultant.pem'
        client_pubkey = "".join(open('./keys/client1.pub.pem').readlines())
        sig = util.sign(consultant_privkey, True, client_id, b64encode(tree_id), client_pubkey)
        expected = "Tried to add key for client {0} twice!".format(client_id)
        self.server.add_pubkey(b64encode(sig), client_id, b64encode(tree_id), client_pubkey)
        result = self.server.add_pubkey(b64encode(sig), client_id, b64encode(tree_id), client_pubkey)
        self.assertEqual(expected, result)

if __name__ == '__main__':
    tests = unittest.main()
