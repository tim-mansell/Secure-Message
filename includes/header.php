<!DOCTYPE html>
<?php
require_once('includes\config.php');
require_once('includes\secret.php');
?>
<html>
    <head>
        <meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php echo $configArray["siteName"];?></title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
        <link rel="icon" type="image/x-icon" href="favicon.ico">
        <script src="js/functions.js"></script>
        <script src="js/jquery.js"></script>
        <script src="js/autosize.js"></script>
		<script src="js/gibberish-aes-1.0.0.min.js"></script>
    </head>
    <body class='text-center' style='margin-top:5em;padding-left:1em;padding-right:0.5em;'>
		<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
			<a class="navbar-brand" href="#"><?php echo $configArray["siteName"];?></a>
		</nav>