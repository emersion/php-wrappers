php-wrappers
============

High-performance wrappers for native FTP functions.

## Usage

```php
<?php
use Wrappers\FtpStream;

FtpStream::register(); // Replace the built-in wrapper

var_dump(file_put_contents('ftp://host/lol.txt', 'Hello world'));
var_dump(file_get_contents('ftp://host/lol.txt'));

var_dump(filesize('ftp://host/lol.txt'));

FtpStream::unregister(); // Restore the built-in wrapper
?>
```

## Why?

The native `ftp://` wrapper is quite buggy and not very efficient: multiple calls to file functions result in multiple connections being opened and closed. In the last example, using PHP's native wrapper 3 different connections are etablished, using this wrapper only one is used (that's much faster).

You can use almost all native PHP functions with this wrapper.

`ftps://` is also supported with `FtpsStream`.