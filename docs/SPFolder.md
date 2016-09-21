# SharePoint Folder
The `SPFolder` class handles all the folder operations in SharePoint.

## Get by GUID
Gets a SharePoint Folder by its GUID

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFolder;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // Generate Access Token
    $site->createSPAccessToken();

    $folder = SPFolder::getByGUID($site, '00000000-0000-ffff-0000-000000000000');

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Get by relative URL
Gets a SharePoint Folder by its relative URL

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFolder;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // Generate Access Token
    $site->createSPAccessToken();

    $folder = SPFolder::getByRelativeUrl($site, 'myFolder');

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Get subfolders
Gets all the Folders within a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFolder;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // Generate Access Token
    $site->createSPAccessToken();

    $folders = SPFolder::getSubFolders($site, 'myFolder');
    
    // Do something with the folders
    foreach ($folders as $folder) {
        var_dump($folder->toArray());
    }

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Create
Create a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFolder;
use Impensavel\Spoil\SPList;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // Generate Access Token and Form Digest
    $site->createSPAccessToken()->createSPContextInfo();

    // Get a Folder (option #1)
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder');

    // Get a List (option #2)
    $folder = SPList::getByTitle($site, 'My List');

    $name = 'mySubfolder';

    $newFolder = SPFolder::create($folder, $name);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

>**TIP:** An `SPFolder` can be created inside an `SPFolder` or an `SPList`.

## Update
Update a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFolder;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // Generate Access Token and Form Digest
    $site->createSPAccessToken()->createSPContextInfo();

    // Get a Folder by relative URL
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    $properties = [
        'Name' => 'Foo',
    ];

    $folder = $folder->update($properties);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Delete
Delete a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFolder;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site settings
    $settings = [
        // ...
    ];

    // Instantiate SharePoint Site
    $site = SPSite::create('https://example.sharepoint.com/sites/mySite/', $settings);

    // Generate Access Token and Form Digest
    $site->createSPAccessToken()->createSPContextInfo();

    // Get a Folder by relative URL
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    $folder->delete();

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## To array
Retrieve an `array` representation of the `SPFolder` object.

**Example:**
```php
var_dump($folder->toArray());
```

**Output:**
```php    
array(11) {
    ["sp_type"]=>
    string(9) "SP.Folder"
    ["guid"]=>
    string(36) "00000000-0000-ffff-0000-000000000000"
    ["title"]=>
    string(8) "myFolder"
    ["name"]=>
    string(8) "myFolder"
    ["created"]=>
    object(Carbon\Carbon)#55 (3) {
    ["date"]=>
        string(26) "2000-01-01 00:00:00.000000"
        ["timezone_type"]=>
        int(3)
        ["timezone"]=>
        string(13) "Europe/London"
    }
    ["modified"]=>
    object(Carbon\Carbon)#59 (3) {
        ["date"]=>
        string(26) "2000-01-01 00:00:00.000000"
        ["timezone_type"]=>
        int(3)
        ["timezone"]=>
        string(13) "Europe/London"
    }
    ["relative_url"]=>
    string(31) "/sites/mySite/myFolder"
    ["items"]=>
    array(0) {
    }
    ["item_count"]=>
    int(1)
    ["extra"]=>
    array(0) {
    }
}
```

## Properties
`SPFolder` property methods belong to a trait and are documented [here](SPProperties.md).

## Timestamps
`SPFolder` timestamp methods belong to a trait and are documented [here](SPTimestamps.md).
