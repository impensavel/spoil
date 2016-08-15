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
```
>**TIP:** This library isn't coupled to a specific HTTP client! Read the **SPSite** [documentation](docs/SPSite.md) for more information.

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
