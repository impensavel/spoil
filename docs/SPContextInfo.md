# SharePoint Context Info
To modify a SharePoint **List**, **Folder**, **Item** or **File**, an `X-RequestDigest` header must be set for every API request.

Among other things, the `SPContextInfo` class has a `formDigest` attribute, exactly for that purpose.

## Instantiation
There are two ways to instantiate a `SPContextInfo` object.

### via SPSite
```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPContextInfo;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    $site->createSPAccessToken();

    $site->createSPContextInfo();

    $contextInfo = $site->getSPContextInfo();

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

### via class factory
```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPContextInfo;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    $site->createSPAccessToken();

    $contextInfo = SPContextInfo::create($site);

    $site->setSPContextInfo($contextInfo);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## To array
Retrieve an `array` representation of the `SPContextInfo` object.

**Example:**
```php
var_dump($contextInfo->toArray());
```

**Output:**
```php
array(5) {
  ["library_version"]=>   
  string(14) "16.0.1234.5678"
  ["schema_versions"]=>   
  array(2) {
    [0]=>
    string(8) "14.0.0.0"
    [1]=>
    string(8) "15.0.0.0"
  }
  ["form_digest"]=>
  string(157) "0x79EAB4CE687BD3DE6B9A87177CC6430759744CDED8C2605..."
  ["form_digest_expiration"]=>
  object(Carbon\Carbon)#26 (3) {
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

## Library version
Get the library version number.

**Example:**
```php
echo $contextInfo->getLibraryVersion();
```

**Output:**
```php
16.0.1234.5678
```

## REST/CSOM schema versions
Get an `array` with the REST/CSOM schema versions.

**Example:**
```php
var_dump($contextInfo->getSchemaVersions());
```

**Output:**
```php
array(2) {
    [0]=>
    string(8) "14.0.0.0"  
    [1]=>
    string(8) "15.0.0.0"  
}
```

## Form Digest
Get the Form Digest attribute value. 

**Example:**
```php
echo $contextInfo->getFormDigest();
```

**Output:**
```php
0x79EAB4CE687BD3DE6B9A87177CC6430759744CDED8C2605...
```

## Has expired
Check if the `formDigest` attribute has expired.

```php
if ($contextInfo->formDigestHasExpired()) {
    // It's time to get a new one
} else {
    // Looking good
}
```

## Expire date
Get the expiration date of the `formDigest` attribute in the form of a `Carbon` object.

**Example:**
```php
$carbon = $contextInfo->formDigestExpirationDate();

echo $carbon->diffForHumans();
```

**Output:**
```php
29 minutes from now
```

## Serialization
The `SPContextInfo` class implements the `Serializable` interface.
This allows saving the Context Info to use at a later time, avoiding extra requests to the SharePoint API each time something needs doing.

```php
    // Serialize the Context Info
    $serialized = serialize($contextInfo);
    
    // Store it in a database
    
    // When needed, get it back

    // Unserialize the data
    $oldContextInfo = unserialize($serialized);
    
    // Check if the `formDigest` attribute is still valid
    if ($oldContextInfo->formDigestHasExpired()) {
        // Time to get a new Context Info
    }

    // Do something
```
