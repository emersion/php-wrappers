php-wrappers
============

High-performance wrappers for native FTP and SFTP functions.

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

The default PHP `ftp://` wrapper closes your connections immediately after use which has a side-effect of slowing down your scripts if doing multiple requests. This wrapper replacement holds your connections open (sessions) until the scripts terminates so you can transfer files at a much faster rate.

You can use almost all native PHP functions with this wrapper.

`ftps://` and `sftp://` are also supported with `FtpsStream` and `SftpStream` (for SFTP, you'll need to enable the [`ssh2`](http://php.net/manual/en/book.ssh2.php) extension).

## Performance

![](https://github.com/emersion/php-wrappers/raw/master/tests/benchmark-results.png)

_(Lower is better)_

## Methods

```php
<?php
var_dump(FtpStream::is_registered()); // Returns true if the wrapper is already registered
FtpStream::register(); // Register the wrapper
FtpStream::unregister(); // Restore the default wrapper
FtpStream::close_all(); // Closes all connections before the end of the script
```
