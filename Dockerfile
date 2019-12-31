
FROM alpine:latest
RUN apk upgrade && apk update
RUN apk add php php-xml php-curl php-ctype php-tokenizer php-pdo php-dom php-session
RUN apk add composer git
WORKDIR /home
RUN git clone https://github.com/Mediashare/Toaster toaster
WORKDIR /home/toaster
RUN composer install
EXPOSE 80 443