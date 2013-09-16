Post via email support for Idno
===============================

This plugin provides the ability to post messages to your idno powered site 
by sending an email to a special address.

Requirements
------------
* mailparse (pecl install mailparse)

Installation
------------

* Install into your IdnoPlugins directory and activate it in the plugins setting panel.
* Each user, on their settings page with then be able to generate a secret email address 
  that they can email messages to.
* In order for email to be processed you need to specify a catch-all email address rule on your server that redirects incoming email to /my/idno/site/IdnoPlugins/IdnoEmailPosting/script/incoming_email.php

  For example, create an entry in /etc/aliases as follows:
  
```
	idno: "/my/idno/site/IdnoPlugins/IdnoEmailPosting/script/incoming_email.php"
```
  And then bounce any email you get to this alias.
  
Todo/Bugs
---------

* [ ] When posting images, quite often the mail parsing functions won't find the text part of the message. Not sure why...

Licence
-------

Released under the Apache 2.0 licence: http://www.apache.org/licenses/LICENSE-2.0.html

See
---
 * Author: Marcus Povey <http://www.marcus-povey.co.uk> 