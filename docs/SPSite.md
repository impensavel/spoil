# SharePoint Site
The `SPSite` class is the foundation for all the other classes of the **SharePoint OAuth App Client** library.
It handles HTTP requests and manages [Access Tokens](SPAccessToken.md) and [Form Digests](SPFormDigest.md).

## Instantiation
There are two ways to create an `SPSite` instance.

### via constructor
```php
<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPSite;

try {
    $settings = [
        'site' => [
            'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
        ]
    ];

    $http = new Client([
        'base_url' => 'https://example.sharepoint.com/sites/mySite/'
    ]);

    $site = new SPSite($http, $settings);

} catch (SPRuntimeException $e) {
    // handle exceptions
}
```

### via factory
```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPSite;

try {
    $settings = [
        'site' => [
            'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
        ]
    ];

    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

} catch (SPRuntimeException $e) {
    // handle exceptions
}
```

## HTTP client settings
To pass custom settings to the HTTP client, the `http` key should be used in the settings `array`.

```php
$settings = [
    // SharePoint Site credentials
    'site' => [
        // ...
    ],

    'http' => [
        'defaults' => [
            'verify' => '/path/to/cert.pem', // enable verification using a custom certificate
            'config' => [
                'curl' => [
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_0, // use TLS v1.0
                ],
            ],
        ],
    ],
];
```

For more info, refer to the [Guzzle HTTP client documentation](http://docs.guzzlephp.org/en/latest/clients.html#creating-a-client)

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
A **GuzzleHttp\Message\Response** object will always be returned, even if an error object exists in the response body.

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
