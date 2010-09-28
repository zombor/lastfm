Last.fm API class
===
Currently does not support much of anything. Work in progress.

Usage
===

static has_valid_session()
---
Determines if the class has a valid session id from last.fm. If this returns FALSE, you should use the authorize() method to get a valid token.

static authorize($redirect_url = NULL)
---
Sends the request to last.fm to authorize the session. Takes an optional parameter to send the request back to. Last.fm will default to your API location defined in your application.

fetch_service_session($token)
---
Obtains an authorization session id. You should store this in a secure location (that means encrypted).

api($method, $session, array $params = array())
---
Sends an api request to last.fm. Returns a json string of the result.

Config
===
Currently, the configuration items are stored in the class itself. You should extend the class and set the $key and $secret properties. This may (that means probably) change later.