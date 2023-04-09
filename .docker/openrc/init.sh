#!/bin/sh
psql -U postgres -c "CREATE DATABASE upcehosting;"
psql -U postgres -c "REVOKE ALL ON DATABASE upcehosting FROM public;"
psql -U postgres -c "REVOKE ALL ON DATABASE template0 FROM public;"
psql -U postgres -c "REVOKE ALL ON DATABASE template1 FROM public;"
psql -U postgres -c "REVOKE ALL ON SCHEMA public FROM public;"
psql -U postgres -c "GRANT ALL ON SCHEMA public TO postgres;"
php /opt/app/worker/DnsUtil.php

cd /opt/app
./vendor/bin/phinx migrate