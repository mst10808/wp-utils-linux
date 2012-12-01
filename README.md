Name: wp-utils-linux
Author: Matt Taniguchi

Contained are various utilities designed to help Linux sysadmins work with Wordpress.
I strongly recommend that you fully test these utilties before attempting to use them
on production data.

--------------------------------------------------------------------------------------
 wp-dash:
--------------------------------------------------------------------------------------
A simple dashboard written in php to help monitor multiple Wordpress single sites for 
updates to plugins or core. This application requires database access and that 
Wordpress be using MySQL.

-------------------------------------------------------------------------------------
 migration:
-------------------------------------------------------------------------------------
Originally written to switch the domain name wordpress is tied to. Can be used to
modify paths in absolute file references or "find and replace" any text in the WP
database. Requires MySQL & PHP to run. Also included helper Bash script to make it
easier to use.

-------------------------------------------------------------------------------------
 backup:
-------------------------------------------------------------------------------------
Bash script used to help backup an entire Wordpress instance. Creates a tar.gz of all
web files which includes database dump.

