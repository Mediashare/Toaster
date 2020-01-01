
FROM debian:buster-slim
RUN apt upgrade && apt update
RUN apt install -y php7.3 php-xml php-curl php-ctype php-tokenizer php-sqlite3 php-pdo php-dom
RUN apt install -y php-simplexml
RUN apt install -y apache2 sqlite
RUN apt install -y composer git
WORKDIR /home
RUN git clone https://github.com/Mediashare/Toaster Toaster
WORKDIR /home/Toaster
RUN composer install
RUN bin/console doctrine:database:create
RUN bin/console doctrine:schema:update --force
EXPOSE 80 443