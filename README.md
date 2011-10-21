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
each client a unique token with which it can access only it's own part of the
database. The consultant has the tokens of all of his clients, so he can access
and change any data that he wants.

Setup
=====

Our implementation consists of a client and a server part, which we will call C
and S respectively, to avoid confusion. Below follows a description of both
parts.

Server (S)
----------

S is basically a wrapper around a database. We will use sqlite3 for portability.
The wrapper will provide the following functions on top of the database:

__insert(tokens[], XMLData[])__

The insert function is used to insert data into the database. The tokens[]
parameter is a list of byte[]'s. The XMLData[] provided is a list of rows to
insert into the database. These rows will look like: 

> \<token, pre, post, parent, Cval\>

Following [1] inserting data in the database isn't as easy as it looks. Each
time new data is inserted all rows that have a higher pre value have to be
re-encrypted because all their pre values change and the encryption of the data
in a row is dependant on the pre value. If we would've used one big tree for all
the clients this would've meant that on each insert the data of all clients
would need to be reëncrypted, which isn't possible since a client doesn't have
another client's token. This is why each of the shards belonging to one client
token contain only one tree, meaning that pre, post and parent values are
restarted for each shard.

__update(tokens[], int pre, byte[] value)__

Updating a row in the database is rather easy. Each row is uniquely identified
by its pre value. So, if a client has the correct token, she can update the
value belonging to the row with the defined pre value.

__search(tokens[], byte[] XPathEncrypted)__

Searching in the database is where the real magic happens. This method can
evaluate a limited set of XPath queries in a very fast manner, using the pre,
post and parent values stored in each row. As a result to the query a set of
subtrees is returned, each of which has a rootnode matching the XPath query.

Client (C)
----------

[1] "Efficient Tree Search in Encrypted Data" by Brinkman et. al.
