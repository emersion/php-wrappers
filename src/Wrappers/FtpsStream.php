<?php

namespace Wrappers;

/**
 * A FTPS stream wrapper.
 * @see http://php.net/manual/en/class.streamwrapper.php
 * @author emersion <contact@emersion.fr>
 */
class FtpsStream extends FtpStream {
	protected static $protocol = 'ftps';

	protected static function conn_new($host, $port) {
		return ftp_ssl_connect($host, $port);
	}
}