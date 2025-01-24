# Proxy ![CI](https://github.com/noeldemartin/proxy/actions/workflows/ci.yml/badge.svg)

This application is a proxy that can be used to read content from the web without running into CORS issues. You can use it by calling sending POST requests to the `/fetch` endpoint.

For example, if the application is being served at `https://proxy.example.com` you can do:

```js
const request = { url: 'https://duckduckgo.com' };
const response = await fetch('https://proxy.example.com/fetch', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(request),
});
const html = await response.text();

console.log(`Website HTML: ${html}`);
```

Eventually, I may implement a proper [HTTP proxy](https://developer.mozilla.org/en-US/docs/Web/HTTP/Proxy_servers_and_tunneling#http_tunneling), but for now this is enough for my use-case.

If you want to use it yourself, feel free to fork it and host it in your own server. I have written some basic instructions to get started, but the app is implemented using [Laravel](https://laravel.com/) so you can use any workflow that you prefer for development. You can also deploy it using [Forge](https://forge.laravel.com/) or [Vapor](https://vapor.laravel.com/).

All these instructions assume that you have installed [Docker](https://www.docker.com) and you're familiar with the basic concepts.

## Development

For development, you can clone the repository and serve it using [Laravel Sail](https://laravel.com/docs/sail). Make sure to also compile assets with `npm` and add the domain to `/etc/hosts`.

```sh
git clone git@github.com:NoelDeMartin/proxy.git proxy
cd proxy
cp .env.example .env
docker run --rm -v "$(pwd):/app" -w /app laravelsail/php81-composer:latest composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
sudo -- sh -c -e "echo '127.0.0.1 proxy.test' >> /etc/hosts"
npm install
npm run dev
```

After running these commands, you should be able to use the app on [http://proxy.test](http://proxy.test).

## Production

This can be deployed using [kanjuro](https://github.com/NoelDeMartin/kanjuro) and [nginx-agora](https://github.com/NoelDeMartin/nginx-agora).

```sh
git clone https://github.com/NoelDeMartin/proxy.git --branch kanjuro --single-branch
cd proxy
kanjuro install

# Make sure to add the origins you want to allow to CORS_ORIGINS in the .env file

kanjuro start
nginx-agora start
```
