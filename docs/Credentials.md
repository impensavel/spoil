# SharePoint credentials
This document provides instructions on how to generate credentials to use with the library.

## Gathering data
Some values are formed by several pieces of data that are available from the **App registration** page and the **OData Client Webservice**.

### App registration data
The App registration URL is usually `https://<SharePoint website>/_layouts/15/AppRegNew.aspx`, where `<SharePoint website>` can be something like `example.sharepoint.com/sites/mySite`.

From here the `<client id>`, `<client secret>` and `<app domain>` can be retrieved.

### OData Client Webservice data
Perform a `GET` request to `https://<SharePoint host>/_vti_bin/client.svc` with an `Authorization` header containing `Bearer`.

The `<SharePoint host>` can be something like `example.sharepoint.com`.

The HTTP response (`401` Unauthorized) should include a `WWW-Authenticate` header with a value similar to:
```
Bearer realm="09g7c3b0-f0d4-416d-39a7-09671ab91f64",client_id="00000000-0000-ffff-0000-000000000000",trusted_issuers="00000000-0000-0000-cccc-000000000000@*,https://sts.windows.net/*/,00000000-0000-ffff-0000-000000000000@16121981-8125-1ee1-3eee-50304924032c",authorization_uri="https://login.windows.net/common/oauth2/authorize"
```

The `<bearer realm>` and `<header client id>` can be obtained from the `WWW-Authenticate` header.

## Resource
The `resource` format is: `<header client id>/<SharePoint host>@<bearer realm>`

## Client ID
The `client_id` format is: `<client id>/<app domain>@<bearer realm>`

## Secret
The secret can be found in the **Client Secret** input form of the **App registration** page.
Its value should be something similar to: `YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=`

## App-only Policy specifics
The `resource`, `client_id` and `secret` must be part of the `SPSite` settings, when using the following methods:
- `SPSite::createSPAccessToken()`
- `SPAccessToken::createAppOnlyPolicy()`

```php
$settings = [
    'site' => [
        'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
        'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
    ]
];
```

## User-only Policy specifics
Only the `secret` needs to be included in the `SPSite` settings, when using the following methods:
- `SPSite::createSPAccessToken()`
- `SPAccessToken::createUserOnlyPolicy()`

```php
$settings = [
    'site' => [
        'secret' => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
    ]
];
```
