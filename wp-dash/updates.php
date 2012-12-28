<?php
require_once("./bootstrap.inc.php");
require_once("./header.php");

foreach ($environments as $key => $envname){
?>
<div class="category_heading"><?php echo $envname; ?></div>
<table id="update_table">
    <tr><th>SITE</th><th>CORE UPDATES</th><th>PLUGIN UPDATES</th><th>ACTION REQUIRED</th></tr>
<?php
    $i = 1;

    foreach ($sites_list as $name => $site) {
        if($site->environment == $envname){

            echo "<tr ";
            if (is_even($i)) {
                echo "class=\"even\" ";
            } else {
                echo "class=\"odd\" ";
            }
                echo ">\n";

            $plugin_updates = $site->get_plugin_updates();
            $core_updates = $site->get_core_updates();

            echo "<td><b>" . $site->name . "</b></td>\n<td>";

            if ($core_updates['action'] == 'upgrade') {
                echo "New version " . $core_updates['new'] . " available.<br />\n Current version: " . $core_updates['current'] . ".";
            } elseif($core_updates['action'] == 'latest') {
                echo "Version " . $core_updates['current'] . " is current.";
            }

            echo "</td>\n<td>";

            if ($plugin_updates) {
                foreach ($plugin_updates as $plugin => $version) {
                    echo $plugin . " has an update (" . $version . ")<br />\n";
                }
            } else {
                echo "No plugins updates available.";
            }

            echo "</td>\n";

            if($site->update_check()){
                echo "<td class=\"red\">Updates Required</td>";
            }
            else{
                echo "<td class=\"green\">No Updates Found</td>";
            }

            echo "</td>\n</tr>\n";

            $i++;
        }
    }
    echo "</table>\n";
}
require_once("footer.php");
?>
</div>
