#!/bin/bash

#validate inputs
error_message="wp-backup.sh <website_dir> <full|plugins>"
if [[ -z "$2" ]]; then
    echo >&2 $error_message
    exit 1
elif [ $2 != "full" ] && [ $2 != "plugins" ]; then
    echo >&2 $error_message
    exit 1
fi

website=$1
backup_type=$2

#Path to the sites dir below website dir
path=""

webdir="${path}${website}/"
config_file="${webdir}wp-config.php"

#Path to location to store backup files
backup_dir=""

#pull the db username and password from the wordpress config file
if [ -e $config_file ];then
	#find the username in question
	db_string=$(grep DB_NAME $config_file)
	host_string=$(grep DB_HOST $config_file)
	username_string=$(grep DB_USER $config_file)
	password_string=$(grep DB_PASSWORD $config_file)

	database=$(echo $db_string | sed -n -e "s/define('DB\_NAME', '\(.*\)'.*$/\1/p")
	host=$(echo $host_string | sed -n -e "s/define('DB\_HOST', '\(.*\)'.*$/\1/p")
	username=$(echo $username_string | sed -n -e "s/define('DB\_USER', '\(.*\)'.*$/\1/p")
	password=$(echo $password_string | sed -n -e "s/define('DB\_PASSWORD', '\(.*\)'.*$/\1/p")

	if [ $backup_type = "full" ]; then
		#Backup the wordpress php/media files
		echo "Backing up ${webdir}"
		echo "${backup_dir}${website}"
		#tar -czf ${backup_dir}${website}.tar.gz $webdir
	elif [ $backup_type = "plugins" ]; then
		#Backup only the plugins dir to save time
		echo "Doing nothing, for now..."
	fi 

	#Backup the database
	echo "Backing up Database $database"
        echo "${host} ${username} ${password} ${database}"
	#mysqldump --host=${host} --user=${username} --password=${password} $database > ${backup_dir}${website}.sql
else
	echo "Config file not found"
fi
