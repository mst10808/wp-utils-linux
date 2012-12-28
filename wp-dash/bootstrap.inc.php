<?php
require_once("./lib/site.class.php");

function is_even($number) {
    if ($number % 2 == 0) {
        return true;
    } else {
        return false;
    }
}

//read all the configuration files in the sites dir
$conf_dir = './sites';
$sites_list = array();
$environments = array();

if ($handle = opendir($conf_dir)) {
    while (false !== ($environment = readdir($handle))) {
        if ($environment != "." && $environment != "..") {
           $environments[] = $environment;
	}
    }
}
foreach ($environments as $key => $env_name){

    $environment_dir = $conf_dir . "/" . $env_name;
    if ($handle = opendir($environment_dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $ini_data = parse_ini_file($environment_dir . "/" . $entry, true);
            
                //set the datase config to a new site object
                $site = new site();
                $site->db_config = $ini_data['database'];
                $site->name = $entry;
		$site->environment = $env_name;

                //load the data from the database
                $site->get_update_data();

                //add it to the list of sites
                $sites_list[$entry] = $site;
            }
        }
        closedir($handle);
    }
}
?>
