#!/bin/bash

# ----------------------------------------------------------------------
# Create the .env file if it does not exist.
# ----------------------------------------------------------------------

if [[ ! -f "/var/www/http/.env" ]] && [[ -f "/var/www/http/.env.example" ]];
then
cp /var/www/http/.env.example /var/www/http/.env
fi

# ----------------------------------------------------------------------
# File permissions
# ----------------------------------------------------------------------

for DIR in \
	/var/www/html/src/bootstrap/cache \
	/var/www/html/src/storage/app \
	/var/www/html/src/storage/app/public \
	/var/www/html/src/storage/app/examples \
	/var/www/html/src/storage/app/examples/response \
	/var/www/html/src/storage/app/examples/response/.twins \
	/var/www/html/src/storage/framework \
	/var/www/html/src/storage/framework/cache \
	/var/www/html/src/storage/framework/cache/data \
	/var/www/html/src/storage/framework/sessions \
	/var/www/html/src/storage/framework/testing \
	/var/www/html/src/storage/framework/views \
	/var/www/html/src/storage/logs
do
	mkdir --parents $DIR
	chown nginx:nginx $DIR
	chmod u+rw,g+rws $DIR
	setfacl --default --logical --mask -m u:"nginx":rwX $DIR
	setfacl --logical --mask -m u:"nginx":rwX $DIR
done

# ----------------------------------------------------------------------
# Start supervisor
# ----------------------------------------------------------------------

exec /usr/bin/supervisord -n -c /etc/supervisord.conf