Description
===========

This is the implementation of our Searching in Encrypted Data (SiED) assignment for the Secure Data Management course.

Setup
=====

Our implementation consists of a client and a server part, which we will describe further.

Server
------

The server is basically a wrapper around a database. We will use sqlite3 for portability. The wrapper will provide the following functions on top of the database:

*insert()*

*update()*

*search()*

Client
------
