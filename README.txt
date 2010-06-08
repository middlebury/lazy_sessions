=====================================
 About
=====================================

The lazy_sessions.php file registers session save handlers so that sessions are not created if no data
has been added to the $_SESSION array.

Copyright &copy; 2010, Middlebury College
Licensed under the GNU General Public License (GPL), Version 3 or later. http://www.gnu.org/copyleft/gpl.html

This code is based on the session handling code in Pressflow (a backport of
Drupal 7 performance features to Drupal 6) as well as the example code described
the PHP.net documentation for session_set_save_handler(). The actual session data
storage in the file-system is directly from the PHP.net example while the switching
based on session data presence is merged in from Pressflow's includes/session.inc

Links:
		http://www.php.net/manual/en/function.session-set-save-handler.php
		http://bazaar.launchpad.net/~pressflow/pressflow/6/annotate/head:/includes/session.inc

Caveats:
		- Requires output buffering before session_write_close(). If content is 
		  sent before shutdown or session_write_close() is called manually, then 
		  the check for an empty session won't happen and Set-Cookie headers will
		  get sent.
		  
		  Work-around: Call session_write_close() before using flush();
		  
		- The current implementation blows away all Set-Cookie headers if the
		  session is empty. This basic implementation will prevent any additional
		  cookie use and should be improved if using non-session cookies.


=====================================
 Usage
=====================================
For usage in basic applications that do not have complex behavior related to sessions or 
output flushing, it is enough to just include lazy_session.php before calling session_start():

	<?php
	
	// Include files or other pre-session_start code
	
	require_once('lazy_sessions/lazy_sessions.php');
	start_session();
	
	// The rest of the application code.
	?>


If your application needs to flush content and thereby send headers before script
shutdown (such as incrementally sending file data), call session_write_close() 
if session_start() has been called for that script:

	<?php
	
	// Include files or other pre-session_start code
	
	require_once('lazy_sessions/lazy_sessions.php');
	start_session();
	
	// other application code.
	
	// If session_write_close() is not called before flushing, then the Set-Cookie
	// header will be sent before our custom session handler has a chance to determine
	// if a session is even needed.
	session_write_close();
	
	
	print "Hello";
	flush();
	print " World.";
	flush();
	
	?>



=====================================
 Running the example
=====================================
1.	Make the directory lazy_sessions/example/docroot/ available in a web-accessible location.
2.	Make the directory lazy_sessions/example/session_storage/ writable by your webserver.
3.	Navigate to the URL of the docroot in your web browser. As you browse the links, you
	should observer this expected behavior:
	-	If you do not click a 'data_set' link, you should not recieve a 'Set-Cookie' header
		in any responses.
	-	Once you click on a 'data-set' link, you should recieve a 'Set-Cookie' header in the
		responce and your session should be maintained on the no_data_set pages.
	-	Clicking the 'logout' link should destroy your session and respond with a
		delete-cookie header.

