<?php

require 'config_connessione.php'; // instaura la connessione con il db
$codice_sondaggio = $_GET['cod_sondaggio'];

// controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium) gestito:
$check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
$check_sondaggio->bindParam(':email', $_SESSION['email'], PDO::PARAM_INT);
$check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
$check_sondaggio->execute();
$sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
$check_sondaggio->closeCursor();
if (!$sondaggio) {
    header("Location: premium_home.php");
    exit;
}

$mostra_dati_sondaggio = $pdo->prepare("SELECT * FROM Sondaggio WHERE Codice = :codice");
$mostra_dati_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
$mostra_dati_sondaggio->execute();
$dati_sondaggio = $mostra_dati_sondaggio->fetch(PDO::FETCH_ASSOC);
$mostra_dati_sondaggio->closeCursor();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Invita</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/checkbox_style.css">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
            background: #0c2840;
            color: #f3f7f9;
        }

        .space {
            border: 2px solid #f3f7f9;
            border-radius: 30px;
            display: flex;
            justify-content: center;
            text-align: center;
            width: auto;
            padding: 10px;
            margin: 20px;
        }
    </style>
</head>

<!--TODO: non mostrare utenti che sono già stati invitati-->
<body>
    <div class="space">
        <h2>Seleziona gli utenti da invitare per il sondaggio <?php echo $dati_sondaggio['Titolo']; ?></h2>
        <!--mostri i dati di tutti gli utenti interessati al dominio di questo specifico sondaggio-->
        <?php
        $parola_chiave_dominio_sondaggio = $dati_sondaggio['ParolachiaveDominio'];
        $mostra_utenti_interessati = $pdo->prepare("CALL MostraUtentiInteressati(:param1, :param2)");
        $mostra_utenti_interessati->bindParam(':param1', $parola_chiave_dominio_sondaggio, PDO::PARAM_STR);
        $mostra_utenti_interessati->bindParam(':param2', $codice_sondaggio, PDO::PARAM_INT);
        $mostra_utenti_interessati->execute();
        $utenti_interessati = $mostra_utenti_interessati->fetchAll(PDO::FETCH_ASSOC);
        $mostra_utenti_interessati->closeCursor();
        ?>
        <form action="script.php/manda_inviti.php" method="POST">
            <ul>
                <?php foreach ($utenti_interessati as $utente_interessato) : ?>
                    <li>
                        <input type="checkbox" name="utenti_selezionati[]" id="utenti_selezionati[] value=<?php $utente_interessato['Email'] ?>">
                        <!--mostra Email Nome, Cognome, Annonascita, Luogonascita-->
                        <label for="utente_interessato[]">
                            <?php echo $utente_interessato['Email'] . ' ' . $utente_interessato['Nome'];
                            echo ' ' . $utente_interessato['Cognome'] . ' ' . $utente_interessato['Annonascita'];
                            echo ' ' . $utente_interessato['Luogonascita'] ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <input type="submit" name="invia" id="invia" value="invia">
        </form>

    </div>
    <a href="logout.php">Effettua il logout</a>
</body>

</html>