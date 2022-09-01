FROM nginx:1.17.10
ENV MOODLE_RELEASE MOODLE_39_STABLE
ENV MOODLE_TAG 'v4.0.1'

ENV SENTRY_DSN 'http://moodle.tesla-ce'
ENV SENTRY_SERVER_NAME 'http://moodle.tesla-ce'
ENV SENTRY_ENABLED 0

RUN apt-get update && apt-get install -y curl zlib1g-dev zip libpng-dev libzip-dev libpq-dev libcurl4-gnutls-dev libxml2-dev libonig-dev git php-fpm php-zip php-pdo php-pdo-mysql php-gd php-json php-curl php-zip php-mbstring php-xml php-xmlrpc php-soap php-intl ssh-client cron
RUN mkdir -p /var/www/html
RUN mkdir -p /var/moodledata

# configure nginx & php
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker/extra_config_php.ini /usr/local/etc/php/conf.d/extra_config_php.ini
#Clone Moodle
RUN rm -rf /var/www/html
RUN mkdir -p /var/www/html
WORKDIR /var/www/html
#RUN git clone --depth=1 tags/$MOODLE_TAG -b $MOODLE_RELEASE git://git.moodle.org/moodle.git .
COPY MOODLE_VERSION /MOODLE_VERSION
RUN MOODLE_TAG=$(cat /MOODLE_VERSION) && export MOODLE_TAG
RUN git clone -b $MOODLE_TAG --single-branch git://git.moodle.org/moodle.git .

COPY teslace_plugin /var/www/html/local/teslace
# install dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN cd /var/www/html/local/teslace && composer install

HEALTHCHECK --interval=30s --timeout=30s --start-period=600s --retries=3 CMD curl http://localhost:80/ || exit 1
RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data /var/moodledata

COPY docker/install_moodle.sh /entrypoint.sh
COPY docker/wait_for_it.sh /wait_for_it.sh
RUN chmod a+x /wait_for_it.sh

# configure cron
COPY docker/moodle-cron /etc/cron.d/moodle-cron
RUN chmod 0644 /etc/cron.d/moodle-cron
RUN crontab /etc/cron.d/moodle-cron

ENTRYPOINT /bin/bash /entrypoint.sh
