#!/bin/bash

# ----------------------------------------------------------------------
# Create the .env file if it does not exist.
# ----------------------------------------------------------------------

if [[ ! -f "/var/www/http/.env" ]] && [[ -f "/var/www/http/.env.example" ]];
then
cp /var/www/http/.env.example /var/www/http/.env
fi

# ----------------------------------------------------------------------
# Run Composer
# ----------------------------------------------------------------------

if [[ ! -d "/var/www/http/vendor" ]];
then
cd /var/www
composer update
composer dump-autoload -o
fi

# ----------------------------------------------------------------------
# Start supervisord
# ----------------------------------------------------------------------

exec /usr/bin/supervisord -n -c /etc/supervisord.conf