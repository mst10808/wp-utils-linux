#!/bin/bash

# WARNING!
# This script will leave the wordpress database in an unusable state.
# It creates a .bak.sql file to restore the database to a working state.
# Decided not to include it in the script because scripted dropping of databases is just plain scary.

old_domain=""
new_domain=""
db_url=""
db_user=""
db_pass=""
db_name=""


# repair is used when theres an inconsistancy in the database names
# Ex. there are both energy.ehawaii.gov and test-energy.ehawaii.gov in the db
# prevents the creation of test-test-energy.ehawaii.gov
# Changes them all to the old value
if [ "$1" = "-repair" ]; then

    echo "repairing db"
    php ./wp-prep-db.php $new_domain $old_domain $db_url $db_user $db_pass $db_name
    exit;
fi

# Do a dry run
# No changes made to the db
if [ "$1" = "-dryrun" ]; then
    
    php ./wp-prep-db.php $old_domain $new_domain $db_url $db_user $db_pass $db_name dryrun
    exit;

fi

#Backup the database before we do anything else
echo "Backing Up the Database"
mysqldump --host=${db_url} --user=${db_user} --password=${db_pass} --default-character-set=utf8 $db_name > ${db_name}.bak.sql

#Prep the database for migration
echo "Preparing Database for Migration"
php ./wp-prep-db.php $old_domain $new_domain $db_url $db_user $db_pass $db_name

#if the php script fails STOP
if [ $? -eq 0 ]; then

    #Get a dump of the "migrated" database
    echo "Creating Database Dump"
    mysqldump --host=${db_url} --user=${db_user} --password=${db_pass} --default-character-set=utf8 $db_name > ${db_name}.migrated.sql

    echo "Done!"
    echo " "
    echo "-------------------------------------------------------"
    echo "Import ${db_name}.migrated.sql into the new database"
    echo "Use ${db_name}.bak.sql to restore the old database to its original state"

fi
