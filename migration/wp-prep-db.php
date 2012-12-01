<?php
/*
 * Script is used to replace domain names in the wordpress database.
 * Should be used in coordination with wp-switch-domain.sh
 *
*/

$old_domain_name = $argv[1];
$new_domain_name = $argv[2];
$db_host = $argv[3];
$db_user = $argv[4];
$db_pass = $argv[5];
$db_name = $argv[6];

if(isset($argv[7])){
    $dry_run = $argv[7];
}
else{
    $dry_run = FALSE;
}

//Make sure the old and new names aren't the same
if ($old_domain_name == $new_domain_name){
    echo "\nOld Domain Name same as New one.\n";
    echo "Nothing will happen";
    exit(1);
}

//Find if the old domain name is in the new domain name
//Running the script twice if the old domain name is in the new one will cause problems
if(strpos($new_domain_name,$old_domain_name)){
    $old_in_new = TRUE;
}
else{
    $old_in_new = FALSE;
}

//escape old domain name for use in regex
$old_domain_name_escaped = preg_replace("(\.)", "\\\.", $old_domain_name);
$old_domain_name_escaped = preg_replace("(\-)", "\\\-", $old_domain_name_escaped);

//escape new domain name for use in regex
$new_domain_name_escaped = preg_replace("(\.)", "\\\.", $new_domain_name);
$new_domain_name_escaped = preg_replace("(\-)", "\\\-", $new_domain_name_escaped);

//is_serialized function taken from wordpress
//i'm sure they wont mind since its their fault we are having this problem in the first place
function is_serialized($data) {
    // if it isn't a string, it isn't serialized
    if (!is_string($data))
        return false;
    $data = trim($data);
    if ('N;' == $data)
        return true;
    if (!preg_match('/^([adObis]):/', $data, $badions))
        return false;
    switch ($badions[1]) {
        case 'a' :
        case 'O' :
        case 's' :
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                return true;
            break;
    }
    return false;
}

//connect to database
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

//set character set
$mysqli->set_charset("utf8");

//GET THE TABLES!
$table_list = array();

$table_result = $mysqli->query("SHOW TABLES");
while ($table = $table_result->fetch_array(MYSQLI_NUM)) {
    $table_list[] = $table[0];
}

$table_result->close();

//GET THE COLUMN NAMES FOR ALL TABLES!
$column_list = array();

for ($i = 0; $i < count($table_list); $i++) {

    //get table columns to find the primary key
    $column_result_pk = $mysqli->query("SHOW COLUMNS FROM " . $table_list[$i]);

    //Find the primary key for that table
    while ($column = $column_result_pk->fetch_array(MYSQLI_ASSOC)) {
        if ($column['Key'] == "PRI") {
            $primary_key = $column['Field'];
        }
    }

    $column_result_pk->close();

    //Run it again cause it wont let me clone the result object
    $column_result = $mysqli->query("SHOW COLUMNS FROM " . $table_list[$i]);

    //Find the columns with text values in them and add them to the list
    while ($column = $column_result->fetch_array(MYSQLI_ASSOC)) {
        //remove the (#) part of field
        $column_type = explode("(", $column['Type']);

        //set the text field types
        $text_types = array('varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob', 'longblob');

        //we only care about columns with text in them
        if (in_array($column_type[0], $text_types)) {
            $column_list[] = array('table' => $table_list[$i], 'column' => $column['Field'], 'pk' => $primary_key);
        }
    }

    $column_result->close();
}

//setup a total row counter
$total_changed = 0;

//setup field summary array
$field_summary = array();

//setup queue
$update_queue = array();
$queue_id = 0;

//query the database on ALL those columns
for ($i = 0; $i < count($column_list); $i++) {

    $column_name = $column_list[$i]['column'];
    $primary_key = $column_list[$i]['pk'];

    $search_query = "SELECT `" . $primary_key . "`, `" . $column_name . "` FROM " . $column_list[$i]['table'] . " WHERE " . $column_name . " LIKE '%" . $old_domain_name . "%'";
    $search_result = $mysqli->query($search_query);

    //we only care about entries that have the old domain name in them
    if ($search_result->num_rows != 0) {

        $num_rows = $search_result->num_rows;

        while ($row = $search_result->fetch_array(MYSQLI_ASSOC)) {

            //do a search for old domain name and replace with new one
            $old_value = $row[$column_name];

            //Check if the new domain name already exists in this field and stop script if it is
            if(preg_match("(" . $new_domain_name_escaped . ")", $old_value) && $old_in_new){
                echo "\nNew domain name exists in the database already\n";
                echo "Please correct first by running wp-switch-domain.sh with -repair flag\n";
                exit(1);
            }

            $new_value = preg_replace("(" . $old_domain_name_escaped . ")", $new_domain_name, $old_value);

            //get primary key value for the update statement
            $primary_key_value = $row[$primary_key];

            //if the value was serialized, repair it
            if (is_serialized($old_value)) {
		//UGLY HACK - replaces single quotes with ^ so the regex can work
		//FIX ASAP
		$new_value = str_replace("'","^",$new_value);
                $new_value = preg_replace('!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"', $new_value);
      		$new_value = str_replace("^","'",$new_value);
            }

            //prep strings for sql query use
            $escaped_new_value = addslashes($new_value);

            //queue up the updates and their corresponding log message
            $update_queue[$queue_id]['query'] = "UPDATE " . $column_list[$i]['table'] . " SET " . $column_name . " = '" . $escaped_new_value . "' WHERE " . $primary_key . " = '" . $primary_key_value . "' LIMIT 1";
            $update_queue[$queue_id]['log'] = "Updating " . $column_list[$i]['table'] . " WHERE " . $primary_key . " = " . $primary_key_value . "\n";

            $queue_id++;
        }

        //add field count to array
        $field_summary[] = array('table' => $column_list[$i]['table'], 'column' => $column_name, 'count' => $num_rows);

        //increment the total changed counter
        $total_changed += $num_rows;
    }
}

//If dry run don't run the updates on the db
if($dry_run == 'dryrun'){
    echo "DRY RUN";
}
else{
	//actually run the queries on the db
	for($i=0;$i<count($update_queue);$i++){

	    //run the queries
    	    echo $update_queue[$i]['log'];
    	    $mysqli->query($update_queue[$i]['query']);
	}
}

//print out a report so we know what happened
echo "\n";
echo "DB CHANGE SUMMARY\n";
echo "Changed " . $old_domain_name . " -> " . $new_domain_name . "\n\n";
echo "Total: " . $total_changed . " values\n";
echo "Breakdown:\n";

//dump summary
for ($i = 0; $i < count($field_summary); $i++) {
    echo $field_summary[$i]['table'] . " " . $field_summary[$i]['column'] . " : " . $field_summary[$i]['count'] . " rows\n";
}
echo "\n";
exit(0);
?>
