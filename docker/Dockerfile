FROM alpine

USER root

RUN apk update && apk add nginx php7 php7-fpm php7-json php7-mysqli php7-xml php7-mbstring php7-phar ca-certificates wget curl
RUN update-ca-certificates

WORKDIR /var/www
RUN wget https://wordpress.org/latest.tar.gz
RUN tar zxvf latest.tar.gz

## Add wp-cli
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN php wp-cli.phar --info
RUN chmod +x wp-cli.phar
RUN mv wp-cli.phar /usr/local/bin/wp
<<<<<<< HEAD

COPY nginx.conf /etc/nginx/nginx.conf
 
RUN chmod 755 -R wordpress
=======
RUN chmod 755 -R wordpress
#RUN wp core update --path=`/var/www/wordpress/` --version=5.3.2 --allow-root
>>>>>>> 366ef4cc9d1bcd0c5298bb55fc2aeae639e1d29b

ADD nginx.conf /etc/nginx/nginx.conf

RUN chown nginx:nginx -R wordpress




#Fix locales
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US:en
ENV LC_ALL en_US.UTF-8



#Fix nginx
RUN chown nginx:nginx -R /var/lib/nginx/
RUN chown root:nginx /var/log/nginx
RUN chmod 775 -R /var/log/nginx
RUN mkdir /run/php
RUN chown nginx:nginx /run/php
<<<<<<< HEAD
RUN chmod 771 /run/php
=======
RUN chmod 771 /run/php
>>>>>>> 366ef4cc9d1bcd0c5298bb55fc2aeae639e1d29b
