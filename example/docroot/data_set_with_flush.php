<?php
require('../library/bootstrap.php');

print "Hello";
flush();
print " World.";
flush();

// Now we are going to save some data in the session.
$_SESSION['test-key-3'] = 'test-val-3';



$title = 'Data-Set with flush() before';
include('../library/content.php');