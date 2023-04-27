<?php
require '../config_connessione.php'; // instaura la connessione con il db

function rimuoviOpzione($pdo, $id_domanda_chiusa, $numero_progressivo)
{
    try {
        $rimuovi_opzione = $pdo->prepare("CALL RimuoviOpzione(:param1, :param2)");
        $rimuovi_opzione->bindParam(':param1', $id_domanda_chiusa, PDO::PARAM_INT);
        $rimuovi_opzione->bindParam(':param2', $numero_progressivo, PDO::PARAM_INT);
        $rimuovi_opzione->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }
}

if (isset($_POST["bottone"])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];
    $id_domanda_chiusa = $_POST['id_domanda'];
    $numero_progressivo = $_POST['numero_progressivo'];

    //se elimino l'ultima opzione ok, ma se elimino un'opzione nel mezzo le successive devono scalare come numeroprogressivo
    rimuoviOpzione($pdo, $id_domanda_chiusa, $numero_progressivo);

    header("Location: ../gestisci_opzioni.php?cod_sondaggio=$codice_sondaggio&id_domanda=$id_domanda_chiusa&success=30");
    exit;
}
?>