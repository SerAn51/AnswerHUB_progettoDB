<?php
require '../config_connessione.php'; // instaura la connessione con il db

require '../config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste
use MongoDB\BSON\UTCDateTime;

function inserisciOpzione($pdo, int $id_domanda_chiusa, string $testo_opzione, $collezione_log)
{
    try {
        $proc_inserisci_opzione = "CALL InserisciOpzione(:param1, :param2)";
        $prep_proc_inserisci_opzione = $pdo->prepare($proc_inserisci_opzione);
        $prep_proc_inserisci_opzione->bindParam(':param1', $id_domanda_chiusa, PDO::PARAM_INT);
        $prep_proc_inserisci_opzione->bindParam(':param2', $testo_opzione, PDO::PARAM_STR);
        $prep_proc_inserisci_opzione->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: ../logout.php");
        exit;
    }

    // Informazione da inserire nella collezione di log
    $informazione_log = array(
        "data" => new MongoDB\BSON\UTCDateTime(),
        "azione" => "Inserimento nuova opzione",
        "dettagli" => array(
            "id_domanda_chiusa" => $id_domanda_chiusa,
            "testo_opzione" => $testo_opzione
        )
    );

    // Inserimento dell'informazione nella collezione di log
    $collezione_log->insertOne($informazione_log);
}

if (isset($_POST["aggiungi_opzione"])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];
    $id_domanda_chiusa = $_POST['id_domanda_chiusa'];
    $testo_opzione = $_POST['testo_opzione'];

    //Un utente premium non può creare due opzioni con lo stesso testo (ignorando maiuscole e minuscole), questo perche'
    //lato utente che risponde ai sondaggi si potrebbe creare confusione, sono ammesse opzioni con lo stesso testo a patto che siano appartenenti a due domande diverse
    try {
        $proc_mostra_opzioni = $pdo->prepare("CALL MostraOpzioni(:param1)");
        $proc_mostra_opzioni->bindParam(':param1', $id_domanda_chiusa, PDO::PARAM_INT);
        $proc_mostra_opzioni->execute();
        $opzioni = $proc_mostra_opzioni->fetchAll(PDO::FETCH_ASSOC);
        $proc_mostra_opzioni->closeCursor();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: ../logout.php");
        exit;
    }
    foreach ($opzioni as $opzione) {
        if (strcasecmp($opzione['Testo'], $testo_opzione) == 0) {
            header("Location: ../gestisci_opzioni.php?cod_sondaggio=$codice_sondaggio&id_domanda=$id_domanda_chiusa&error=10");
            exit;
        }
    }

    inserisciOpzione($pdo, $id_domanda_chiusa, $testo_opzione, $collezione_log);

    header("Location: ../gestisci_opzioni.php?cod_sondaggio=$codice_sondaggio&id_domanda=$id_domanda_chiusa&success=10");
    exit;
}
?>