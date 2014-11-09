<?php
/**
 * A FTPS stream wrapper.
 * @see http://php.net/manual/en/class.streamwrapper.php
 * @author emersion <contact@emersion.fr>
 */
class FtpsStream extends FtpStream {
	protected static function conn_new($host, $port) {
		return ftp_ssl_connect($host, $port);
	}
}

if (in_array('ftps', stream_get_wrappers())) {
	stream_wrapper_unregister('ftps');
}
stream_wrapper_register('ftps', 'FtpsStream');