FROM php:8.2-fpm-alpine

ARG user
ARG uid

RUN apk update && apk add \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    shadow  # Add shadow package to install useradd

RUN docker-php-ext-install pdo pdo_mysql \
    && apk --no-cache add nodejs npm

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

#USER root

#RUN chmod 777 -R /var/www/

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user
WORKDIR /var/www
USER $user

FROM nginx:alpine

# Install Certbot + plugins for Nginx
RUN apk update && apk add --no-cache certbot certbot-nginx

# Optional: setup directories for certs
RUN mkdir -p /etc/letsencrypt /var/www/certbot

# Copy your custom Nginx config
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]
