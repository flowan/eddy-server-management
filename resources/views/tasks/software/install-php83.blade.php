@include('tasks.apt-functions')

echo "Install PHP 8.3"

waitForAptUnlock
apt-add-repository ppa:ondrej/php -y
apt-get update
waitForAptUnlock

# confdef: If a conffile has been modified and the version in the package did change,
# always choose the default action without prompting. If there is no default action
# it will stop to ask the user unless --force-confnew or --force-confold is also
# been given, in which case it will use that to decide the final action.

# confold: If a conffile has been modified and the version in the package did change,
# always keep the old version without prompting, unless the --force-confdef is also
# specified, in which case the default action is preferred.

apt-get install -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" -y --force-yes \
    php8.3-bcmath \
    php8.3-cli \
    php8.3-curl \
    php8.3-dev \
    php8.3-fpm \
    php8.3-gd \
    php8.3-gmp \
    php8.3-igbinary \
    php8.3-imap \
    php8.3-intl \
    php8.3-mbstring \
    php8.3-memcached \
    php8.3-msgpack \
    php8.3-mysql \
    php8.3-pgsql \
    php8.3-readline \
    php8.3-soap \
    php8.3-sqlite3 \
    php8.3-swoole \
    php8.3-tokenizer \
    php8.3-xml \
    php8.3-zip

echo "Install Imagick for PHP 8.3"

waitForAptUnlock
echo "extension=imagick.so" > /etc/php/8.3/mods-available/imagick.ini
yes '' | apt-get install php8.3-imagick

echo "Install Redis for PHP 8.3"

waitForAptUnlock
yes '' | apt-get install php8.3-redis

@include('tasks.software.update-php-config', ['version' => '8.3'])

service php8.3-fpm restart > /dev/null 2>&1

echo "{!! $server->username !!} ALL=NOPASSWD: /usr/sbin/service php8.3-fpm reload" >> /etc/sudoers.d/php-fpm
