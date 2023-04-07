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

$mostra_domande_sondaggio = $pdo->prepare("SELECT * FROM Sondaggio WHERE Codice = :codice");
$mostra_domande_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
$mostra_domande_sondaggio->execute();
$domande_sondaggio = $mostra_domande_sondaggio->fetch(PDO::FETCH_ASSOC);
$mostra_domande_sondaggio->closeCursor();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Inserisci domanda</title>

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
        <!--Mostra tutte le domande-->
        <h2>Domande</h2>
        <!--Cliccando su una domanda chiusa, posso andare ad inserire nuove opzioni-->
    </div>

    <div class="space">
        <!--Crea una nuova domanda-->
        <h2>Inserisci una nuova domanda</h2>
        <!--L'inserimento deve avvenire in Domanda, in ComponenteSondaggioDomanda e
        se APERTA anche in DomandaAperta, altrimenti in DomandaChiusa
        (in questo caso, si devono inserire le opzioni-->
    </div>

    <a href="premium_home.php">Torna alla home</a>
    <a href="logout.php">Effettua il logout</a>
</body>

</html>