# SharePoint Access Token
In order to work with SharePoint **Lists**, **Folders**, **Items**, **Files**, **Users**, **RecycleBinItems** and **RecycleBinItemCollections**, an Access Token is needed.

Access Tokens can have two authorization policies: **App-only Policy** and **User-only Policy**

## Instantiation (App-only Policy)
There are two ways to create a new **App-only Policy** `SPAccessToken` instance.

### via SPSite
```php
<?php

require 'vendor/autoload.php';

use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site configuration
    $config = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, new HttpClient, new MessageFactory);

    $site->createSPAccessToken();

    $token = $site->getSPAccessToken();

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

### via class factory
```php
<?php

require 'vendor/autoload.php';

use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPAccessToken;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site configuration
    $config = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, new HttpClient, new MessageFactory);

    $token = SPAccessToken::createAppOnlyPolicy($site);

    $site->setSPAccessToken($token);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Instantiation (User-only Policy)
Like with the **App-only Policy** `SPAccessToken`, there's also two ways to instantiate a **User-only Policy** `SPAccessToken`.

### via SPSite
```php
<?php

require 'vendor/autoload.php';

use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site configuration
    $config = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, new HttpClient, new MessageFactory);

    $context_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIyNTQyNGR...';

    $site->createSPAccessToken($context_token);

    $token = $site->getSPAccessToken();

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

### via class factory
```php
<?php

require 'vendor/autoload.php';

use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPAccessToken;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site configuration
    $config = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, new HttpClient, new MessageFactory);

    $context_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiIyNTQyNGR...';

    $token = SPAccessToken::createUserOnlyPolicy($site, $context_token);

    $site->setSPAccessToken($token);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

**Note:** On both **User-only Policy** examples above, the context token comes from the `SPAppToken` HTTP POST field when the SharePoint application launches.

## To array
Retrieve an `array` representation of the `SPAccessToken` object.

**Example:**
```php
var_dump($token->toArray());
```

**Output:**
```php
array(3) {
    ["token"]=>
    string(1132) "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ik1uQ19WWmNBVG..."
    ["expires"]=>
    object(Carbon\Carbon)#28 (3) {
        ["date"]=>
        string(26) "2000-01-01 00:00:00.000000"
        ["timezone_type"]=>
        int(3)
        ["timezone"]=>
        string(13) "Europe/London"
    }
    ["extra"]=>
    array(0) {
    }
}
```

## Has expired
Check if the `SPAccessToken` has expired.

```php
if ($token->hasExpired()) {
    // It's time to get a fresh one
} else {
    // We're good
}
```

## Expire date
Get the expiration date of a `SPAccessToken` in the form of a `Carbon` object.

**Example:**
```php
$carbon = $token->expiration();

echo $carbon->diffForHumans();
```

**Output:**
```php
12 hours from now
```

## To String
The `SPAccessToken` class implements the `__toString` magic method, which enables us to get the token value when we treat the object as a `string`. 

**Example:**
```php
echo $token;
```

**Output:**
```php
eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6Ik1uQ19WWmNBVG...
```

## Serialization
The `SPAccessToken` class implements the `Serializable` interface.
This allows saving the token to use at a later time, avoiding new token requests to the SharePoint API each time something needs doing.

```php
// Serialize the token
$serialized = serialize($token);

// Store it in a database

// When needed, get it back

// Unserialize the data
$oldToken = unserialize($serialized);

// Check if it's still valid
if ($oldToken->hasExpired()) {
    // Request a new token from the API
}

// Do something
```
