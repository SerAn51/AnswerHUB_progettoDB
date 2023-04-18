<?php
require '../config_connessione.php'; // instaura la connessione con il db

function diventaAmministratore($pdo, $email)
{
    $diventa_amministratore = $pdo->prepare("CALL DiventaAmministratore(:param1)");
    $diventa_amministratore->bindParam(':param1', $email, PDO::PARAM_STR);
    $diventa_amministratore->execute();
}

if (isset($_POST["diventa_amministratore"])) {
    if (isset($_POST['checkbox_codice_amm']) && $_POST['checkbox_codice_amm'] == 'on') { // La checkbox è stata selezionata
        $codice_amministratore = $_POST["codice_amm"];
        if (isset($codice_amministratore) && ($codice_amministratore == 66)) { //che bello Star Wars pt2
            $email = $_SESSION['email'];
            diventaAmministratore($pdo, $email);
            header("Location: ../index.php");
            exit;
        } else {
            header("Location: ../semplice_home.php?error=10");
            exit;
        }
    }
}
?>