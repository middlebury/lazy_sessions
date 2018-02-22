<?php
/**
 * This file registers session save handlers so that sessions are not created if no data
 * has been added to the $_SESSION array.
 * 
 * This code is based on the session handling code in Pressflow (a backport of
 * Drupal 7 performance features to Drupal 6) as well as the example code described
 * the PHP.net documentation for session_set_save_handler(). The actual session data
 * storage in the file-system is directly from the PHP.net example while the switching
 * based on session data presence is merged in from Pressflow's includes/session.inc
 *
 * Links:
 *		http://www.php.net/manual/en/function.session-set-save-handler.php
 *		http://bazaar.launchpad.net/~pressflow/pressflow/6/annotate/head:/includes/session.inc
 *
 * Caveats:
 * 		- Requires output buffering before session_write_close(). If content is 
 *		  sent before shutdown or session_write_close() is called manually, then 
 *		  the check for an empty session won't happen and Set-Cookie headers will
 *		  get sent.
 *		  
 *		  Work-around: Call session_write_close() before using flush();
 *		  
 *		- The current implementation blows away all Set-Cookie headers if the
 *		  session is empty. This basic implementation will prevent any additional
 *		  cookie use and should be improved if using non-session cookies.
 *
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL), Version 3 or later.
 */ 

/*********************************************************
 * Storage Callbacks
 *********************************************************/

function lazysess_open($save_path, $session_name)
{
	global $sess_save_path;

	$sess_save_path = $save_path;
	return(true);
}

function lazysess_close()
{
	return(true);
}

function lazysess_read($id)
{ 
	// Write and Close handlers are called after destructing objects
	// since PHP 5.0.5.
	// Thus destructors can use sessions but session handler can't use objects.
	// So we are moving session closure before destructing objects.
	register_shutdown_function('session_write_close');

	// Handle the case of first time visitors and clients that don't store cookies (eg. web crawlers).
	if (!isset($_COOKIE[session_name()])) {
		return '';
	}
	
	// Continue with reading.
	global $sess_save_path;
	
	$sess_file = "$sess_save_path/sess_$id";
	$return = (string) @file_get_contents($sess_file);
	if ($return === FALSE) {
		return FALSE;
	} else {
		return $return;
	}
}

function lazysess_write($id, $sess_data)
{ 
	// If saving of session data is disabled, or if a new empty anonymous session
	// has been started, do nothing. This keeps anonymous users, including
	// crawlers, out of the session table, unless they actually have something
	// stored in $_SESSION.
	if (empty($_COOKIE[session_name()]) && empty($sess_data)) {
		
		// Ensure that the client doesn't store the session cookie as it is worthless
		lazysess_remove_session_cookie_header();
		
		return TRUE;
	}
	
	// Continue with storage
	global $sess_save_path;
	
	$sess_file = "$sess_save_path/sess_$id";
	if ($fp = @fopen($sess_file, "w")) {
		$return = fwrite($fp, $sess_data);
		fclose($fp);
		return $return;
	} else {
		return(false);
	}

}

function lazysess_destroy($id)
{
	// If the session ID being destroyed is the one of the current user,
	// clean-up his/her session data and cookie.
	if ($id == session_id()) {
		global $user;

		// Reset $_SESSION and $user to prevent a new session from being started
		// in drupal_session_commit()
		$_SESSION = array();
		
		// Unset the session cookie.
		lazysess_set_delete_cookie_header();
		if (isset($_COOKIE[session_name()])) {
			unset($_COOKIE[session_name()]);
		}
	}


	// Continue with destruction
	global $sess_save_path;

	$sess_file = "$sess_save_path/sess_$id";
	return(@unlink($sess_file));
}

function lazysess_gc($maxlifetime)
{
	global $sess_save_path;

	foreach (glob("$sess_save_path/sess_*") as $filename) {
		if (filemtime($filename) + $maxlifetime < time()) {
			@unlink($filename);
		}
	}
	return true;
}

/*********************************************************
 * Helper functions
 *********************************************************/

function lazysess_set_delete_cookie_header() {
	$params = session_get_cookie_params();
	
	if (version_compare(PHP_VERSION, '5.2.0') === 1) {
		setcookie(session_name(), '', $_SERVER['REQUEST_TIME'] - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	else {
		setcookie(session_name(), '', $_SERVER['REQUEST_TIME'] - 3600, $params['path'], $params['domain'], $params['secure']);			
	}
}

function lazysess_remove_session_cookie_header () {
	// Note: this implementation will blow away all Set-Cookie headers, not just
	// those for the session cookie. If your app uses other cookies, reimplement
	// this function.
	if (version_compare(PHP_VERSION, '5.3.0') === 1) {
		header_remove('Set-Cookie');
	} else {
		// PHP < 5.3 only allows sending empty headers, not fully removing them.
		// These empty Set-Cookie headers can prevent proxies from caching the response.
		//
		// If using PHP < 5.3.0 and using Varnish for caching, add the following
		// to the vcl_fetch section of your Varnish default.vcl before the line that
		// passes if Set-Cookie headers are present:
		//
		// 		# If using PHP < 5.3 there is no way to fully delete headers, so empty
		//		# Set-Cookie headers may be in the response. Ignore these empty headers.
		//		if (beresp.http.Set-Cookie ~ "^\s*$") {
		//			unset beresp.http.Set-Cookie;
		//		}
		//
		header('Set-Cookie:', true);
	}
}

/*********************************************************
 * Register the save handlers
 *********************************************************/

session_set_save_handler('lazysess_open', 'lazysess_close', 'lazysess_read', 'lazysess_write', 'lazysess_destroy', 'lazysess_gc');
