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

        li {
            list-style: none;
        }
    </style>
</head>

<body>
    <div class="space">
        <?php if (isset($dati_sondaggio) && is_array($dati_sondaggio)) { ?>
            <h2>Seleziona gli utenti da invitare per il sondaggio <?php echo $dati_sondaggio['Titolo']; ?></h2>
            <!--mostri i dati di tutti gli utenti interessati al dominio di questo specifico sondaggio-->
        <?php
            if (isset($dati_sondaggio) && is_array($dati_sondaggio)) {
                $parola_chiave_dominio_sondaggio = $dati_sondaggio['ParolachiaveDominio'];
                $mostra_utenti_interessati = $pdo->prepare("CALL MostraUtentiInteressati(:param1, :param2)");
                $mostra_utenti_interessati->bindParam(':param1', $parola_chiave_dominio_sondaggio, PDO::PARAM_STR);
                $mostra_utenti_interessati->bindParam(':param2', $codice_sondaggio, PDO::PARAM_INT);
                $mostra_utenti_interessati->execute();
                $utenti_interessati = $mostra_utenti_interessati->fetchAll(PDO::FETCH_ASSOC);
                $mostra_utenti_interessati->closeCursor();
            }
        }
        ?>
        <form action="script_php/manda_inviti.php" method="POST">
            <?php if (isset($_GET['error']) && ($_GET['error'] == 10)) {
                echo "Devi selezionare almeno un utente";
            } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                echo "Utenti invitati con successo";
            } else if (isset($_GET['error']) && $_GET['error'] == 20) {
                echo "Per questo sondaggio hai già invitato tutti gli utenti possibili";
            } else if (isset($_GET['error']) && $_GET['error'] == 30) {
                echo "Hai selezionato troppi utenti, puoi selezionare" . " " . $_GET['num_selezionabili'] . " " . "utente/i";
            }
            ?>
            <ul>
                <?php if (isset($utenti_interessati) && is_array($utenti_interessati)) {
                    foreach ($utenti_interessati as $utente_interessato) { ?>
                        <li>
                            <input type="checkbox" name="utenti_selezionati[]" value="<?php echo $utente_interessato['Email'] ?>">
                            <!-- mostra Email Nome, Cognome, Annonascita, Luogonascita -->
                            <label for="utente_interessato[]">
                                <?php echo $utente_interessato['Email'] . ' ' . $utente_interessato['Nome'];
                                echo ' ' . $utente_interessato['Cognome'] . ' ' . $utente_interessato['Annonascita'];
                                echo ' ' . $utente_interessato['Luogonascita'] ?>
                            </label>
                        </li>
                <?php }
                } ?>
            </ul>
            <input type="hidden" name="codice_sondaggio" value="<?php echo $codice_sondaggio; ?>"><!--Per inviare il codice_sondaggio tramite POST-->
            <input type="submit" name="invita" id="invita" value="invita">
        </form>

    </div>
    <a href="premium_home.php">Torna alla home</a>
    <a href="logout.php">Effettua il logout</a>
</body>

</html>