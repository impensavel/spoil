# SPOIL
SPOIL (**S**hare**P**oint **O**nline **I**nquiry **L**ibrary) is a [PHP](http://www.php.net) library for **SharePoint Online (2013)** and **SharePoint for Office 365**.

Currently supported are SharePoint **Lists**, **Folders**, **Items**, **Files**, **RecycleBinItems** and **Users**.

The library aims to comply with the [PSR-2][] and [PSR-4][] standards.

[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

## Requirements
* [PHP](http://www.php.net) 5.5+
* [HTTP Message related tools](https://packagist.org/packages/php-http/message)
* [PHP-JWT](https://packagist.org/packages/firebase/php-jwt)
* [Carbon](https://packagist.org/packages/nesbot/carbon)
* A package that provides [php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation)

## Installation
``` bash
composer require "impensavel/spoil"
composer require "php-http/guzzle6-adapter"
```
>**TIP:** This library isn't coupled to a specific HTTP client! Read the **SPSite** [documentation](docs/SPSite.md) for more information.

## Basic usage example
```php
<?php

require 'vendor/autoload.php';

use Http\Adapter\Guzzle6\Client as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory as MessageFactory;

use Impensavel\Spoil\Exception\SPRuntimeException;
use Impensavel\Spoil\SPList;
use Impensavel\Spoil\SPSite;

try {
    // SharePoint Site configuration
    $config = [
        'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
    ];

    // Create a SharePoint Site instance
    $client = new HttpClient;
    $message = new MessageFactory;

    $site = new SPSite('https://example.sharepoint.com/sites/mySite/', $config, $client, $message);

    // Generate an Access Token (App-only Policy)
    $site->createSPAccessToken();

    // Get all the Lists and respective Items 
    $lists = SPList::getAll($site, [
        'fetch' => true,
    ]);

    // Iterate through each List
    foreach ($lists as $list) {
        var_dump($list->toArray());

        // Iterate through each List Item
        foreach ($list as $item) {
            var_dump($item->toArray());
        }
    }

} catch (SPRuntimeException $e) {
    // Handle exceptions
}
```

## Class documentation
- [SPSite](docs/SPSite.md)
- [SPAccessToken](docs/SPAccessToken.md)
- [SPFormDigest](docs/SPFormDigest.md)
- [SPList](docs/SPList.md)
- [SPItem](docs/SPItem.md)
- [SPFolder](docs/SPFolder.md)
- [SPFile](docs/SPFile.md)
- [SPRecycleBinItem](docs/SPRecycleBinItem.md)
- [SPRecycleBinItemCollection](docs/SPRecycleBinItemCollection.md)
- [SPUser](docs/SPUser.md)

## SharePoint credentials
[Instructions](docs/Credentials.md) on how to generate SharePoint credentials.

## Troubleshooting
Common issues and how to [solve them](docs/Troubleshooting.md).

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
