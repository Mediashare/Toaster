
FROM alpine:latest
RUN apk upgrade && apk update
RUN apk add php php-xml php-curl php-ctype php-tokenizer php-sqlite3 php-session php-pdo php-dom
RUN apk add composer git
WORKDIR /home
RUN git clone https://github.com/Mediashare/Toaster toaster
WORKDIR /home/toaster
RUN composer install
RUN bin/console doctrine:database:create
RUN bin/console doctrine:schema:update --force
RUN composer dump-env prod
EXPOSE 80 443