FROM nginx:1.17.10
ENV MOODLE_RELEASE MOODLE_39_STABLE
ENV MOODLE_TAG 'v3.9.4'

RUN apt-get update && apt-get install -y curl zlib1g-dev zip libpng-dev libzip-dev libpq-dev libcurl4-gnutls-dev libxml2-dev libonig-dev git php-fpm php-zip php-pdo php-pdo-mysql php-gd php-json php-curl php-zip php-mbstring php-xml php-xmlrpc php-soap php-intl ssh-client
RUN mkdir -p /var/www/html
RUN mkdir -p /var/moodledata

# configure nginx & php
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/conf.d/default.conf
COPY docker/extra_config_php.ini /usr/local/etc/php/conf.d/extra_config_php.ini
#Clone Moodle
RUN rm -rf /var/www/html
RUN mkdir -p /var/www/html
WORKDIR /var/www/html
#RUN git clone --depth=1 tags/$MOODLE_TAG -b $MOODLE_RELEASE git://git.moodle.org/moodle.git .
RUN git clone -b $MOODLE_TAG --single-branch git://git.moodle.org/moodle.git .

COPY teslace_plugin /var/www/html/local/teslace

HEALTHCHECK --interval=30s --timeout=30s --start-period=600s --retries=3 CMD curl http://localhost:80/ || exit 1
RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data /var/moodledata

COPY docker/install_moodle.sh /entrypoint.sh
COPY docker/wait_for_it.sh /wait_for_it.sh
RUN chmod a+x /wait_for_it.sh

ENTRYPOINT /bin/bash /entrypoint.sh