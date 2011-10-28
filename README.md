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
access their own data, not the data of other clients. To authorize a command,
the client will sign it using it's private key. The server has a list of public
keys with which it can verify the command and execute it. Each user has it's own
"tree" stored in the database which is identified by a tree_id. The pre, post and
parent values are restarted for each tree, in this way the recalculations needed
at insertion (see [1]) will only affect the tree of a single user.

Setup 
=====

Our implementation consists of a client and a server part, which we will call C
and S respectively, to avoid confusion. Below follows a description of both
parts.

Server (S)
----------

S is basically a wrapper around a database. We will use sqlite3 for portability.
The wrapper will provide the following functions on top of the database. Each of
functions has a sig and client_id parameter. These parameters are used to 
validate the query. The client_id can be used to lookup a public key in the 
database. This public key is then used to validate the signature for the query.
The signature signs the hash of all the following parameters concatenated.
For the add_pubkey and del_pubkey methods the pubkey isn't lookup up using the
client_id; instead the predefined consultant pubkey is used.

__add_pubkey(base64 sig, int client_id, base64 tree_id, base64 pubkey)__

The add pubkey function is used by the consultant to add new client keys. The
sig is created using the private key of the consultant and the query will be
executed only if the sig matches a check against the public key of the
consultant. This public key is built into the system.

__fetch_pubkey(base64 sig, int client_id, base64 tree_id)__

This function allows fetching of pubkeys from the database, based on the
client_id and tree_id

__delete_pubkey(base64 sig, int client_id, base64 tree_id)__

This function can only be called by the consultant and allows for pubkey removal

__insert(base64 sig, int client_id, base64 tree_id, string[] EncryptedRows)__

The insert function is used to insert data into the database. Any existing rows
with the same tree_id are first deleted. The tree_id parameter indentifies the
tree.  The EncryptedRows provided is a list of rows to insert into the database.
These rows will look like: 

> (base64 tree_id, int pre, int post, int parent, base64 Ctag, base64 Cval)

The XML document is encoded as described in [1], and all text must be encapsulated
in a <#TEXT> node. All attribute names are prefixed with '@'. For example:

> \<test foo="bar"\>Some text!\</test\>

is equivalent to

   \<test\>
      \<@foo\>
         bar
      \</@foo\>
      \<#TEXT\>
         Some text!
      \</#TEXT\>
   \</test\>

and will generate the following rows:

   * (tree_id, 1, 0, 0, "@foo", "bar")
   * (tree_id, 2, 1, 0, "#TEXT", "Some text!")
   * (tree_id, 0, 2, -1, "test", <random value>)

Note that parent is -1 for the root node, and that normal nodes (that would normally have
an empty "value" field) are assigned a random value. This ensures the server cannot distinguish
between the node types. The final step is, of course, to encrypt the tag and value fields to form
Ctag and Cval.
   
Finally, the sig is a signature over all the other values of the function
concatenated. This signature is created by the client using his or her private
key and can be validated by the server using a list of public keys.

The __return_value__ of the function is true if the operation was succesful, and 
false otherwise. 

Following [1] inserting data in the database isn't as easy as it looks. Each
time new data is inserted all rows that have a higher pre value have to be
re-encrypted because all their pre values change and the encryption of the data
in a row is dependant on the pre value. If we would've used one big tree for all
the clients this would've meant that on each insert the data of all clients
would need to be re-encrypted, which isn't possible since a client doesn't have
another client's encryption key. This is why each of the shards belonging to one
tree_id contain only one tree, meaning that pre, post and parent values are
restarted for each shard.

__update(base64 sig, int client_id, base64 tree_id, int pre, base64 value)__

Updating a row in the database is rather easy. Each node in a tree is uniquely 
identified by its pre value. So, if a client supplies both tree_id and pre then
the node is uniquely identified. The sig, again, is used to validate this query.

The __return_value__ is true if the operation succeeded, and false otherwise.

__search(base64 sig, int client_id, base64 tree_id, string query, base64[] encrypted_content)__

Searching in the database is where the real magic happens. This method can
evaluate an XPath query in a very fast manner, using the pre, post and parent
values stored in each row. The result to the query is a result tree which
contains a rootnode matching the XPath query. The query itself contains numbers,
each of which denoting a spot in the encrypted_content list. For instance, in
the query '/1/2//3[@4="5"]' we substitute each of the numbers x with the content
on spot encrypted_content[x]. The sig, again, is used to validate this query.

The __return_value__ of this function is an array of strings. Each string 
represents a single row. The string format is the same as the insert() parameter:

> (base64 tree_id, int pre, int post, int parent, base64 Ctag, base64 Cval)


Client (C)
----------

The client consists of a PHP front-end that calls the server API. There are
several use cases that the client supports:


1) Insert/Overwrite
----------
   __Insert/overwrite__ a tree corresponding to a tree_id on the server. The client 
   will parse the XML file to the required row format, then encrypt it. The server's
   __insert()__ function is called with this input.

Required input:

* The tree_id
* The XML file that should be stored. Note that any node can contain either text or child nodes, __not both__.
* Secret key information.

Output: 

* If the operation succeeded: yes or no.


2) Querying
------------
   __Querying__ using an XPath query. The client will encrypt the XPath query, then send 
   it off to the server. The trees returned by the server are decrypted, and the results 
   are displayed.

Required input: 

* The tree_id
* The XPath query
* Secret key information

Output:

* If the query is succesful, the decrypted row(s) that represent the result of the query
  are displayed. Ideally, a tree representation is given.
* If an error occurs (Invalid XPath, Invalid tree_id) then this should be displayed.


3) Updating
----------
  __Updating__ a token in a tree on the server. The combination of tree_id and pre value uniquely 
  identifies the node that should have its token changed. The client will encrypt the token before 
  sending it off to the server.

Required input:

* The tree_id
* The pre value
* The new token
* Secret key information

Output: 

* If the query was succesful (yes/no). If an error occured, the reason should be displayed.

Nice Links
==========

Link | Explanation
---- | -----------
http://bit.ly/sKadHW | Information about storing xml in databases using pre- and post-order traversal
http://bit.ly/uaERUo | The paper we're basing our prototype on


[1] "Efficient Tree Search in Encrypted Data" by Brinkman et. al.
