from hashlib import sha512
from util import util
from binascii import hexlify
from base64 import b64encode

key = sha512('je_moeder').digest()
print 'key:', hexlify(key)
c = util.encrypt(key, 'je_moeder')
print 'hex(c):', hexlify(c)
print 'b64(c):', b64encode(c)

s = util.decrypt(key, c)
if s == 'je_moeder':
    print 'win!'
else:
    print ':('

# now without padding:
cnopad = util.encrypt(key, 'a'*16)
print 'hex(cnopad):', hexlify(cnopad)
print 'b64(cnopad):', b64encode(cnopad)
cnopad = util.encrypt(key, 'a'*15)
print 'hex(cnopad):', hexlify(cnopad)
print 'b64(cnopad):', b64encode(cnopad)
