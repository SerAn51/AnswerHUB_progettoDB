<?php

require 'config_conn_mongodb.php'; // instaura la connessione con mongodb, creando db e collezione se non esiste

// esegui una query per recuperare tutti i documenti
$documents = $collezione_log->find([]);

// stampa il campo "nome" di ogni documento
foreach ($documents as $document) {
    // ottieni la data dal documento come un oggetto UTCDateTime di MongoDB
    $data_mongodb = $document->data;

    // converte la data in PHP DateTime
    $data_php = $data_mongodb->toDateTime();

    // formatta la data come stringa leggibile
    $data_leggibile = $data_php->format("d/m/Y H:i:s");

    echo $data_leggibile . " " . $document->azione . "<br>";
}


?>