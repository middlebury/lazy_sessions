<?php
require('../library/bootstrap.php');


// If session_write_close() is not called before flushing, then the Set-Cookie
// header will be sent before our custom session handler has a chance to determine
// if a session is even needed.
session_write_close();


print "Hello";
flush();
print " World.";
flush();



$title = 'No-Data-Set With flush()';
include('../library/content.php');