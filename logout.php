<?php
require 'config_connessione.php'; // instaura la connessione con il db
$_SESSION = [];
session_unset();
session_destroy();
header("Location: login.php");
?>