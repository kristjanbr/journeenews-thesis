FROM php:8.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite remoteip

COPY src/ /var/www/html/

# These few lines are specific for use in my thesis scenario, for logging showcase, 
# and to fix an unusual bug, that is causing many other users problems too. It seems provider made a mistake somewhere.
# They can (and likely should) be removed for regual use
COPY docker/apache-logging.conf /etc/apache2/conf-enabled/logging.conf
COPY docker/remoteip.conf /etc/apache2/conf-enabled/remoteip.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]