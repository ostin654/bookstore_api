FROM nginx:alpine AS bookstore-nginx

ENV NGINX_PORT=80
RUN mkdir /etc/nginx/templates
COPY templates/default.conf.template /etc/nginx/templates/

EXPOSE 80
