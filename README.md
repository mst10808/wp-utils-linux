 Use at your own risk!
--------------------------------------------------------------------------------------
Contained are various utilities designed to help Linux sysadmins work with Wordpress.
I strongly recommend that you fully test these utilties before attempting to use them
on production data.

 migration:
-------------------------------------------------------------------------------------
Originally written to switch the domain name wordpress is tied to. Can be used to
modify paths in absolute file references or "find and replace" any text in the WP
database. Requires MySQL & PHP to run. Also included helper Bash script to make it
easier to use.

I recommend not using this on your actual database. Take a database dump of the
wordpress site and run this on a staging database. Can be run as the a normal wordpress
user for single site installs. For multisite/network installs a database user with
SUPER privilege may be required if not using the exact same user that wordpress is
using.

 backup:
-------------------------------------------------------------------------------------
Bash script used to help backup an entire Wordpress instance. Creates a tar.gz of all
web files which includes database dump.

