<?php
require '../config_connessione.php'; // instaura la connessione con il db

function rimuoviDomanda($pdo, $id_domanda) {
    $rimuovi_domanda = $pdo->prepare("CALL RimuoviDomanda(:param1)");
    $rimuovi_domanda->bindParam(':param1', $id_domanda, PDO::PARAM_INT);
    $rimuovi_domanda->execute();
}

if (isset($_POST["bottone"])) {
    $id_domanda = $_POST['id_domanda'];// basta l'id della domanda e la rimozione da domanda,
    //inutile il codice del sondaggio in quanto rimuovo in Domanda e a cascata rimuove in ComponenteSondaggioDomanda e le altre tabelle

    // codice_sondaggio utile solo per il get di ritorno
    $codice_sondaggio = $_POST['codice_sondaggio'];

    //se elimino l'ultima opzione ok, ma se elimino un'opzione nel mezzo le successive devono scalare come numeroprogressivo
    rimuoviDomanda($pdo, $id_domanda);

    header("Location: ../gestisci_domanda.php?cod_sondaggio=$codice_sondaggio&success=20");
    exit;
}
?>