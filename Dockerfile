# BUILD STEP
FROM alpine:latest as builder
RUN apk update && apk add curl php php-cli php-json php-curl php-phar php-mbstring php-openssl nodejs npm
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /opt/gui
COPY ./gui /opt/gui
RUN npm i && npm run build
WORKDIR /opt/app
COPY ./app /opt/app
RUN composer install

###########################################
FROM alpine:latest as environment

RUN apk update && apk add --no-cache redis nginx dnsmasq vsftpd openssl php php-fpm php-json php-pdo_pgsql php-pgsql php-mbstring php-iconv php-pdo php-pcntl php-posix php-session postgresql openrc 

COPY ./.docker/dnsmasq/priority.hosts.dat /etc/dnsmasq.d/priority.hosts.dat
COPY ./.docker/dnsmasq/users.hosts.dat /etc/dnsmasq.d/users.hosts.dat
COPY ./.docker/dnsmasq/dnsmasq.conf /etc/dnsmasq.d/dnsmasq.conf
COPY ./.docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./.docker/php/php-fpm.d/www.conf /etc/php81/php-fpm.d/www.conf
COPY ./.docker/vsftpd/pam_pwdfile.so /lib/security/pam_pwdfile.so
COPY ./.docker/vsftpd/vsftpd.virtual /etc/pam.d/vsftpd.virtual
COPY ./.docker/vsftpd/virtual_users /etc/vsftpd/virtual_users
COPY ./.docker/vsftpd/vsftpd.conf /etc/vsftpd/vsftpd.conf
COPY ./.docker/openrc/init.sh /opt/init.sh
COPY ./.docker/openrc/upcehosting /etc/init.d/upcehosting

COPY --from=builder /opt/gui/dist /opt/gui
COPY --from=builder /opt/app /opt/app
COPY ./process /opt/process

RUN mkdir /run/postgresql && mkdir -p /var/lib/postgresql && \
    chown postgres /run/postgresql && chown postgres /var/lib/postgresql && \
    su postgres -c "initdb --locale en_US.UTF-8 -D /var/lib/postgresql/15/data"
COPY ./.docker/postgres/pg_hba.conf /var/lib/postgresql/15/data/pg_hba.conf
COPY ./.docker/postgres/postgresql.conf /var/lib/postgresql/15/data/postgresql.conf

RUN rc-update add nginx default && \
    rc-update add postgresql default && \
    rc-update add dnsmasq default && \
    rc-update add vsftpd default && \
    rc-update add php-fpm81 default && \
    rc-update add redis default && \
    rc-update add upcehosting default

CMD ["/sbin/init"]