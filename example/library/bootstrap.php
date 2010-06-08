<?php

session_name('LAZY_SID');

$storagePath = realpath(dirname(__FILE__)).'/../session_storage';
if (!is_writable($storagePath)) {
	print "<h1>Error: StoragePath <code>lazy_sessions/example/session_storage/</code> is not writable. The example cannot operate. Please make this directory writable by your webserver to run this example.";
}
session_save_path($storagePath);

require_once('../../lazy_sessions.php');
