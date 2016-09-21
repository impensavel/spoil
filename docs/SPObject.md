# SharePoint Object
The `SPObject` class contains methods related to common attributes of SharePoint objects.

## Subclasses
- [SPAccessToken](docs/SPAccessToken.md)
- [SPContextInfo](docs/SPContextInfo.md)
- [SPList](docs/SPList.md)
- [SPItem](docs/SPItem.md)
- [SPFolder](docs/SPFolder.md)
- [SPFile](docs/SPFile.md)
- [SPUser](docs/SPUser.md)
- [SPRecycleBinItem](docs/SPRecycleBinItem.md)

## Extra
Get an extra property of a `SPAccessToken`, `SPContextInfo`, `SPList`, `SPItem`, `SPFolder`, `SPFile`, `SPUser` or `SPRecycleBinItem` object.

**Example:**
```php
echo $object->getExtra('Foo');
```

**Output:**
```php
Bar
```
