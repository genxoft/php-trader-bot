# Trader bot (Binance)

- ⚠️**Do not use it for your real Binance account without your own review**: Strategy presented in this application is pretty stupid.

- ⚠️**The developer and contributors are not responsible for the money you lose while using this application or its fragments.**

This application was made with educational purposes not for earn real money.

When I was making this application my goals were:
- Try to interact with Binance API
- Try to analyze trading data and automatically make decision to buy or sell an asset
- Code my own simple Logger (it will need it in the future)

## Requirements
- PHP > 8.1
- PECL Trader lib (https://pecl.php.net/package/trader)
- with PDO_SQLITE

## Installation

1. Clone project
```shell
git clone https://github.com/genxoft/php-trader-bot
cd php-trader-bot
```

2. Composer install
```shell
php composer.phar install
```

3. Copy and fill .env
```shell
cp .env.local .env
vim .env
```

4. Migrate database
```shell
php composer.phar run migrate
```

5. Docker compose up
```shell
docker compose up
```
