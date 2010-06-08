<?php
require('../library/bootstrap.php');


// Now we are going to save some data in the session.
$_SESSION['test-key-1'] = 'test-val-1';


$title = 'Data-Set 1';
include('../library/content.php');