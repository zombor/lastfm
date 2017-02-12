Last.fm API class
===

A Kohana3 module to interface with the last.fm API.

Usage
===

static instance()
---
Obtains a singleton of the LastFM class

has_valid_session()
---
Determines if the class has a valid session id from last.fm. If this returns FALSE, you should use the authorize() method to get a valid token.

session()
---
Obtains the session object for the class. Will contain NULL or an object containing the following properties:

1. *name* - the last.fm username who is authenticated
2. *key* - the api key. Used internally for write requests to the API

static authorize($redirect_url = NULL)
---
Sends the request to last.fm to authorize the session. Takes an optional parameter to send the request back to. Last.fm will default to your API location defined in your application.

fetch_service_session($token)
---
Obtains an authorization session id. You should store this in a secure location (that means encrypted).

api($method, array $params = array())
---
Sends an api request to last.fm. Returns a json_decode()'d object of the result. Can throw an exception if an error occurs. This can happen if you don't supply required parameters for a method.

Config
===
Currently, the configuration items are stored in the class itself. You should extend the class and set the $key and $secret properties. This may (that means probably) change later.

Test the API on [RapidAPI](https://rapidapi.com/package/LastFM/functions?utm_source=LastFMGitHub&utm_medium=button).
