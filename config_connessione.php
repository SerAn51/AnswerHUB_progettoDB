<?php
    //Avvia la sessione
    session_start();
    //Istanzia variabili con dati per connettere db
    $connessione= "mysql:host=localhost;dbname=AnswerHUB_DB;charset=utf8mb4";
    $utente = "root";
    $password = "erT23.hk";
    //apri cartella da terminale ed esegui php -S localhost:8080

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