# Troubleshooting
Here you will find a list of common library problems and possible solutions to overcome them.

## SPRuntimeException
This is the most common exception that the library might throw. 
More specific exceptions will extend from this one.

### Unable to make an HTTP request
This happens whenever the HTTP adapter being used throws an `Http\Client\Exception`.

There might be several reasons for it, so pay attention to the actual message.

#### cURL specific
Most of the time, cURL is being used under the hood, so here's a list of common errors that might happen.

##### Error code 4
This error happens when **libcURL** tries to use a protocol it doesn't have support for. Either because the SSL/TLS library doesn't support it ([GnuTLS](http://www.gnutls.org/manual/gnutls.html#On-SSL-2-and-older-protocols)), or simply because it was disabled at build time by the vendor ([Ubuntu](http://serverfault.com/questions/456334/problems-with-disabled-ssl-version-2-in-ubuntu-server-can-i-also-disable-ssl-ve)).

In the majority of cases, it's because `CURL_SSLVERSION_SSLv2` is being set, or even `CURL_SSLVERSION_DEFAULT` (which may default to the former), but it should always be confirmed from the error message.

Try using `CURL_SSLVERSION_SSLv3` instead.

For more information about the security protocols supported by **libcURL**, refer to the [SSL library comparison list](http://curl.haxx.se/docs/ssl-compared.html)

##### Error code 35
This is the error when the protocol handshake fails. The error message will most likely be the following:

```
Unknown SSL protocol error in connection to accounts.accesscontrol.windows.net:443
```

If `CURL_SSLVERSION_SSLv2` or `CURL_SSLVERSION_SSLv3` are being used, try using `CURL_SSLVERSION_TLSv1_0` instead.


Note that `CURL_SSLVERSION_TLSv1_0` is **only** available since PHP `5.5.19`/`5.6.3` and cURL `7.34`.


##### Error code 56
The error message associated with this error code might be:
```
GnuTLS recv error (-9): A TLS packet with unexpected length was received.
```

This happens when **libcURL** is built against [GnuTLS](http://www.gnutls.org/) and the fact that this library is more strict when dealing with the TLS protocol.

There's an explanation about it in the [GnuTLS mailing list](http://lists.gnu.org/archive/html/gnutls-devel/2011-02/msg00002.html):
> Several sites terminate the TLS connection without following the TLS protocol (i.e. sending closure alerts), but rather terminate the TCP connection directly. This is a relic of SSLv2 and it seems other implementations ignore this error. GnuTLS doesn't and thus prints this error.

Given that this is a mix of SharePoint not following the TLS protocol correctly and GnuTLS being strict about it, there's not much that can be done except for using a different PHP/libcURL version.

##### Other codes
If other cURL errors emerge, check the libcURL [error list](http://curl.haxx.se/libcurl/c/libcurl-errors.html) to help find the root cause.

### The length of the URL for this request exceeds the configured maxUrlLength value.
This issue occurs when folder/file operations like **move** or **copy** use long file or folder names.

Read more about SharePoint URL length limitations [here](https://technet.microsoft.com/en-us/library/ff919564(v=office.14).aspx).

## Access denied. You do not have permission to perform this action or access this resource.
This usually happens when using **App-only Policy** without the **AllowAppOnlyPolicy** attribute being set to `true` in the **AppPermissionRequests** node.

More information about the SharePoint hosted app manifest configuration can be found [here](https://msdn.microsoft.com/en-us/library/office/fp142383.aspx).
