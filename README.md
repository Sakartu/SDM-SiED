Description
===========

This is the implementation of our Searching in Encrypted Data (SiED) assignment
for the Secure Data Management course. The description of the assignment is as
follows:

> Consider a financial consultant that uses a cloud storage service to store the
> financial data of his clients. The cloud storage server is considered to be
> honest but curious. In order to prevent data leakage, the consultant stores
> all data on the cloud server in encrypted form.

We will use a modified version of the encryption scheme described in the paper
[1]. Our modifications have to do with the requisite that clients may only
access their own data, not the data of other clients. To this end we'll give
each client a unique tree identifier with which it can access only it's own part 
of the database. The consultant has the identifier of all of his clients, so he can 
access and change any data that he wants.

Setup
=====

Our implementation consists of a client and a server part, which we will call C
and S respectively, to avoid confusion. Below follows a description of both
parts.

Server (S)
----------

S is basically a wrapper around a database. We will use sqlite3 for portability.
The wrapper will provide the following functions on top of the database:

__insert(treeID, EncryptedRows[])__

The insert function  is used to insert data into the database. Any existing rows 
with the same treeID are first deleted. The treeID parameter indentifies the tree.  
The XMLData[] provided is a list of rows to insert into the database. 
These rows will look like: 

> \<treeID, pre, post, parent, Cval\>

Following [1] inserting data in the database isn't as easy as it looks. Each
time new data is inserted all rows that have a higher pre value have to be
re-encrypted because all their pre values change and the encryption of the data
in a row is dependant on the pre value. If we would've used one big tree for all
the clients this would've meant that on each insert the data of all clients
would need to be re-encrypted, which isn't possible since a client doesn't have
another client's treeID. This is why each of the shards belonging to one treeID
contain only one tree, meaning that pre, post and parent values are
restarted for each shard.

__update(treeID, int pre, byte[] value)__

Updating a row in the database is rather easy. Each node in a tree is uniquely 
identified by its pre value. So, if a client supplies both treeID and pre then
the node is uniquely identified.

__search(treeID, byte[] XPathEncrypted)__

Searching in the database is where the real magic happens. This method can
evaluate an XPath query in a very fast manner, using the pre, post and parent 
values stored in each row. As a result to the query a set of result trees is 
returned, each of which has a rootnode matching the XPath query.

Client (C)
----------

The client consists of a PHP front-end that calls the server API. There are
several use cases that the client supports:


1) Insert/Overwrite
----------
1) __Insert/overwrite__ a tree corresponding to a treeID on the server. The client 
   will parse the XML file to the required row format, then encrypt it. The server's
   __insert()__ function is called with this input.

Required input:

* The treeID
* The XML file that should be stored. Note that any node can contain either text or child nodes, __not both__.
* Secret key information.

Output: 

* If the operation succeeded: yes or no.


2) Querying
------------
2) __Querying__ using an XPath query. The client will encrypt the XPath query, then send 
   it off to the server. The trees returned by the server are decrypted, and the results 
   are displayed.

Required input: 

* The treeID
* The XPath query
* Secret key information

Output:

* If the query is succesful, the decrypted tree(s) that represent the result of the query
  are displayed.
* If an error occurs (Invalid XPath, Invalid treeID) then this should be displayed.


3) Updating
----------
3) __Updating__ a token in a tree on the server. The combination of treeID and pre value uniquely 
  identifies the node that should have its token changed. The client will encrypt the token before 
  sending it off to the server.

Required input:

* The treeID
* The pre value
* The new token
* Secret key information

Output: 

* If the query was succesful (yes/no). If an error occured, the reason should be displayed.




[1] "Efficient Tree Search in Encrypted Data" by Brinkman et. al.
