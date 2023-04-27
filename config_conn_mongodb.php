<?php
// carica l'autoload del composer
require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

// Connessione al server di MongoDB
try {
    $client = new Client("mongodb://localhost:27017");
} catch (Exception $e) {
    // Gestisce errori di connessione al server
    die("Impossibile connettersi al server mongoDB: " . $e->getMessage());
}

// Recupero della lista dei database
try {
    $lista_dbs = $client->listDatabases();
} catch (Exception $e) {
    // Gestisce errori di recupero della lista dei db
    die("Impossibile recuperare la lista dei database: " . $e->getMessage());
}

// Verifica se il database esiste
$nome_db = "AnswerHUB_logs";
$nome_collezione = "log";
$collezione_log = null;

try {
    // Iterate over databases and find the one with the correct name
    foreach ($lista_dbs as $info_db) {
        if ($info_db->getName() == $nome_db) {
            // Il database esiste già, recupera la collezione
            $db_answerHUB_logs = $client->$nome_db;
            $collezione_log = $db_answerHUB_logs->$nome_collezione;
            break;
        }
    }

    // Se la collezione non esiste, la crea
    if ($collezione_log === null) {
        $db_answerHUB_logs = $client->$nome_db;
        $db_answerHUB_logs->createCollection($nome_collezione);
        $collezione_log = $db_answerHUB_logs->$nome_collezione;
    }
} catch (Exception $e) {
    // Gestisce errori di database e collezioni
    die("Impossibile recuperare database/collezioni: " . $e->getMessage());
}

?>