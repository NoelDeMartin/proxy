APP_DOMAIN=$(echo $APP_URL | sed -E s/https?:\\/\\///g)

sed s/\\[\\[SERVER_NAME\\]\\]/$APP_DOMAIN/g /etc/nginx/templates/proxy.conf.template > /etc/nginx/conf.d/proxy.conf
nginx -g "daemon off;"
