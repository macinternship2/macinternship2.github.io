<html>
 <head>
 </head>
 <body>
<?php 
  echo 'This is from PHP';


$user = 'root';
$password = 'Jagruti@12345';
$db = 'hhaccessibility';
$host = '127.0.0.1';
$port = 3306;

echo "After setting a few variables.";
$link = mysqli_connect($host . ':' . $port, $user, $password, $db);

if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
}
else {
	echo "Success: A proper connection to MySQL was made! The my_db database is great." . PHP_EOL;
	echo "Host information: " . mysqli_get_host_info($link) . PHP_EOL;

	mysqli_close($link);

}




phpinfo();

?>
 </body>
</html>