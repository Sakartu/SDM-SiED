from hashlib import sha512
from util import util
from binascii import hexlify
from base64 import b64encode, b64decode

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

p = b64decode('lefa8X/OjnWoE2M5Q4fLJw==')
k = b64decode('lefa8X/OjnWoE2M5Q4fLJw==')
print "test 1:", b64encode(util.encrypt(k, p))
p = b64decode('uRxjhhONcQk=')
k = b64decode('lefa8X/OjnWoE2M5Q4fLJw==')
print "test 2:", b64encode(util.encrypt(k, p))
p = 'wzup'
k = b64decode('lefa8X/OjnWoE2M5Q4fLJw==')
print "test 3:", b64encode(util.encrypt(k, p))
p = 'a'*16
k = b64decode('lefa8X/OjnWoE2M5Q4fLJw==')
print "test 4:", b64encode(util.encrypt(k, p))
p = 'price'
k = b64decode('lefa8X/OjnWoE2M5Q4fLJw==')
print "test 5:", b64encode(util.encrypt(k, p))
assert 'price' == util.decrypt(k, util.encrypt(k, p))
