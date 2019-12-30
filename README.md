# Toaster
Toaster is a social network about file sharing. It works with a hub system that segments the different shares.

The project aims to become a decentralized service, giving the possibility to create Toaster instances on your server while keeping the possibility to link to other connected instances.

## Installation
```bash
git clone https://github.com/Mediashare/Toaster
cd Toaster
composer install
```
### Database (Sqlite)
```bash
bin/console doctrine:database:create
bin/console doctrine:schema:update --force
```
### php.ini
```ini
post_max_size = 10000M
upload_max_filesize = 10000M
max_file_uploads = 10000
```
### Apache config
```apache
<VirtualHost *:80>
    ServerName toaster.bio
    ServerAlias www.toaster.bio

    DocumentRoot /var/www/Toaster/public
    DirectoryIndex /index.php
    <Directory /var/www/Toaster/public>
        AllowOverride All
        Order Allow,Deny
        Allow from All
        FallbackResource /index.php
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeeScript assets
    <Directory /var/www/Toaster>
        Options FollowSymlinks
    </Directory>

    <Directory /var/www/Toaster/public/bundles>
        FallbackResource disabled
    </Directory>

    ErrorLog /var/log/apache2/project_error.log
    CustomLog /var/log/apache2/project_access.log combined
</VirtualHost>

```