<?php
require_once("./header.php");
?>
<div class="category_heading">DOCUMENTATION</div>
<p>
    <b>ADDING A SITE TO UPDATE CHECKS</b><br />
    Add a new file into the sites folder with a filename matching the site's domain name. File contents should be as follows:
</p>
<pre>
[database]
host =
username =
password =
db_name =
table_prefix =
</pre>
<p>IMPORTANT: make sure "apache" owns these files and they are chmod 600</p>
<p>
    <b>CHECK IF A SITE IS UP</b><br />
    check.php is a blank update check page for use with nagios. It returns "YES" if there are updates and "NO" if there aren't any. Go to check.php?site=<i>website-domain-name</i>
</p>
<?php
require_once("./footer.php");
?>
