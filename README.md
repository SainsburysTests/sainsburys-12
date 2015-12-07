Sainsbury’s Software Engineering Test
=====================================

Dependencies
------------

This project has three dependencies:
- Commando for command line tools
- php-simple-html-dom-parser for DOM parsing
- PHPUnit for unit testing

These dependencies can be installed using Composer:
> php composer.phar install

Execution
---------

Run the script on the command line using:
> php sainsburys.php

The command has various options tha can be displayed using the --help option
> php sainsburys.php --help

The options are:

**-d/--descriptiontext**:
     Get the product description from the page text instead of the meta tags.


**-f/--fileget**:
     Use PHP file_get_contents instead of CURL.


**--help**:
     Show the help page for this command.


**-p/--plainjson**:
     Use plain json instead of human readable.


**-t/--texterrors**:
     Display errors as plain text instead of json.

Testing
-------

Unit tests can be found in the /tests directory.
To run them with php unit use the command:

> vendor/bin/phpunit tests