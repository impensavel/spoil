# SharePoint Properties
The `SPPropertiesTrait` contains methods related to common properties of SharePoint objects.

## Classes using this trait
- [SPList](docs/SPList.md)
- [SPItem](docs/SPItem.md)
- [SPFolder](docs/SPFolder.md)
- [SPFile](docs/SPFile.md)
- [SPRecycleBinItem](docs/SPRecycleBinItem.md)

## GUID
Get the GUID of a `SPList`, `SPItem`, `SPFolder`, `SPFile` or `SPRecycleBinItem` object.

**Example:**
```php
echo $object->getGUID();
```

**Output:**
```php
00000000-0000-ffff-0000-000000000000
```

## Title
Get the title of a `SPList`, `SPItem`, `SPFolder`, `SPFile` or `SPRecycleBinItem` object.

**Example:**
```php
echo $object->getTitle();
```

**Output:**
```php
Some Title
```

## SPType
Get the SharePoint type of a `SPList`, `SPItem`, `SPFolder`, `SPFile` or `SPRecycleBinItem` object.

**Example:**
```php
echo $object->getSPType();
```

**Output:**
```php
SP.Folder
```
