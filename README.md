# Teamleader API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/justijndepover/teamleader-api.svg?style=flat-square)](https://packagist.org/packages/justijndepover/teamleader-api)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/justijndepover/teamleader-api.svg?style=flat-square)](https://packagist.org/packages/justijndepover/teamleader-api)

PHP Client for the Teamleader API

## Caution

This application is still in development and could implement breaking changes. Please use at your own risk.

## Installation

You can install the package with composer

```sh
composer require justijndepover/teamleader-api
```

## Installing the package in Laravel

To use the plugin in Laravel applications, please refer to the [Laravel usage page](laravel-usage.md)

## Usage

Connecting to Teamleader:
```php
// note the state param: this can be a random string. It's used as an extra layer of protection. Teamleader will return this value when connecting.
$teamleader = new Teamleader(CLIENT_ID, CLIENT_SECRET, REDIRECT_URI, STATE);
// open the teamleader login
header("Location: {$teamleader->redirectForAuthorizationUrl()}");
exit;
```

After connecting, Teamleader will send a request back to your redirect uri.
```php
$teamleader = new Teamleader(CLIENT_ID, CLIENT_SECRET, REDIRECT_URI, STATE);

if ($_GET['error']) {
    // your application should handle this error
}

if ($_GET['state'] != $teamleader->getState()) {
    // state value does not match, your application should handle this error
}

$teamleader->setAuthorizationCode($_GET['code']);
$teamleader->connect();

// store these values:
$accessToken = $teamleader->getAccessToken();
$refreshToken = $teamleader->getRefreshToken();
$expiresAt = $teamleader->getTokenExpiresAt();
```

Your application is now connected. To start fetching data:
```php
$teamleader = new Teamleader(CLIENT_ID, CLIENT_SECRET, REDIRECT_URI, STATE);
$teamleader->setAccessToken($accessToken);
$teamleader->setRefreshToken($refreshToken);
$teamleader->setTokenExpiresAt($expiresAt);

// fetch data:
$teamleader->crm->get();

// you should always store your tokens at the end of a call
$accessToken = $teamleader->getAccessToken();
$refreshToken = $teamleader->getRefreshToken();
$expiresAt = $teamleader->getTokenExpiresAt();
```

## Available methods

Note that your application should have the correct scopes enabled inside the [integration](https://marketplace.focus.teamleader.eu/be/nl/ontwikkel/integraties)

This application is in an early development stage. Therefore not all resources are available as props yet. (for example: `$teamleader->users->me`)
In the meantime it's possible to fetch every resource available through the `get` and `post` methods:
```php
$teamleader->get('users.me');
$teamleader->get('departments.list');
$teamleader->get('departments.info', ['id' => $id]);
$teamleader->post('contacts.add', [
    // all the data
]);
```

## Rate limiting

After each request, the package stores the rate limit headers. How you handle rate limiting is up to you, but the package does give you some handy functions to manage this:
```php
// returns the maximum rate limit your application can hit in 1 minute
$teamleader->getRateLimitLimit();

// returns the current limit remaining
$teamleader->getRateLimitRemaining();

// returns the datetime (UTC) when your application can make calls again, after hitting the rate limit.
$teamleader->getRateLimitReset();

// if the current rate limit is equal to 1, sleep until the reset datetime has passed
$teamleader->ensureRateLimitingIsNotExceeded();
```

## Security

If you find any security related issues, please open an issue or contact me directly at [justijndepover@gmail.com](justijndepover@gmail.com).

## Contribution

If you wish to make any changes or improvements to the package, feel free to make a pull request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
