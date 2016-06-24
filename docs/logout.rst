.. _logout:


Logout
======

If you are using browser-based sessions, you'll need a way for the user to
logout and destroy their session cookies.

This library will automatically provide a POST route at ``/logout``.  Simply make a
request of this URL and the session cookies will be destroyed.


Configuration Options
---------------------

If you wish to change the logout URI or the redirect url, you can provide the
following configuration::

    [
      "web" =>  [
        "logout" =>  [
          "enabled" =>  true,
          "uri" =>  "/logout",
          "nextUri" =>  "/goodbye"
        ]
      ]
    ]
