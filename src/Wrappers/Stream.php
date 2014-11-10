<?php

namespace Wrappers;

/**
 * A stream wrapper.
 * @see http://php.net/manual/en/class.streamwrapper.php
 * @author emersion <contact@emersion.fr>
 */
abstract class Stream {
	protected static $protocol = '';
	protected static $registered = false;
	protected static $connections = array();

	public static function is_registered() {
		return static::$registered;
	}

	public static function register() {
		if (in_array(static::$protocol, stream_get_wrappers())) {
			stream_wrapper_unregister(static::$protocol);
		}
		stream_wrapper_register(static::$protocol, get_called_class(), STREAM_IS_URL);
		static::$registered = true;
	}

	public static function unregister() {
		stream_wrapper_restore(static::$protocol);
		static::$registered = false;
	}

	public static function close_all() {
		foreach (static::$connections as $conn) {
			static::conn_close($conn);
		}
	}

	abstract protected static function conn_new($host, $port);
	abstract protected static function conn_close($conn);
}