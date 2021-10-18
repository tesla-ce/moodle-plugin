#!/bin/bash
set -eo pipefail
shopt -s nullglob

envsubst '${SENTRY_DSN} ${SENTRY_SERVER_NAME} ${SENTRY_ENABLED}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# usage: file_env VAR [DEFAULT]
#    ie: file_env 'XYZ_DB_PASSWORD' 'example'
# (will allow for "$XYZ_DB_PASSWORD_FILE" to fill in the value of
#  "$XYZ_DB_PASSWORD" from a file, especially for Docker's secrets feature)
file_env() {
	local var="$1"
	local fileVar="${var}_FILE"
	local def="${2:-}"
	if [ "${!var:-}" ] && [ "${!fileVar:-}" ]; then
		mysql_error "Both $var and $fileVar are set (but are exclusive)"
	fi
	local val="$def"
	if [ "${!var:-}" ]; then
		val="${!var}"
	elif [ "${!fileVar:-}" ]; then
		val="$(< "${!fileVar}")"
	fi
	export "$var"="$val"
	unset "$fileVar"
}

file_env 'MOODLE_DBPASS'
file_env 'MOODLE_ADMINPASS'
file_env 'ROLE_ID'
file_env 'SECRET_ID'

mkdir -p /var/moodledata
chown -R www-data:www-data /var/moodledata

/etc/init.d/php7.3-fpm stop && /etc/init.d/php7.3-fpm start

/bin/bash /wait_for_it.sh "${MOODLE_DBHOST}":"${MOODLE_DBPORT}" -- echo "Database is up"

/usr/bin/php /var/www/html/admin/cli/install.php --lang=en  --wwwroot="${MOODLE_WWWROOT}" --dataroot=/var/moodledata --dbtype=mariadb --dbhost="${MOODLE_DBHOST}" --dbname="${MOODLE_DBNAME}" --dbuser="${MOODLE_DBUSER}" --dbpass="${MOODLE_DBPASS}" --dbport="${MOODLE_DBPORT}" --prefix="${MOODLE_DBPREFIX}" --fullname="${MOODLE_FULLNAME}" --shortname="${MOODLE_SHORTNAME}" --summary="${MOODLE_SUMMARY}" --adminuser="${MOODLE_ADMINUSER}" --adminpass="${MOODLE_ADMINPASS}" --adminemail="${MOODLE_ADMINEMAIL}" --non-interactive --agree-license --allow-unstable || echo "Database present"

if [ ! -f /var/www/html/config.php ]; then
    echo "/var/www/html/config.php does not exists. Installation was terminated with error."
    exit 1;
fi

chown www-data:www-data /var/www/html/config.php

sed -i "/\\\*wwwroot\\\*/i \$CFG->sslproxy = $MOODLECFG_SSLPROXY;\n" /var/www/html/config.php

if [ ${MOODLE_DEBUG:-0} == 1 ]; then
  sed -i "/\\\*wwwroot\\\*/i \$CFG->debug = E_ALL;\n" /var/www/html/config.php
fi

if [ ${MOODLE_DEBUG:-0} == 0 ]; then
  sed -i "/\\\*wwwroot\\\*/i \$CFG->disableupdateautodeploy = true;\n" /var/www/html/config.php
fi

nginx -g "daemon off;"
