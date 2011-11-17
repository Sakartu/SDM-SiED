#!/usr/bin/python -u
from xmlrpclib import ServerProxy
import util.util as util
from base64 import b64encode
import unittest


class Tests(unittest.TestCase):
    def setUp(self):
        self.server = ServerProxy("http://localhost:8000", allow_none=True)
        self.consultant_id = 0
        self.consultant_privkey = "".join(open('./keys/consultant.pem').readlines())
        self.consultant_pubkey = "".join(open('./keys/consultant.pub.pem').readlines())
        self.client_privkey = "".join(open('./keys/client1.pem').readlines())
        self.client_pubkey = "".join(open('./keys/client1.pub.pem').readlines())
        self.client_id = 1
        self.tree_id = util.digest(self.client_id)

    def tearDown(self):
        self.server.clear_db()

    def test_conn(self):
        a = 'woei'
        b = 'woeiwoei'
        self.assertEqual(str(a) + str(b), self.server.test(a, b))

    def test_pubkey_add(self):
        #sig = util.sign(self.consultant_privkey, False, "add_pubkey", self.client_id, b64encode(self.tree_id), self.client_pubkey)
        sig = util.sign(self.consultant_privkey, False, "add_pubkey")
        #call the server
        expected = "Added key for client {0}".format(self.client_id)
        result = self.server.add_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id), self.client_pubkey)
        self.assertEqual(expected, result)

    def test_pubkey_add_twice(self):
        #sig = util.sign(self.consultant_privkey, False, "add_pubkey", self.client_id, b64encode(self.tree_id), self.client_pubkey)
        sig = util.sign(self.consultant_privkey, False, "add_pubkey")
        expected = "Tried to add key for client {0} twice!".format(self.client_id)
        self.server.add_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id), self.client_pubkey)
        result = self.server.add_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id), self.client_pubkey)
        self.assertEqual(expected, result)

    def test_pubkey_del(self):
        #first add the key
        #sig = util.sign(self.consultant_privkey, False, "add_pubkey", self.client_id, b64encode(self.tree_id), self.client_pubkey)
        sig = util.sign(self.consultant_privkey, False, "add_pubkey")
        self.server.add_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id), self.client_pubkey)

        #then remove it
        #sig = util.sign(self.consultant_privkey, False, "del_pubkey", self.client_id, b64encode(self.tree_id))
        sig = util.sign(self.consultant_privkey, False, "del_pubkey")
        expected = "Removed key for client {id}".format(id=self.client_id)
        result = self.server.del_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id))
        self.assertEqual(expected, result)

    def test_pubkey_fetch(self):
        #sig = util.sign(self.consultant_privkey, False, "add_pubkey", self.client_id, b64encode(self.tree_id), self.client_pubkey)
        sig = util.sign(self.consultant_privkey, False, "add_pubkey")
        self.server.add_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id), self.client_pubkey)

        #then fetch it
        #sig = util.sign(self.client_privkey, False, "fetch_pubkey", self.client_id, b64encode(self.tree_id))
        sig = util.sign(self.client_privkey, False, "fetch_pubkey")
        expected = self.client_pubkey
        result = self.server.fetch_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id))
        self.assertEqual(expected, result)

    def test_clear_keys(self):
        # first add the key
        sig = util.sign(self.consultant_privkey, False, "add_pubkey")
        self.server.add_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id), self.client_pubkey)
        # then fetch it
        sig = util.sign(self.client_privkey, False, "fetch_pubkey")
        expected = self.client_pubkey
        result = self.server.fetch_pubkey(b64encode(sig), self.client_id, b64encode(self.tree_id))
        self.assertEqual(expected, result)
 
        # then clear the keys
        sig = util.sign(self.consultant_privkey, False, "clear_keys")
        result = self.server.clear_keys(b64encode(sig))
        self.assertTrue(result)

if __name__ == '__main__':
    tests = unittest.main()
