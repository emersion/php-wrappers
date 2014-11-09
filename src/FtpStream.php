<?php
/**
 * A FTP stream wrapper.
 * @see http://php.net/manual/en/class.streamwrapper.php
 * @author emersion <contact@emersion.fr>
 */
class FtpStream {
	protected static $connections = array();

	public $context;

	protected $conn;

	protected $url;

	protected $stream_mode;
	protected $stream_pos;
	protected $stream_handle;

	protected $dir_list;
	protected $dir_pos;

	protected static function conn_get($url) {
		$urlData = parse_url($url);

		$host = $urlData['host'];
		if (isset($urlData['port'])) {
			$host .= ':'.$urlData['port'];
		}

		$connId = $host;
		if (isset($urlData['user'])) {
			if (isset($urlData['pass'])) {
				$connId = $urlData['user'].':'.$urlData['pass'].'@'.$connId;
			} else {
				$connId = $urlData['user'].'@'.$connId;
			}
		}

		if (isset(self::$connections[$connId])) {
			return self::$connections[$connId];
		}

		if (($conn = ftp_connect($host)) === false) {
			return false;
		}

		if (isset($urlData['user'])) {
			if (!ftp_login($conn, $urlData['user'], (isset($urlData['pass'])) ? $urlData['pass'] : '')) {
				return false;
			}
		}

		self::$connections[$connId] = $conn;
		return $conn;
	}

	protected function conn_open($url) {
		$this->url = $url;

		$conn = self::conn_get($url);

		// Turn passive mode on
		if (ftp_pasv($conn, true) === false) {
			return false;
		}

		$this->conn = $conn;
		return true;
	}

	public function url_stat($url, $flags) {
		$this->conn_open($url);

		// TODO: what if the file doesn't exist?
		// TODO: implement missing fields
		$path = parse_url($url, PHP_URL_PATH);
		$stat = array(
			'size' => ftp_size($this->conn, $path),
			'mtime' => ftp_mdtm($this->conn, $path)
		);
		return $stat;
	}

	// STREAM

	public function stream_open($url, $mode, $options, &$opened_path) {
		$this->conn_open($url);

		$this->stream_handle = fopen('php://memory','r+');
		$this->stream_pos = 0;
		$this->stream_written = false;
		$this->stream_mode = $mode;

		return true;
	}

	public function stream_read($count) {
		// TODO: use ftp_nb_fget() - http://php.net/manual/en/function.ftp-nb-fget.php
		if (!$this->stream_written) {
			$path = parse_url($this->url, PHP_URL_PATH);
			if (!ftp_fget($this->conn, $this->stream_handle, $path, FTP_BINARY, $this->stream_pos)) {
				return false;
			}
			rewind($this->stream_handle);
			$this->stream_written = true;
		}

		$buffer = stream_get_contents($this->stream_handle, $count);
		$this->stream_pos += strlen($buffer);
		return $buffer;
	}

	public function stream_write($data) {
		return fwrite($this->stream_handle, $data);
	}

	public function stream_flush() {
		$path = parse_url($this->url, PHP_URL_PATH);
		rewind($this->stream_handle);
		return ftp_fput($this->conn, $path, $this->stream_handle, FTP_BINARY, $this->stream_pos);
	}

	public function stream_eof() {
		return feof($this->stream_handle);
	}

	public function stream_seek($offset, $whence = SEEK_SET) {
		if (fseek($this->stream_handle, $offset, $whence) === 0) {
			$this->stream_pos = $offset;
			return true;
		}
		return false;
	}

	public function stream_stat() {
		return fstat($this->stream_handle);
	}

	public function stream_tell() {
		return $this->stream_pos;
	}

	public function stream_close() {
		fclose($this->stream_handle);
		$this->stream_handle = null;
	}

	// DIR
	
	public function dir_opendir($url, $options) {
		$this->conn_open($url);

		$this->dir_pos = 0;

		$path = parse_url($url, PHP_URL_PATH);
		if (($this->dir_list = ftp_nlist($this->conn, $path)) === false) {
			return false;
		}

		return true;
	}

	public function dir_readdir() {
		if (($filename = next($this->dir_list)) === false) {
			return false;
		}
		$this->dir_pos++;
		return $filename;
	}

	public function dir_rewinddir() {
		reset($this->dir_list);
		$this->dir_pos = 0;
		return true;
	}

	public function dir_closedir() {
		return true;
	}

	// FS
	
	public function mkdir($url, $mode, $options) {
		$this->conn_open($url);

		//TODO: mode is ignored
		$path = parse_url($url, PHP_URL_PATH);
		if (ftp_mkdir($this->conn, $path) === false) {
			return false;
		}

		return true;
	}

	public function rename($url_from, $url_to) {
		$this->conn_open($url);

		$oldname = parse_url($url_from, PHP_URL_PATH);
		$newname = parse_url($url_to, PHP_URL_PATH);
		return ftp_rename($this->conn, $oldname, $newname);
	}

	public function unlink($url) {
		$this->conn_open($url);

		$path = parse_url($url, PHP_URL_PATH);
		return ftp_delete($this->conn, $path);
	}

	public function rmdir($url, $options) {
		$this->conn_open($url);

		$path = parse_url($url, PHP_URL_PATH);
		return ftp_rmdir($this->conn, $path);
	}
}

if (in_array('ftp', stream_get_wrappers())) {
	stream_wrapper_unregister('ftp');
}
stream_wrapper_register('ftp', 'FtpStream');