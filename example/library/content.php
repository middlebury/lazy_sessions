<html>
<head>
	<title><?php echo $title; ?></title>
</head>
</body>
	<h1><?php echo $title; ?></h1>
	<a href='no_data_set_1.php'>No data set 1</a>
	<a href='no_data_set_2.php'>No data set 2</a>
	<a href='data_set_1.php'>Data set 1</a>
	<a href='data_set_2.php'>Data set 2</a>
	<a href='logout.php'>Logout</a>
	
	<h2><?php echo session_name().'='.session_id() ?></h2>
	
	<pre>
	<?php print_r($_SESSION); ?>
	</pre>
</body>
</html>