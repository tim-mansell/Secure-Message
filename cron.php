<?php
require_once('includes\config.php');

function dbConnect(){
    GLOBAL $configArray;
    $dbConnection = new PDO('mysql:dbname=' . $configArray["dbName"] . ';host=' . $configArray["dbHost"] .';charset=utf8', $configArray["dbUser"], $configArray["dbPassword"]);
    return $dbConnection;
}

function removeSecret($token) {
    $dbLink = dbConnect();
    $query = $dbLink->prepare('UPDATE data set status = ? WHERE token = ?');
    $array = array("1","$token");
    $query->execute($array);
    $query = $dbLink->prepare('DELETE FROM data WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
    $query = $dbLink->prepare('DELETE from tokens WHERE token = ?');
    $array = array("$token");
    $query->execute($array);
}

$dbLink = dbConnect();
$query = $dbLink->prepare("SELECT * FROM data WHERE timetolive < NOW()");
$query->execute();
$expiredTokens = $query->fetchAll(PDO::FETCH_ASSOC);

	foreach($expiredTokens as $secret){
		removeSecret($secret['token']);
	}	
?>