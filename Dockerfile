FROM alpine:latest

RUN apk update && apk add --no-cache nginx dnsmasq vsftpd openssl php php81-pgsql php81-session postgresql openrc 

COPY ./.docker/dnsmasq/dnsmasq.conf /etc/dnsmasq.d/dnsmasq.conf
COPY ./.docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./.docker/vsftpd/pam_pwdfile.so /lib/security/pam_pwdfile.so
COPY ./.docker/vsftpd/vsftpd.virtual /etc/pam.d/vsftpd.virtual
COPY ./.docker/vsftpd/virtual_users /etc/vsftpd/virtual_users
COPY ./.docker/vsftpd/vsftpd.conf /etc/vsftpd/vsftpd.conf

# RUN mkdir /run/postgresql && mkdir -p /var/lib/postgresql && \
#     chown postgres /run/postgresql && chown postgres /var/lib/postgresql && \
#     su postgres -c "initdb --locale en_US.UTF-8 -D /var/lib/postgresql/15/data"

RUN rc-update add nginx default && \
    # rc-update add postgresql default && \
    rc-update add dnsmasq default && \
    rc-update add vsftpd default

CMD ["/sbin/init"]