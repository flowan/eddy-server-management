@include('tasks.apt-functions')

echo "Install MySQL 8.0"

waitForAptUnlock
apt-get update

waitForAptUnlock
apt-get install -y mysql-server-core-8.0 mysql-server-8.0

echo "default_password_lifetime = 0" >> /etc/mysql/mysql.conf.d/mysqld.cnf

echo "" >> /etc/mysql/my.cnf
echo "[mysqld]" >> /etc/mysql/my.cnf
echo "default_authentication_plugin=caching_sha2_password" >> /etc/mysql/my.cnf
echo "skip-log-bin" >> /etc/mysql/my.cnf

sed -i "s/^# max_connections/max_connections/" /etc/mysql/mysql.conf.d/mysqld.cnf
sed -i "s/^max_connections.*=.*/max_connections={{ $mysqlMaxConnections() }}/" /etc/mysql/mysql.conf.d/mysqld.cnf

if grep -q "bind-address" /etc/mysql/mysql.conf.d/mysqld.cnf; then
  sed -i '/^bind-address/s/bind-address.*=.*/bind-address = */' /etc/mysql/mysql.conf.d/mysqld.cnf
else
  echo "bind-address = *" >> /etc/mysql/mysql.conf.d/mysqld.cnf
fi

service mysql restart

echo "Set root password"
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH caching_sha2_password BY '{{ $server->database_password }}';"

echo "Delete anonymous users"
mysql --user="root" --password="{{ $server->database_password }}" -e "DELETE FROM mysql.user WHERE User = '';"
mysql --user="root" --password="{{ $server->database_password }}" -e "DELETE FROM mysql.user WHERE User = 'root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"

echo "Delete the test database"
mysql --user="root" --password="{{ $server->database_password }}" -e "DROP DATABASE IF EXISTS test;"
mysql --user="root" --password="{{ $server->database_password }}" -e "DELETE FROM mysql.db WHERE Db = 'test' OR Db = 'test\\_%';"
mysql --user="root" --password="{{ $server->database_password }}" -e "FLUSH PRIVILEGES;"

echo "Create root user for remote access"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER 'root'@'{{ $server->public_ipv4 }}' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER 'root'@'%' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO root@'{{ $server->public_ipv4 }}' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO root@'%' WITH GRANT OPTION;"

echo "Create the default database user"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER '{{ config('eddy.server_defaults.database_name') }}'@'{{ $server->public_ipv4 }}' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER '{{ config('eddy.server_defaults.database_name') }}'@'%' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO '{{ config('eddy.server_defaults.database_name') }}'@'{{ $server->public_ipv4 }}' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO '{{ config('eddy.server_defaults.database_name') }}'@'%' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "FLUSH PRIVILEGES;"

echo "Create the default database"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE DATABASE {{ config('eddy.server_defaults.database_name') }} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

service mysql restart
