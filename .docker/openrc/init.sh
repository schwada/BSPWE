#!/bin/sh
psql -U postgres -c "CREATE DATABASE upcehosting"
php /opt/process/dns/dns-update.php

cd /opt/app
./vendor/bin/phinx migrate