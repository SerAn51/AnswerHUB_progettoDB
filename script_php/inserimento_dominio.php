<?php

require '../config_connessione.php'; // instaura la connessione con il db

function inserisciDominio($pdo, $parola_chiave, $descrizione)
{
    //Insert nella tabella Premio che mette come EmailUtenteAmministratore la mail di sessione
    $proc_inserisci_dominio = "CALL InserisciDominio(:param1, :param2)";
    $prep_proc_inserisci_dominio = $pdo->prepare($proc_inserisci_dominio);
    $prep_proc_inserisci_dominio->bindParam(':param1', $parola_chiave, PDO::PARAM_STR);
    $prep_proc_inserisci_dominio->bindParam(':param2', $descrizione, PDO::PARAM_STR);
    $prep_proc_inserisci_dominio->execute();
}

if (isset($_POST["inserisci"])) {
    $parola_chiave = $_POST["parola_chiave"];
    $descrizione = $_POST["descrizione"];
    inserisciDominio($pdo, $parola_chiave, $descrizione);

    header('Location: ../amministratore_home.php?success=20');//i codici da 20 a 29 gestiscono l'inserimento del dominio
} else {
    // il tipo di file dell'immagine selezionata non è valido
    header('Location: ../amministratore_home.php?error=20');
}
