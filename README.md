php-wrappers
============

High-performance wrappers for native FTP functions.

## Usage

```php
use FtpStream;

var_dump(file_put_contents('ftp://host/lol.txt', 'Hello world'));
var_dump(file_get_contents('ftp://host/lol.txt'));
```

## Why?

The native `ftp://` wrapper is quite buggy and not very efficient: multiple calls to file functions result in multiple connections being opened and closed.
