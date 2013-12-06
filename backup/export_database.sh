d=`date +%s`;
filename='./backup/database_'$d'.sql';

if command -v xmllint > /dev/null; then
    host="$(echo "cat /config/global/resources/default_setup/connection/host/text()" | xmllint --nocdata --shell app/etc/local.xml | sed '1d;$d')"
    username="$(echo "cat /config/global/resources/default_setup/connection/username/text()" | xmllint --nocdata --shell app/etc/local.xml | sed '1d;$d')"
    password="$(echo "cat /config/global/resources/default_setup/connection/password/text()" | xmllint --nocdata --shell app/etc/local.xml | sed '1d;$d')"
    dbname="$(echo "cat /config/global/resources/default_setup/connection/dbname/text()" | xmllint --nocdata --shell app/etc/local.xml | sed '1d;$d')"
else
    host=`php ./xml_reader.php app/etc/local.xml 'global/resources/default_setup/connection/host'`;
    username=`php ./xml_reader.php app/etc/local.xml 'global/resources/default_setup/connection/username'`;
    password=`php ./xml_reader.php app/etc/local.xml 'global/resources/default_setup/connection/password'`;
    dbname=`php ./xml_reader.php app/etc/local.xml 'global/resources/default_setup/connection/dbname'`;
fi

mysqldump -h $host -u $username $dbname -p$password > $filename;