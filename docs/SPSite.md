# SharePoint Site
The `SPSite` class is the foundation for all the other classes of the **SPOIL** library.
It handles HTTP requests and manages [Access Tokens](SPAccessToken.md) and [Context Info](SPContextInfo.md).

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

### Guzzle 5 HTTP Adapter

**Dependencies:**
``` bash
composer require "impensavel/spoil"
composer require "php-http/guzzle5-adapter"
composer require "guzzlehttp/psr7"
```

**Example:**
```php
<?php

require 'vendor/autoload.php';

use Http\Adapter\Guzzle5\Client as HttpClient;
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

### Discovery service

**Dependencies:**
``` bash
composer require "impensavel/spoil"
composer require "php-http/discovery"
```
>**TIP:** An `\Http\Client\HttpClient` and `\Http\Message\MessageFactory` of your choosing must also be available!

**Example:**
```php
<?php

require 'vendor/autoload.php';

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPSite;

try {
    $config = [
        'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
    ];

    $client = HttpClientDiscovery::find();
    $message = MessageFactoryDiscovery::find();

    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, $client, $message);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Configuration
Retrieve the `SPSite` configuration array.

**Example:**
```php
$config = $site->getConfig();

var_dump($config);
```

**Output:**
```php
array(4) {
    ["acs"]=>
    string(57) "https://accounts.accesscontrol.windows.net/tokens/OAuth/2"
    ["resource"]=>
    string(101) "00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64"
    ["client_id"]=>
    string(90) "52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64"
    ["secret"]=>
    string(44) "YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE="
}
```

## Hostname
Retrieve the `SPSite` hostname.

**Example:**
```php
echo $site->getHostname();

echo $site->getHostname('/sites/mySite');
```

**Output:**
```php
https://example.sharepoint.com

https://example.sharepoint.com/sites/mySite
```

## Path
Retrieve the `SPSite` path.


**Example:**
```php
echo $site->getPath();

echo $site->getPath('/stuff');
```

**Output:**
```php
/sites/mySite/

/sites/mySite/stuff
```

## URL
Retrieve the `SPSite` URL.

**Example:**
```php
echo $site->getUrl();

echo $site->getUrl('/stuff');
```

**Output:**
```php
https://example.sharepoint.com/sites/mySite

https://example.sharepoint.com/sites/mySite/stuff
```

## Logout URL
Retrieve the `SPSite` logout URL.

**Example:**
```php
echo $site->getLogoutUrl();
```

**Output:**
```php
https://example.sharepoint.com/sites/mySite/_layouts/SignOut.aspx
```

## HTTP request
Make an HTTP request to the SharePoint API. Use this method when extending the library with new classes/methods or for debugging purposes.

**HTTP GET example:**
```php
// Get the most popular tags
$json = $site->request('_api/sp.userprofiles.peoplemanager.gettrendingtags', [
    'headers' => [
        'Authorization' => 'Bearer '.$site->getSPAccessToken(),
        'Accept'        => 'application/json',
    ],
]);
```

**HTTP POST example:**
```php
// Follow a user
$json = $site->request('_api/sp.userprofiles.peoplemanager/follow(@v)', [
    'headers' => [
        'Authorization'   => 'Bearer '.$site->getSPAccessToken(),
        'Accept'          => 'application/json',
        'X-RequestDigest' => $site->getSPContextInfo()->getFormDigest(),
    ],
    'query' => [
        '@v' => 'i:0#.f|membership|user@example.onmicrosoft.com',
    ],
], 'POST');
```

The `$json` variable will be an `array` with the OData response payload.
If the API returns an error, either a `SPRuntimeException` or a `SPObjectNotFoundException` will be thrown.

### Debugging
To **debug** a response, the 4th argument should be set to `false`.

**HTTP GET debug example:**
```php
// Get the most popular tags
$response = $site->request('_api/sp.userprofiles.peoplemanager.gettrendingtags', [
    'headers' => [
        'Authorization' => 'Bearer '.$site->getSPAccessToken(),
        'Accept'        => 'application/json',
    ],
], 'GET', false);
```

**HTTP POST debug example:**
```php
// Follow a user
$response = $site->request('_api/sp.userprofiles.peoplemanager/follow(@v)', [
    'headers' => [
        'Authorization'   => 'Bearer '.$site->getSPAccessToken(),
        'Accept'          => 'application/json',
        'X-RequestDigest' => $site->getSPContextInfo()->getFormDigest(),
    ],
    'query' => [
        '@v' => 'i:0#.f|membership|user@example.onmicrosoft.com',
    ],
], 'POST', false);
```

>**TIP:** When debugging, an `\Psr\Http\Message\ResponseInterface` object will be returned, regardless of any API errors that may occur.

- When omitted, the 3rd argument defaults to `GET`.
- When omitted, the 4rd argument defaults to `true`.

For more information on the API endpoints used in the examples above, see the [User profiles REST API reference](https://msdn.microsoft.com/EN-US/library/office/dn790354%28v=office.15%29.aspx).

## Access Token
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
The `getSPAccessToken()` method returns the `SPAccessToken` in use by the `SPSite`. If it hasn't been set yet or if it has expired, an `SPRuntimeException` will be thrown.

```php
$token = $site->getSPAccessToken();
```

## Context Info
Like with the **Access Tokens**, there's also three methods to manage `SPContextInfo` objects from a **SPSite** class.

### Create
Like it's `createSPAccessToken()` couterpart, the `createSPContextInfo()` method is just a shorthand that creates a `SPContextInfo` and sets it internally to the `SPSite`.
Refer to the [SharePoint Context Info](SPContextInfo.md) documentation for usage examples.

### Set
The `setSPContextInfo()` method will assign a `SPContextInfo` to the `SPSite`. An `SPRuntimeException` will be thrown if the `formDigest` attribute has expired.

```php
$site->setSPContextInfo($contextInfo);
```

### Get
The `getSPContextInfo()` method returns the `SPContextInfo` in use by the `SPSite`. An `SPRuntimeException` will be thrown if `SPContextInfo` hasn't been set or if the `formDigest` attribute has expired.

```php
$contextInfo = $site->getSPContextInfo();
```
