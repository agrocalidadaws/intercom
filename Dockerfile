FROM rockylinux:9

# Base & PHP 8.2
RUN dnf -y update \
    && dnf -y install dnf-plugins-core unzip which ca-certificates tar gzip \
    && dnf -y install epel-release \
    && dnf -y install https://rpms.remirepo.net/enterprise/remi-release-9.rpm \
    && dnf -y module reset php \
    && dnf -y module enable php:remi-8.2 \
    && dnf -y install \
    php php-fpm php-cli php-common php-opcache \
    php-pdo php-pgsql php-xml php-mbstring php-gd php-zip php-bcmath php-intl \
    php-redis \
    && dnf clean all

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# User www
RUN groupadd -r www && useradd -r -g www -s /sbin/nologin www

# PHP-FPM in foreground, listen on 0.0.0.0:9000
RUN sed -i 's@^;*daemonize *=.*@daemonize = no@' /etc/php-fpm.conf \
    && sed -i 's@^;*pid *=.*@pid = /run/php-fpm/php-fpm.pid@' /etc/php-fpm.conf \
    && sed -i 's@^listen *=.*@listen = 0.0.0.0:9000@' /etc/php-fpm.d/www.conf \
    && sed -i 's@^user *=.*@user = www@' /etc/php-fpm.d/www.conf \
    && sed -i 's@^group *=.*@group = www@' /etc/php-fpm.d/www.conf \
    && sed -i 's/^listen\.allowed_clients.*/;listen.allowed_clients =/' /etc/php-fpm.d/www.conf \
    && install -d -o www -g www /run/php-fpm

# App dir
WORKDIR /var/www/html

# Copy app files
COPY . /var/www/html
RUN composer install --no-interaction --prefer-dist --no-dev && php artisan optimize


USER www
# Nota: FPM requiere escribir en /run/php-fpm; cambiamos user abajo otra vez a root solo para lanzar
USER root
# EXPOSE 9000
CMD ["php-fpm", "-F"]



