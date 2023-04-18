<?php
require '../config_connessione.php'; // instaura la connessione con il db

$email = $_SESSION["email"];

$diventa_premium = $pdo->prepare("CALL DiventaPremium(:param1)");
$diventa_premium->bindParam(':param1', $email, PDO::PARAM_STR);
$diventa_premium->execute();

header("Location: ../index.php");
exit;
?>