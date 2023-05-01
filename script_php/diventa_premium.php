<?php
require '../config_connessione.php'; // instaura la connessione con il db

if (isset($_POST["diventa_premium"])) {
    $email = $_SESSION["email"];

    try {
        $diventa_premium = $pdo->prepare("CALL DiventaPremium(:param1)");
        $diventa_premium->bindParam(':param1', $email, PDO::PARAM_STR);
        $diventa_premium->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: ../logout.php");
        exit;
    }

    header("Location: ../index.php");
    exit;
}
?>