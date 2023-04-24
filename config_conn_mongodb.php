<?php
require 'vendor/autoload.php';

// Connessione al server di MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");

// Recupero della lista dei database
$lista_dbs = $client->listDatabases();

// Verifica se il database esiste
$nome_db = "AnswerHUB_logs";
$nome_collezione = "log";
$collezione_log = null;
foreach ($lista_dbs as $info_db) {
    if ($info_db->getName() == $nome_db) {
        // Il database esiste già, recupera la collezione
        $db_answerHUB_logs = $client->$nome_db;
        $collezione_log = $db_answerHUB_logs->$nome_collezione;
        break;
    }
}

// Se non esiste, crealo e recupera la collezione
if ($collezione_log === null) {
    $db_answerHUB_logs = $client->$nome_db;
    $db_answerHUB_logs->createCollection($nome_collezione);
    $collezione_log = $db_answerHUB_logs->$nome_collezione;
}
?>