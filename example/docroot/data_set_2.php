<?php
require('../library/bootstrap.php');


// Now we are going to save some data in the session.
$_SESSION['test-key-2'] = 'test-val-2';


$title = 'Data-Set 2';
include('../library/content.php');