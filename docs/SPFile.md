# SharePoint File
The `SPFile` class handles all the file operations in SharePoint.

## Get all
Gets all the SharePoint Files from a SharePoint Folder

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    // Get a Folder by relative URL (option #1)
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder');

    // Get a List by title (option #2)
    $folder = SPList::getByTitle($site, 'My List');

    // Get all the Files from the Folder/List we just got
    $files = SPFile::getAll($folder);
    
    // Do something with the files
    foreach ($files as $file) {
        var_dump($file->toArray());
    }

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Get by relative URL
Gets a SharePoint File by its relative URL

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    $file = SPFile::getByRelativeUrl($site, 'myFolder/mySubfolder/image.png');

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Get by name
Gets a SharePoint File by its name

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    // Get a Folder by relative URL (option #1)
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');
    
    // Get a List by title (option #2)
    $folder = SPList::getByTitle($site, 'My List');

    $file = SPFile::getByName($folder, 'image.png');

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Create
Create a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    // Get a Folder by relative URL (option #1)
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    // Get a List by title (option #2)
    $folder = SPList::getByTitle($site, 'My List');

    // Content from an SplFileInfo object
    $content = new SplFileInfo('document.pdf');
    
    // If null, the file name from the SplFileInfo will be used
    $name = null;

    // Content from a resource
    $content = fopen('document.pdf', 'r');
    
    // An SPBadMethodCallException will be thrown if the name is not provided
    $name = 'document.pdf';

    // Content from a string
    $content = 'Document content...';
    
    // An SPRuntimeException will be thrown if the name is not provided
    $name = 'document.pdf';

    // Allow overwriting the file if it already exists
    // An SPBadMethodCallException will be thrown if the file exists and we didn't allow overwriting
    $overwrite = false;

    $file = SPFile::create($folder, $content, $name, $overwrite);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Update
Update a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    // Get a Folder by relative URL (option #1)
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    // Get a List by title (option #2)
    $folder = SPList::getByTitle($site, 'My List');

    $file = SPFile::getByName($folder, 'document.pdf');

    // Content from an SplFileInfo object
    $content = new SplFileInfo('document2.pdf');

    // Content from a resource
    $content = fopen('document2.pdf', 'r');

    // Content from a string
    $content = 'New document content...';

    $file = $file->update($content);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Move/rename
Move and/or rename a SharePoint File.

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    // Get a Folder by relative URL (option #1)
    $folder1 = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    // Get another Folder by relative URL (option #1)
    $folder2 = SPFolder::getByRelativeUrl($site, 'otherFolder');

    // Get a List by title (option #2)
    $folder1 = SPList::getByTitle($site, 'My List');

    // Get another List by title (option #2)
    $folder2 = SPList::getByTitle($site, 'My Other List');

    // Get the File we want to move
    $file = SPFile::getByName($folder1, 'document.pdf');

    // Rename the file (If null, the original name will be used)
    $name = 'moved_document.pdf';

    $file->move($folder2, $name);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Copy
Copy a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    // Get a Folder by relative URL (option #1)
    $folder1 = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    // Get another Folder by relative URL (option #1)
    $folder2 = SPFolder::getByRelativeUrl($site, 'otherFolder');

    // Get a List by title (option #2)
    $folder1 = SPList::getByTitle($site, 'My List');

    // Get another List by title (option #2)
    $folder2 = SPList::getByTitle($site, 'My Other List');

    // Get the File we want to copy
    $file = SPFile::getByName($folder1, 'document.pdf');

    // Rename the file (If null, the original name will be used)
    $name = 'copied_document.pdf';
    
    // Allow overwriting the file if it already exists
    // An SPRuntimeException will be thrown if the file exists and overwriting wasn't allowed
    $overwrite = false;

    $file->copy($folder2, $name, $overwrite);

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Delete
Delete a SharePoint File

```php
<?php

require 'vendor/autoload.php';

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPFile;
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

    // Get a Folder by relative URL (option #1)
    $folder = SPFolder::getByRelativeUrl($site, 'myFolder/mySubfolder');

    // Get a List by title (option #2)
    $folder = SPList::getByTitle($site, 'My List');

    // Get the File we want to delete
    $file = SPFile::getByName($folder, 'document.pdf');

    $file->delete();

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## To array
Retrieve an `array` representation of the `SPFile` object.

**Example:**
```php
var_dump($file->toArray());
```

**Output:**
```php
array(11) {
    ["sp_type"]=>
    string(18) "SP.Data.mySubfolderItem"
    ["id"]=>
    int(123)
    ["guid"]=>
    string(36) "00000000-0000-ffff-0000-000000000000"
    ["title"]=>
    NULL
    ["name"]=>
    string(12) "document.pdf"
    ["size"]=>
    string(5) "65536"
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
    string(31) "/sites/mySite/myFolder/mySubfolder/document.pdf"
    ["author"]=>
    string(55) "i:0i.t|membership|username@example.onmicrosoft.com"
    ["extra"]=>
    array(0) {
    }
}
```

## Get ID
Get the `SPFile` id.

**Example:**
```php
echo $file->getID();
```

**Output:**
```php
123
```

## Get name
Get the `SPFile` name.

**Example:**
```php
echo $file->getName();
```

**Output:**
```php
document.pdf
```

## Get size
Get the `SPFile` size in **kilobytes**.

**Example:**
```php
echo $file->getSize();
```

**Output:**
```php
65536
```

## Get relative URL
Get the `SPFile` relative URL.

**Example:**
```php
echo $file->getRelativeUrl();
```

**Output:**
```php
/sites/mySite/myFolder/mySubfolder/document.pdf
```

## Get URL
Get the `SPFile` URL.

**Example:**
```php
echo $file->getUrl();
```

**Output:**
```php
https://example.sharepoint.com/sites/mySite/myFolder/mySubfolder/document.pdf
```

## Get author
Get the `SPFile` author.

**Output:**
```php
echo $file->getAuthor();
```

**Output:**
```php
i:0i.t|membership|username@example.onmicrosoft.com
```

>**TIP:** The [SPUser](SPUser.md) class can be used to get more info about the author.

## Get contents
Get the contents of the `SPFile`.

```php
file_put_contents('document.pdf', $file->getContents());
```

## Get metadata
This method is similar to the `toArray()` one, with the exception that it includes the `url` and excludes the `sp_type`, `title`, `relative_url`, `author` and `extra` values.

**Example:**
```php
var_dump($file->getMetadata());
```

**Output:**
```php
array(11) {
    ["id"]=>
    int(123)
    ["guid"]=>
    string(36) "00000000-0000-ffff-0000-000000000000"
    ["name"]=>
    string(12) "document.pdf"
    ["size"]=>
    string(5) "65536"
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
    ["url"]=>
    string(78) "https://example.sharepoint.com/sites/mySite/myFolder/mySubfolder/document.pdf"
}
```

## Get SharePoint Item
Get the associated SharePoint Item of a `SPFile`. This method is normally used when the metadata of a `SPFile` needs to be set.

```php
try {
    $item = $file->getSPItem();
    
    $item->update([
        'Title'        => 'A PDF Document',
        
        // Custom fields
        'CustomField1' => 'Foo',
    ]);
} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Properties
`SPFile` property methods belong to a trait and are documented [here](SPProperties.md).

## Timestamps
`SPFile` timestamp methods belong to a trait and are documented [here](SPTimestamps.md).
