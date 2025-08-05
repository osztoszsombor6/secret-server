# Secret Server Api

Secret server project which allows storing and retrieving secret strings on a server 
through the use of API calls.

### /secret endpoint:

Secrets may be added by sending a Http Post request to the /secret endpoint with x-www-form-urlencoded form data.
The required post fields for adding a secret are:
- secret: nonempty string, the secret text we want to store
- expireAfterViews: integer greater than zero, sets how many times the secret should be viewable before expiring.
- expireAfter: integer greater than or equal to zero, gives in minutes how long the secret should be available 
to view after creation. Zero means it never expires based on time.

After the creation of a secret the server returns a representation of the created object, from which
the hash value should be saved, as this will be used uniquely identify and retrieve the secret later.
In case the form inputs do not include all the fields or the wrong format is given in one of the fields,
the server will respond with a 405 error code.

### /secret/\{hash\} endpoint:

Secrets can be retrieved by sending a Http Get request to the /secret/\{hash\} endpoint,
where \{hash\} is the hash value in the representation returned after adding a new secret.
If the secret is found and is nonexpired, a representation of it will be returned, otherwise
the server will respond with a 404 error code.

Currently, the server is able to respond with a json or xml representation by adding
Accept: applicatio/json Header to the request, or
Accept: application/xml for xml. If not specified, defaults to returning json.