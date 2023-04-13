<?php
require '../config_connessione.php'; // instaura la connessione con il db

function eliminaSondaggio($pdo, $codice_sondaggio) {
    $elimina_sondaggio = $pdo->prepare("CALL EliminaSondaggio(:param1)");
    $elimina_sondaggio->bindParam(':param1', $codice_sondaggio, PDO::PARAM_INT);
    $elimina_sondaggio->execute();
}

if (isset($_POST["bottone"])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];

    eliminaSondaggio($pdo, $codice_sondaggio);

    header("Location: ../premium_home.php");
    exit;
}
?>