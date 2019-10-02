# Bearer PHP Client

This is the official PHP client for interacting with [Bearer.sh](https://www.bearer.sh).

# Installation

You can install the package manually or by adding it to your `composer.json`:

```json
{
  "require": {
    "bearer/bearer-php": "^1.0.0"
  }
}
```

## Usage

Get your Bearer [Secret Key](https://app.bearer.sh/keys) and integration id from the [Dashboard](https://app.bearer.sh) and use the Bearer client as follows:

### Calling any APIs

```php
$bearer = new Bearer\Client('BEARER_SECRET_KEY'); // find it on https://app.bearer.sh/keys

$api = $bearer->integration('api_name');
$api->get('/api-endpoint');
```

## More advanced examples

**Note**: to run the following examples, you'll need to activate the GitHub API first. To activate it, log in to the [Dashboard](https://app.bearer.sh) then click on "Add an api" and select "GitHub API".

### Passing query parameters

```php
$bearer = new Bearer\Client('BEARER_SECRET_KEY'); // find it on https://app.bearer.sh/keys

$github = $bearer->integration('github');
$github->get('/users/bearer/repos', [ "query" => [ "direction" => "desc" ] ]);
```

### Authenticating users

```php
$bearer = new Bearer\Client('BEARER_SECRET_KEY'); // find it on https://app.bearer.sh/keys

$github = $bearer->integration('github');
$github
    ->auth("AUTH_ID") // Generate a user identity from the Dashboard
    ->put('/user/starred/bearer/bearer', [ "headers" => [ "Content-Length" => 0 ] ]);
```

### Available methods

The following methods are available out-of-the-box: `GET`, `POST`, `PUT`, `DELETE`, `OPTIONS`. If you want to dynamically perform requests, use the `request($method)` function:

```php
$bearer = new Bearer\Client('BEARER_SECRET_KEY'); // find it on https://app.bearer.sh/keys

$github = $bearer->integration('github');
$github
    ->auth("AUTH_ID") // Generate a user identity from the Dashboard
    ->request('PUT', '/user/starred/bearer/bearer', [ "headers" => [ "Content-Length" => 0 ] ]);
```

## Calling custom functions

```php
$bearer = new Bearer\Client('BEARER_SECRET_KEY');

$integration = $bearer->integration('integration_id');
$integration->invoke('functionName');
```

[Learn more](https://docs.bearer.sh/working-with-bearer/manipulating-apis) on how to use custom functions with Bearer.sh.

## Development

Install [composer](https://getcomposer.org/)
```bash
$ composer install
# run tests
$ vendor/bin/phpunit tests
```
