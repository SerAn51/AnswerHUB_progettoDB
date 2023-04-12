<?php
require 'config_connessione.php'; // instaura la connessione con il db

$codice_sondaggio = $_GET['cod_sondaggio'];
$id_domanda = $_GET['id_domanda'];

// controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium) gestito:
$check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
$check_sondaggio->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
$check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
$check_sondaggio->execute();
$sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
$check_sondaggio->closeCursor();
if (!$sondaggio) {
    header("Location: premium_home.php");
    exit;
}

// controllo per evitare che si cambi url e si faccia l'accesso ad una domanda di un altro utente premium, al massimo se cambio url per il get dell'ID della domanda posso mettere quella di una domanda chiusa da me (utente premium) gestita:
$aperta_chiusa = "CHIUSA";
$check_domanda = $pdo->prepare("SELECT ID FROM Domanda WHERE EmailUtenteinserente = :email AND ID = :id AND ApertaChiusa = :aperta_chiusa");
$check_domanda->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
$check_domanda->bindParam(':id', $id_domanda, PDO::PARAM_INT);
$check_domanda->bindParam(':aperta_chiusa', $aperta_chiusa, PDO::PARAM_STR);
$check_domanda->execute();
$domanda = $check_domanda->fetch(PDO::FETCH_ASSOC);
$check_domanda->closeCursor();
if (!$domanda) {
    header("Location: premium_home.php");
    exit;
}


$mostra_opzioni_domanda = $pdo->prepare("CALL MostraOpzioni(:id_domanda)");
$mostra_opzioni_domanda->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
$mostra_opzioni_domanda->execute();
$opzioni_domanda = $mostra_opzioni_domanda->fetchAll(PDO::FETCH_ASSOC);
$mostra_opzioni_domanda->closeCursor();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Gestisci opzioni</title>

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
    <!--VISUALIZZA OPZIONI-->
    <div class="space">
        <h2>Opzioni</h2>
        <ul>
            <?php foreach ($opzioni_domanda as $opzione) { ?>
                <li>
                    <?php echo $opzione['Numeroprogressivo'] . ' ' . $opzione['Testo'] . '<br>'; ?>
                </li>
            <?php } ?>
        </ul>
    </div>


    <!--AGGIUNGI UN'OPZIONE, NB: quando invito, se non ci sono opzioni, mostra messaggio-->
    <?php
    $check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
    $check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
    $check_inviti->execute();
    $inviti = $check_inviti->fetchAll();
    $check_inviti->closeCursor();
    ?>
    <div class="space">
        <h2>Aggiungi opzione</h2>
        <?php if ((isset($_GET['success'])) && ($_GET['success'] == 10)) {
            echo "Opzione inserita con successo";
        }
        ?>
        <?php
        // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' inserire opzioni-->
        if ($inviti && count($inviti) > 0) {
            echo "E' stato gia' inviato almeno un utente, per questa domanda non e' piu' possibile inserire opzioni";
        } else { ?>
            <form action="script_php/aggiungi_opzione.php" method="POST">
                <input type="text" name="testo_opzione" id="testo_opzione">
                <label for="testo_opzione">Testo</label>
                <input type="hidden" name="id_domanda_chiusa" id="id_domanda_chiusa" value="<?php echo $id_domanda; ?>">
                <input type="submit" name="aggiungi_opzione" id="aggiungi_opzione" value="Aggiungi opzione">
            </form>
        <?php }
        ?>
    </div>

    <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $codice_sondaggio ?>">Torna indietro</a>
    <a href="premium_home.php">Torna alla home</a>
</body>

</html>