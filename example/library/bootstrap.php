<?php

/*********************************************************
 * Example set-up stuff, not needed for real usage.
 *********************************************************/
session_name('LAZY_SID');

$storagePath = realpath(dirname(__FILE__)).'/../session_storage';
if (!is_writable($storagePath)) {
	print "<h1>Error: StoragePath <code>lazy_sessions/example/session_storage/</code> is not writable. The example cannot operate. Please make this directory writable by your webserver to run this example.";
}
session_save_path($storagePath);



/*********************************************************
 * Include the lazy_sessions.php file.
 *********************************************************/
require_once('../../lazy_sessions.php');

/*********************************************************
 * Start the Session. Unless data gets saved to the session
 * no session will actually be saved.
 *********************************************************/
session_start();
