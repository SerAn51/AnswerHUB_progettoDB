<?php
require '../config_connessione.php'; // instaura la connessione con il db

function eliminaSondaggio($pdo, $codice_sondaggio)
{
    try {
        $elimina_sondaggio = $pdo->prepare("CALL EliminaSondaggio(:param1)");
        $elimina_sondaggio->bindParam(':param1', $codice_sondaggio, PDO::PARAM_INT);
        $elimina_sondaggio->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }
}

if (isset($_POST["elimina"])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];

    eliminaSondaggio($pdo, $codice_sondaggio);

    //dato che e' utilizzato sia da azienda che da utente premium,
    //reindirizzo a index al cui interno gestisco il giusto reindirizzamento se verso la home dell'azienda o verso la home dell'utente
    header("Location: ../index.php");
    exit;
}
?>