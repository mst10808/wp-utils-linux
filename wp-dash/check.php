<?php

//make sure required values are set
if (!isset($_GET['site'])) {
    exit("INVALID PARAMTERS");
}

$website = $_GET['site'];

//load functions and classes and everything else we need to make this work
require_once("./bootstrap.inc.php");

//make sure the site is actually in the system
if (!array_key_exists($website, $sites_list)) {
    exit("INVALID PARAMTERS");
}

$site = $sites_list[$website];

$updates = $site->update_check();

if($updates){
    echo "YES";
}
else{
    echo "NO";
}

?>
