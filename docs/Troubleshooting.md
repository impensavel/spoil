# Troubleshooting
Here you will find a list of common library problems and possible solutions to overcome them.

## SPRuntimeException
This is the most common exception that the library might throw. 
More specific exceptions will extend from this one.

### Unable to make an HTTP request
This happens whenever the HTTP adapter being used throws an `Http\Client\Exception`.

There might be several reasons for it, so pay attention to the actual message.

Most of the time, cURL is being used under the hood, so here is a list of common libcURL [errors](http://curl.haxx.se/libcurl/c/libcurl-errors.html).

### The length of the URL for this request exceeds the configured maxUrlLength value.
This issue occurs when folder/file operations like **move** or **copy** use long file or folder names.

Read more about SharePoint URL length limitations [here](https://technet.microsoft.com/en-us/library/ff919564(v=office.14).aspx).

## Access denied. You do not have permission to perform this action or access this resource.
This usually happens when using **App-only Policy** without the **AllowAppOnlyPolicy** attribute being set to `true` in the **AppPermissionRequests** node.

More information about the SharePoint hosted app manifest configuration can be found [here](https://msdn.microsoft.com/en-us/library/office/fp142383.aspx).
