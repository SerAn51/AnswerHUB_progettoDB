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

$mostra_domande_sondaggio = $pdo->prepare("CALL MostraDomande(:codice)");
$mostra_domande_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
$mostra_domande_sondaggio->execute();
$domande_sondaggio = $mostra_domande_sondaggio->fetchAll(PDO::FETCH_ASSOC);
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

    #inputbox_max_caratteri_domanda_aperta {
        display: none;
    }

    #checkbox_aperta:checked~#inputbox_max_caratteri_domanda_aperta {
        display: block;
        transition: .5s;
    }
    </style>
</head>

<body>

    <div class="space">
        <!--Mostra tutte le domande: lista di domande, le chiuse sono cliccabili e rimandano ad una pagina che mostra le opzioni e il form per inserire opzioni-->
        <h2>Domande</h2>
        <ul>
            <?php foreach($domande_sondaggio as $domanda) {?>
            <li>
                <?php if($domanda ["ApertaChiusa"] == "CHIUSA") {?>
                    <form action="gestisci_opzioni.php" method="POST">
                        <label><?php echo $domanda['Testo'];?></label>
                        <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                            value="<?php echo $codice_sondaggio; ?>">
                            <input type="hidden" name="id_domanda" id="id_domanda"
                            value="<?php echo $domanda['ID'];?>">
                            <input type="submit" name="gestisci_opzioni" id="gestisci_opzioni" value="gestisci_opzioni">
                    </form>
                <?php } else if($domanda ["ApertaChiusa"] == "APERTA"){?>
                <?php echo $domanda['Testo'];?>
                <?php }?>
            </li>
            <?php }?>
        </ul>
    </div>

    <div class="space">
        <!--Crea una nuova domanda-->
        <h2>Inserisci una nuova domanda</h2>
        <p>I campi con * sono obbligatori</p>
        <!--L'inserimento deve avvenire in Domanda, in ComponenteSondaggioDomanda e
        se APERTA anche in DomandaAperta, altrimenti in DomandaChiusa
        (in questo caso, si devono inserire le opzioni...vedi space "Domande"-->
        <form action="script_php/inserimento_domanda.php" method="POST" enctype="multipart/form-data">
            <?php if ((isset($_GET['error'])) && ($_GET['error'] == 10)) {
                echo "Tipo immagine non valido, supportati: PNG, JPEG";
            } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                echo "Domanda inserita con successo";
            }
            ?>
            <!--input box Testo-->
            <input type="text" name="testo" id="testo" required>
            <label for="testo">Testo domanda*<label>
                    <!--input box Foto-->
                    <input type="file" name="foto" id="foto">
                    <label for="foto">Foto</label>
                    <!--input box Punteggio-->
                    <input type="number" min="0" name="punteggio" id="punteggio">
                    <label for="punteggio">Punteggio</label>
                    <!--checkbox per inserire max caratteri risposta se la domanda e' APERTA, altrimenti si da per scontato sia chiusa-->
                    <label name="label_checkbox_aperta" id="label_checkbox_aperta" for="checkbox_aperta">Spunta se la
                        domanda e' aperta</label>
                    <input type="checkbox" name="checkbox_aperta" id="checkbox_aperta">
                    <!--input box Codice amministratore-->
                    <div name="inputbox_max_caratteri_domanda_aperta" id="inputbox_max_caratteri_domanda_aperta"
                        class="inputbox">
                        <!--TODO: gestire lato utente il max_caratteri-->
                        <input type="number" min="1" max="3000" name="max_caratteri_domanda_aperta"
                            id="max_caratteri_domanda_aperta">
                        <label for="max_caratteri_domanda_aperta">Numero massimo di caratteri</label>
                        <!--Mi trovo ad usare un form, invio anche il codice con post così da non dover gestire eventuali cambiamenti di url-->
                        <input type="hidden" name="codice_sondaggio" id="codice_sondaggio"
                            value="<?php echo $codice_sondaggio; ?>">
                    </div>
                    <!--bottone crea domanda-->
                    <input type="submit" name="crea" id="crea" value="Crea">
        </form>
    </div>

    <a href="premium_home.php">Torna alla home</a>
    <a href="logout.php">Effettua il logout</a>
</body>

</html>