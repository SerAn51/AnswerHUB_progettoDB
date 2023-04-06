<?php
require '../config_connessione.php'; // instaura la connessione con il db

if (isset($_POST['invia'])) {
    $array_utenti_selezionati = $_POST["utenti_selezionati"];
    if (is_array($array_utenti_selezionati)) {
        foreach ($array_utenti_selezionati as $utente_selezionato) {
            //fai una insert in Invito(ID, Esito, EmailUtente, CodiceSondaggio, CFAziendainvitante, EmailUtenteinvitante)
            
        }
    }
}
?>