<?php

require '../config_connessione.php'; // instaura la connessione con il db

require '../config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste
use MongoDB\BSON\UTCDateTime;

function inserisciDominio($pdo, $parola_chiave, $descrizione, $collezione_log)
{
    //Insert nella tabella Premio che mette come EmailUtenteAmministratore la mail di sessione
    try {
        $proc_inserisci_dominio = "CALL InserisciDominio(:param1, :param2)";
        $prep_proc_inserisci_dominio = $pdo->prepare($proc_inserisci_dominio);
        $prep_proc_inserisci_dominio->bindParam(':param1', $parola_chiave, PDO::PARAM_STR);
        $prep_proc_inserisci_dominio->bindParam(':param2', $descrizione, PDO::PARAM_STR);
        $prep_proc_inserisci_dominio->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        header("Location: logout.php");
        exit;
    }

    // Informazione da inserire nella collezione di log
    $informazione_log = array(
        "data" => new MongoDB\BSON\UTCDateTime(),
        "azione" => "Inserimento nuovo dominio",
        "dettagli" => array(
            "parola_chiave_dominio" => $parola_chiave,
            "descrizione" => $descrizione
        )
    );

    // Inserimento dell'informazione nella collezione di log
    $collezione_log->insertOne($informazione_log);
}

if (isset($_POST["inserisci"])) {
    $parola_chiave = $_POST["parola_chiave"];
    $descrizione = $_POST["descrizione"];
    inserisciDominio($pdo, $parola_chiave, $descrizione, $collezione_log);

    header('Location: ../amministratore_home.php?success=20'); //i codici da 20 a 29 gestiscono l'inserimento del dominio
} else {
    // il tipo di file dell'immagine selezionata non è valido
    header('Location: ../amministratore_home.php?error=20');
}