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

sed -i "s/^max_connections.*=.*/max_connections={{ $mysqlMaxConnections() }}/" /etc/mysql/my.cnf

if grep -q "bind-address" /etc/mysql/mysql.conf.d/mysqld.cnf; then
  sed -i '/^bind-address/s/bind-address.*=.*/bind-address = */' /etc/mysql/mysql.conf.d/mysqld.cnf
else
  echo "bind-address = *" >> /etc/mysql/mysql.conf.d/mysqld.cnf
fi

# Set the root password
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH caching_sha2_password BY '{{ $server->database_password }}';"
mysql -e "DELETE FROM mysql.user WHERE User = '';"
mysql -e "DELETE FROM mysql.user WHERE User = 'root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db = 'test' OR Db = 'test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER 'root'@'{{ $server->public_ipv4 }}' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER 'root'@'%' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO root@'{{ $server->public_ipv4 }}' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO root@'%' WITH GRANT OPTION;"
service mysql restart

mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER '{{ config('eddy.server_defaults.database_name') }}'@'{{ $server->public_ipv4 }}' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE USER '{{ config('eddy.server_defaults.database_name') }}'@'%' IDENTIFIED BY '{{ $server->database_password }}';"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO '{{ config('eddy.server_defaults.database_name') }}'@'{{ $server->public_ipv4 }}' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "GRANT ALL PRIVILEGES ON *.* TO '{{ config('eddy.server_defaults.database_name') }}'@'%' WITH GRANT OPTION;"
mysql --user="root" --password="{{ $server->database_password }}" -e "FLUSH PRIVILEGES;"

mysql --user="root" --password="{{ $server->database_password }}" -e "CREATE DATABASE {{ config('eddy.server_defaults.database_name') }} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

service mysql restart
