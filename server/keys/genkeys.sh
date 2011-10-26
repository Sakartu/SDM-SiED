#!/bin/sh
# consultant key
openssl genrsa -out consultant.pem 2048 
openssl rsa -in consultant.pem -pubout -out consultant.pub.pem

openssl genrsa -out client1.pem 2048 
openssl rsa -in client1.pem -pubout -out client1.pub.pem

openssl genrsa -out client2.pem 2048 
openssl rsa -in client2.pem -pubout -out client2.pub.pem
