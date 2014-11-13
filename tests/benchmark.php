<?php
ini_set('display_errors', 1);
set_time_limit(0);

require(__DIR__.'/../vendor/autoload.php');

$loops = 10;
$file = 'ftp://host/home/user/.profile';

function benchmark($op, $loops) {
	if ($loops <= 0) {
		return 0;
	}

	$startTime = microtime(true);
	for ($i = 0; $i < $loops; $i++) {
		$op();
		
	}
	return (microtime(true) - $startTime) / $loops;
}

$ops = array(
	'Reading a file' => function () use($file) {
		file_get_contents($file);
	},
	'Calling stat()' => function () use($file) {
		stat($file);
	},
	'Reading a directory' => function () use($file) {
		$dir = opendir(dirname($file));
		$list = array();
		while (($file = readdir($dir)) !== false) {
			$list[] = $file;
		}
		closedir($dir);
	}
);

$results = 'Operation;Native wrapper;php-wrapper'."\n";
foreach ($ops as $opTitle => $op) {
	// Using the native wrapper
	$native = benchmark($op, $loops);
	echo $opTitle.' with native wrapper: '.$native.' s/op'.PHP_EOL;

	// Using php-wrappers
	Wrappers\FtpStream::register();
	$wrappers = benchmark($op, $loops);
	echo $opTitle.' with php-wrappers: '.$wrappers.' s/op'.PHP_EOL;
	Wrappers\FtpStream::unregister();

	$results .= $opTitle.';'.$native.';'.$wrappers."\n";
}

file_put_contents(__DIR__.'/benchmark-results.csv', $results);