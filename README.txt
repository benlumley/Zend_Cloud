======= THIS REPO =======

I wanted an API to program against that would let me write code to store files once and
via configuration changes, allow me to choose to store them on local disk/cloudfiles/s3 etc.

I found simple cloud/Zend_Cloud (http://www.simplecloud.org/), which is nearly there, but no Cloudfiles support yet.

Then I found Compass's Rackspace Cloudfiles PHP API - http://www.compasswebpublisher.com/php/rackspace-cloudfiles-php-api

Compass have created an API for cloudfiles closely mimicking the S3 API from Zend Framework - ideal, a copy paste from the
Zend_Cloud S3 adapter and a bit of search and replace later and we have a Cloudfiles adapter for Zend_Cloud too!

(preliminary testing says it works, but I will be doing more!)

====== ORIGINAL ZEND_CLOUD/SIMPLE CLOUD README =======

Simple Cloud API
----------------

The Simple Cloud API is a common interface to cloud application services.
This project is still under development and may be subject to change until 
its production release. 

VERSION
-------
Simple Cloud API Preview Release 1 (Zend Framework Rev. 22113)

INSTALLATION
------------

The Simple Cloud API requires no special installation steps. Simply download the
API, extract it to the folder you would like to keep it in, and add the library
directory to your PHP include_path.

GETTING STARTED
---------------

To get started, create an adapter by passing your config object to the appropriate
adapter. You'll find examples in the accompanying unit tests for '.ini' config files.
Join us at http://www.simplecloud.org to give feedback and stay up-to-date with the
Simple Cloud API.

SYSTEM REQUIREMENTS
-------------------

PHP 5.2.4 or later.

LICENSE
-------

The files in this archive are released under the Zend Framework license.
You can find a copy of this license in LICENSE.txt.
