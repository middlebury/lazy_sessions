<?php
require('../library/bootstrap.php');


// Now we are going to save some data in the session.

session_unset();
session_destroy();
$_SESSION = array();


$title = 'Log Out';
include('../library/content.php');