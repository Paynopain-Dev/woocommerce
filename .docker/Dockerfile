FROM wordpress:5.2.1

RUN apt-get update && apt-get install -y vim

ADD "plugins" "/var/www/html/wp-content/plugins"
RUN rm -rf "/var/www/html/wp-content/plugins/.gitignore"