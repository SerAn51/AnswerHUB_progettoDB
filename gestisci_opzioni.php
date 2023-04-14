<?php
require 'config_connessione.php'; // instaura la connessione con il db

$codice_sondaggio = $_GET['cod_sondaggio'];
$id_domanda = $_GET['id_domanda'];

// controllo per evitare che si cambi url e si faccia l'accesso ad un sondaggio di un altro utente premium/azienda, al massimo se cambio url per il get del codice posso mettere il codice di un sondaggio da me (utente premium/azienda) gestito:
// se email non e' vuota vuol dire che ho richiamato gestisci_domanda come utente premium
if (!(empty($_SESSION["email"]))) {
    $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE EmailUtentecreante = :email AND Codice = :codice");
    $check_sondaggio->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
    // altrimenti l'ho richiamato come azienda
} else if (!(empty($_SESSION["cf_azienda"]))) {
    $check_sondaggio = $pdo->prepare("SELECT Codice FROM Sondaggio WHERE CFAziendacreante = :cf_azienda AND Codice = :codice");
    $check_sondaggio->bindParam(':cf_azienda', $_SESSION['cf_azienda'], PDO::PARAM_STR);
}
$check_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
$check_sondaggio->execute();
$sondaggio = $check_sondaggio->fetch(PDO::FETCH_ASSOC);
$check_sondaggio->closeCursor();
if (!$sondaggio) {
    header("Location: index.php");
    exit;
}

// controllo per evitare che si cambi url e si faccia l'accesso ad una domanda di un altro utente premium/azienda, al massimo se cambio url per il get dell'ID della domanda posso mettere quella di una domanda chiusa da me (utente premium/azienda) gestita:
$aperta_chiusa = "CHIUSA";
if (!(empty($_SESSION["email"]))) {
    $check_domanda = $pdo->prepare("SELECT ID FROM Domanda WHERE EmailUtenteinserente = :email AND ID = :id AND ApertaChiusa = :aperta_chiusa");
    $check_domanda->bindParam(':email', $_SESSION['email'], PDO::PARAM_STR);
} else if (!(empty($_SESSION["cf_azienda"]))) {
    $check_domanda = $pdo->prepare("SELECT ID FROM Domanda WHERE CFAziendainserente = :cf_azienda AND ID = :id AND ApertaChiusa = :aperta_chiusa");
    $check_domanda->bindParam(':cf_azienda', $_SESSION['cf_azienda'], PDO::PARAM_STR);
}
$check_domanda->bindParam(':id', $id_domanda, PDO::PARAM_INT);
$check_domanda->bindParam(':aperta_chiusa', $aperta_chiusa, PDO::PARAM_STR);
$check_domanda->execute();
$domanda = $check_domanda->fetch(PDO::FETCH_ASSOC);
$check_domanda->closeCursor();
if (!$domanda) {
    header("Location: index.php");
    exit;
}

$mostra_opzioni_domanda = $pdo->prepare("CALL MostraOpzioni(:id_domanda)");
$mostra_opzioni_domanda->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
$mostra_opzioni_domanda->execute();
$opzioni_domanda = $mostra_opzioni_domanda->fetchAll(PDO::FETCH_ASSOC);
$mostra_opzioni_domanda->closeCursor();


$check_inviti = $pdo->prepare("SELECT * FROM Invito WHERE CodiceSondaggio = :codice_sondaggio");
$check_inviti->bindParam(':codice_sondaggio', $codice_sondaggio, PDO::PARAM_INT);
$check_inviti->execute();
$inviti = $check_inviti->fetchAll();
$check_inviti->closeCursor();

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
    <!--VISUALIZZA E RIMUOVI OPZIONI (se nessun utente e' stato ancora invitato)-->
    <div class="space">
        <h2>Opzioni</h2>
        <ul>
            <?php foreach ($opzioni_domanda as $opzione) { ?>
                <li>
                    <form action="script_php/rimuovi_opzione.php" method="POST">

                        <!--Etichetta per mostrare numero e nome dell'opzione-->
                        <label for="bottone">
                            <?php echo $opzione['Numeroprogressivo'] . ' ' . $opzione['Testo']; ?>
                        </label>

                        <?php
                        // se non ci sono utenti invitati mostra il bottone per eliminare, se ci sono mostra i bottoni solo se nessuno ha ancora accettato l'invito
                        $tutti_sospesi = true;

                        // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' rimuovere opzioni-->
                        if (($inviti && count($inviti) > 0)) {
                            foreach ($inviti as $invito) {
                                if ($invito['Esito'] == "ACCETTATO") {
                                    $tutti_sospesi = false;
                                    break;
                                }
                            }
                        } ?>
                        <?php

                        // se non ci sono invitati la variabile booleana non e' stata modificata quindi accedo,
                        // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso eliminare il sondaggio
                        if ($tutti_sospesi) {
                            ?>
                            <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                                value="<?php echo $codice_sondaggio ?>">
                            <input type="hidden" name="id_domanda" id="id_domanda" value="<?php echo $id_domanda ?>">
                            <input type="hidden" name="numero_progressivo" id="numero_progressivo"
                                value="<?php echo $opzione['Numeroprogressivo'] ?>">
                            <input type="submit" name="bottone" id="bottone" value="Elimina">
                        <?php } ?>
                    </form>
                </li>
            <?php } ?>
        </ul>
    </div>


    <!--AGGIUNGI UN'OPZIONE-->
    <div class="space">
        <h2>Aggiungi opzione</h2>
        <?php if ((isset($_GET['success'])) && ($_GET['success'] == 10)) {
            echo "Opzione inserita con successo";
        } else if ((isset($_GET['error'])) && ($_GET['error'] == 10)) {
            echo "Opzione gia' esistente";
        }
        ?>

        <?php
        // se non ci sono utenti invitati dai la possibilita' di inserire una nuova opzione;
        // se ci sono, continua a dare la possibilità solo se nessuno ha ancora accettato l'invito
        $tutti_sospesi_due = true;

        // se la query restituisce almeno una riga, vuol dire che ho invitato almeno un utente quindi non posso piu' aggiungere opzioni-->
        if (($inviti && count($inviti) > 0)) {
            foreach ($inviti as $invito) {
                if ($invito['Esito'] == "ACCETTATO") {
                    $tutti_sospesi_due = false;
                    break;
                }
            }
        } ?>
        <?php

        // se non ci sono invitati la variabile booleana non e' stata modificata quindi posso aggiungere un'opzione,
        // se tutti gli invitati sono con Esito='Sospeso' oppure con Esito='Rifiutato' ho eseguito i controlli ma la variabile booleana non e' stata modificata, quindi posso aggiungere un'opzione
        if ($tutti_sospesi_due) { ?>
            <form action="script_php/aggiungi_opzione.php" method="POST">
                <input type="text" name="testo_opzione" id="testo_opzione" required>
                <label for="testo_opzione">Testo</label>
                <input type="hidden" name="codice_sondaggio" id="codice_sondaggio" value="<?php echo $codice_sondaggio; ?>">
                <input type="hidden" name="id_domanda_chiusa" id="id_domanda_chiusa" value="<?php echo $id_domanda; ?>">
                <input type="submit" name="aggiungi_opzione" id="aggiungi_opzione" value="Aggiungi opzione">
            </form>
        <?php } else {
            echo "Un utente invitato ha accettato, per questa domanda non e' piu' possibile inserire opzioni";
        } ?>
    </div>

    <a href="gestisci_domanda.php?cod_sondaggio=<?php echo $codice_sondaggio ?>">Torna indietro</a>
    <a href="premium_home.php">Torna alla home</a>
</body>

</html>