# SharePoint Site
The `SPSite` class is the foundation for all the other classes of the **SPOIL** library.
It handles HTTP requests and manages [Access Tokens](SPAccessToken.md) and [Form Digests](SPFormDigest.md).

## Instantiation
The library uses [**HTTPlug**](http://httplug.io), so it doesn't depend on a specific HTTP client implementation.
Here are some examples of how to create an `SPSite` instance using different HTTP adapters.

### Guzzle 6 HTTP Adapter

**Dependencies:**
``` bash
composer require "impensavel/spoil"
composer require "php-http/guzzle6-adapter"
```

**Example:**
```php
<?php

require 'vendor/autoload.php';

use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPSite;

try {
    $config = [
        'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
    ];

    $client = new HttpClient;
    $message = new MessageFactory;

    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, $client, $message);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

### cURL client for PHP-HTTP + Guzzle PSR-7 message implementation

**Dependencies:**
``` bash
composer require "impensavel/spoil"
composer require "php-http/curl-client"
composer require "guzzlehttp/psr7"
```

**Example:**
```php
<?php

require 'vendor/autoload.php';

use Http\Client\Curl\Client as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;
use Http\Message\StreamFactory\GuzzleStreamFactory as StreamFactory;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPSite;

try {
    $config = [
        'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
    ];

    $client = new HttpClient(new MessageFactory, new StreamFactory);
    $message = new MessageFactory;

    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, $client, $message);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Configuration
Retrieve the `SPSite` configuration array.

```php
    $config = $site->getConfig();

    var_dump($config);

    // array(4) {
    //     ["acs"]=>
    //     string(57) "https://accounts.accesscontrol.windows.net/tokens/OAuth/2"
    //     ["resource"]=>
    //     string(101) "00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64"
    //     ["client_id"]=>
    //     string(90) "52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64"
    //     ["secret"]=>
    //     string(44) "YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE="
    // }
```

## Hostname
Retrieve the `SPSite` hostname.

```php
    echo $site->getHostname(); // https://example.sharepoint.com

    echo $site->getHostname('/sites/mySite'); // https://example.sharepoint.com/sites/mySite
```

## Path
Retrieve the `SPSite` path.

```php
    echo $site->getPath(); // /sites/mySite/

    echo $site->getPath('/stuff'); // /sites/mySite/stuff
```

## URL
Retrieve the `SPSite` URL.

```php
    echo $site->getUrl(); // https://example.sharepoint.com/sites/mySite

    echo $site->getUrl('/stuff'); // https://example.sharepoint.com/sites/mySite/stuff
```

## Logout URL
Retrieve the `SPSite` logout URL.

```php
    echo $site->getLogoutUrl(); // https://example.sharepoint.com/sites/mySite/_layouts/SignOut.aspx
```

## HTTP request
Make an HTTP request to the SharePoint API. Use this method when extending the library with new classes/methods or for debugging purposes.

```php
    // [HTTP GET] get the most popular tags
    $json = $site->request('_api/sp.userprofiles.peoplemanager.gettrendingtags', [
        'headers' => [
            'Authorization' => 'Bearer '.$site->getSPAccessToken(),
            'Accept'        => 'application/json;odata=verbose',
        ],
    ]);

    // [HTTP POST] follow a user
    $json = $site->request('_api/sp.userprofiles.peoplemanager/follow(@v)', [
        'headers' => [
            'Authorization'   => 'Bearer '.$site->getSPAccessToken(),
            'Accept'          => 'application/json;odata=verbose',
            'X-RequestDigest' => (string) $site->getSPFormDigest(),
        ],
        'query' => [
            '@v' => 'i:0#.f|membership|user@example.onmicrosoft.com',
        ],
    ], 'POST');
```
The `$json` variable will be an `array` with the response of a successful request.
If the response contains an error object, either a `SPRuntimeException` or a `SPObjectNotFoundException` will be thrown.

To **debug** a response, the 4th argument should be set to `false`.
```php
    // [HTTP GET] get the most popular tags
    $response = $site->request('_api/sp.userprofiles.peoplemanager.gettrendingtags', [
        'headers' => [
            'Authorization' => 'Bearer '.$site->getSPAccessToken(),
            'Accept'        => 'application/json;odata=verbose',
        ],
    ], 'GET', false);

    // [HTTP POST] follow a user
    $response = $site->request('_api/sp.userprofiles.peoplemanager/follow(@v)', [
        'headers' => [
            'Authorization'   => 'Bearer '.$site->getSPAccessToken(),
            'Accept'          => 'application/json;odata=verbose',
            'X-RequestDigest' => (string) $site->getSPFormDigest(),
        ],
        'query' => [
            '@v' => 'i:0#.f|membership|user@example.onmicrosoft.com',
        ],
    ], 'POST', false);
```

>**TIP:** An `\Psr\Http\Message\ResponseInterface` object will always be returned, even if an error object exists in the response body.

- When omitted, the 3rd argument will default to `GET`.
- When omitted, the 4rd argument will default to `true`.

For more information on the API endpoints used in the examples above, see the [User profiles REST API reference](https://msdn.microsoft.com/EN-US/library/office/dn790354%28v=office.15%29.aspx).

## Access Tokens
There are three methods to manage **Access Tokens** within the **SPSite** class.

### Create
The `createSPAccessToken()` method isn't more than a shorthand that creates a `SPAccessToken` and sets it internally to the `SPSite` object.
Refer to the [SharePoint Access Token](SPAccessToken.md) documentation for usage examples.

### Set
The `setSPAccessToken()` method assigns a `SPAccessToken` to the `SPSite`. An `SPRuntimeException` will be thrown if the token has expired.

```php
$site->setSPAccessToken($token);
```

### Get
The `getSPAccessToken()` method returns the `SPAccessToken` in use by the `SPSite`. If it hasn't been set yet or if it's expired, an `SPRuntimeException` will be thrown.

```php
$token = $site->getSPAccessToken();
```

## Form Digests
Like with the **Access Tokens**, there's also three methods to manage `SPFormDigest` objects within the **SPSite** class.

### Create
Like it's `createSPAccessToken()` couterpart, the `createSPFormDigest()` method is just a shorthand that creates a `SPFormDigest` and sets it internally to the `SPSite`.
Refer to the [SharePoint Form Digest](SPFormDigest.md) documentation for usage examples.

### Set
The `setSPFormDigest()` method will assign a `SPFormDigest` to the `SPSite`. An `SPRuntimeException` will be thrown if the digest has expired.

```php
$site->setSPFormDigest($token);
```

### Get
The `getSPFormDigest()` method returns the `SPFormDigest` in use by the `SPSite`. An `SPRuntimeException` will be thrown if it's expired or non existent.

```php
$digest = $site->getSPFormDigest();
```
