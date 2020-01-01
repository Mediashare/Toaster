
FROM alpine:latest
RUN apk upgrade && apk update
RUN apk add php php-xml php-curl php-ctype php-tokenizer php-sqlite3 php-session php-pdo php-dom
RUN apk add --no-cache php-simplexml
RUN apk add apache2 sqlite mysql-server
RUN apk add composer git
WORKDIR /home
RUN git clone https://github.com/Mediashare/Toaster Toaster
WORKDIR /home/Toaster
RUN composer install
RUN bin/console doctrine:database:create
RUN bin/console doctrine:schema:update --force
RUN composer dump-env prod
RUN bin/console server:start
EXPOSE 80 443