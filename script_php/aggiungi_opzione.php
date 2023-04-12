<?php
require '../config_connessione.php'; // instaura la connessione con il db

function inserisciOpzione($pdo, int $id_domanda_chiusa, string $testo_opzione)
{
    $proc_inserisci_opzione = "CALL InserisciOpzione(:param1, :param2)";
    $prep_proc_inserisci_opzione = $pdo->prepare($proc_inserisci_opzione);
    $prep_proc_inserisci_opzione->bindParam(':param1', $id_domanda_chiusa, PDO::PARAM_INT);
    $prep_proc_inserisci_opzione->bindParam(':param2', $testo_opzione, PDO::PARAM_STR);
    $prep_proc_inserisci_opzione->execute();
}


$id_domanda_chiusa = $_POST['id_domanda_chiusa'];
$testo_opzione = $_POST['testo_opzione'];

inserisciOpzione($pdo, $id_domanda_chiusa, $testo_opzione);

header("Location: ../gestisci_opzioni.php?id_domanda=$id_domanda_chiusa&success=10");
exit;
?>