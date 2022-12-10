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

For production, you can use some the Docker Compose configuration that is included in the repository. It is, however, not straight-forward to use because it has some configuration parameters such as the domain where it will be served from and the allowed origins. It is also integrated with [nginx-agora](https://github.com/noeldemartin/nginx-agora), which I use in my server.

In order to simplify this setup, you can use the `./proxy` binary:

```sh
git clone git@github.com:NoelDeMartin/proxy.git proxy
cd proxy
./proxy install
./proxy start
```

Run `./proxy` to see more commands.

## Production (headless)

The code is published in [Docker Hub](https://hub.docker.com/r/noeldemartin/proxy), so if you just want to run the application in the server without modifying any files you can do it in "headless mode". This only means that you won't have the source code checked out in the server, instead you'll only keep the configuration files and folders necessary to run the app using Docker Compose.

This can also be configured using the `./proxy` binary:

```sh
git clone --branch headless --single-branch git@github.com:NoelDeMartin/proxy.git proxy
cd proxy
./proxy install
./proxy start
```

Run `./proxy` to see more commands.
