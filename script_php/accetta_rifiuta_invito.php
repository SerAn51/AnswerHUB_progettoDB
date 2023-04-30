<?php

require '../config_connessione.php'; // instaura la connessione con il db

function accettaRifiutaInvito($pdo, $decisione, string $email, string $id_invito)
{
    //Delete nella tabella Interessato che rimuove la riga con questa email e questa password
    try {
        $proc_accetta_rifiuta_invito = "CALL AccettaRifiutaInvito(:param1, :param2, :param3)";
        $prep_proc_accetta_rifiuta_invito = $pdo->prepare($proc_accetta_rifiuta_invito);
        $prep_proc_accetta_rifiuta_invito->bindParam(':param1', $decisione, PDO::PARAM_BOOL);
        $prep_proc_accetta_rifiuta_invito->bindParam(':param2', $email, PDO::PARAM_STR);
        $prep_proc_accetta_rifiuta_invito->bindParam(':param3', $id_invito, PDO::PARAM_INT);
        $prep_proc_accetta_rifiuta_invito->execute();
    } catch (PDOException $e) {
        echo "Errore Stored Procedure: " . $e->getMessage();
        //header("Location: ../logout.php");
        exit;
    }
}

if (isset($_POST["invito_accettato"])) {
    accettaRifiutaInvito($pdo, true, $_SESSION["email"], $_POST["invito_accettato"]);
} else {
    accettaRifiutaInvito($pdo, false, $_SESSION["email"], $_POST["invito_rifiutato"]);
}
//reindirizza alla pagina index.php
header("Location: ../index.php");
exit(); //termina l'esecuzione dello script dopo il reindirizzamento
?>