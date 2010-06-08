<?php
require('../library/bootstrap.php');


// Now we are going to save some data in the session.
$_SESSION['test-key-4'] = 'test-val-4';

print "Hello";
flush();
print " World.";
flush();


$title = 'Data-Set With flush() after';
include('../library/content.php');