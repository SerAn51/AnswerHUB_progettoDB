<?php
    //Avvia la sessione
    session_start();
    //Istanzia variabili con dati per connettere db
    $connessione= "mysql:host=localhost;dbname=eformdb;charset=utf8mb4";
    $utente = "root";
    $password = "ert23h";

    try {
        $pdo = new PDO($connessione, $utente, $password);
        // Configura PDO per gestire gli errori in modo sicuro
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // Gestisci l'errore in modo sicuro
        echo "Errore di connessione al database: " . $e->getMessage();
        exit();
    }
?>