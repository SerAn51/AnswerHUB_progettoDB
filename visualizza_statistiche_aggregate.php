<?php
require 'config_connessione.php'; // instaura la connessione con il db

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Statistiche aggregate</title>

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

    <?php if (isset($_POST["statistiche_aggregate"])) { ?>
        <?php $codice_sondaggio = $_POST['codice_sondaggio']; ?>

        <!--NUMERO DI RISPOSTE PER OGNI DOMANDA-->
        <div class="space">
            <h3>
                Numero di risposte per ogni domanda (quanti utenti hanno risposto?)
            </h3>
            <?php
            $mostra_domande_sondaggio = $pdo->prepare("CALL MostraDomande(:codice)");
            $mostra_domande_sondaggio->bindParam(':codice', $codice_sondaggio, PDO::PARAM_INT);
            $mostra_domande_sondaggio->execute();
            $domande_sondaggio = $mostra_domande_sondaggio->fetchAll(PDO::FETCH_ASSOC);
            $mostra_domande_sondaggio->closeCursor();
            ?>

            <!--Se il sondaggio non ha domande, mostri un messaggio-->
            <?php if (empty($domande_sondaggio)) {
                echo "Il sondaggio non contiene domande";
            } else { ?>
                <?php
                foreach ($domande_sondaggio as $domanda_sondaggio) {
                    if ($domanda_sondaggio['ApertaChiusa'] == "APERTA") {
                        //prendi ID e usalo in una query che ritorna le risposte
                        $conta_numero_risposte = $pdo->prepare("CALL ContaNumeroRisposteDomandaAperta(:id_domanda_aperta)");
                        $conta_numero_risposte->bindParam(':id_domanda_aperta', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                        $conta_numero_risposte->execute();
                        $numero_risposte = $conta_numero_risposte->fetch(PDO::FETCH_ASSOC);
                        $conta_numero_risposte->closeCursor();
                    } else {
                        $conta_numero_risposte = $pdo->prepare("CALL ContaNumeroRisposteDomandaChiusa(:id_domanda_chiusa)");
                        $conta_numero_risposte->bindParam(':id_domanda_chiusa', $domanda_sondaggio['ID'], PDO::PARAM_INT);
                        $conta_numero_risposte->execute();
                        $numero_risposte = $conta_numero_risposte->fetch(PDO::FETCH_ASSOC);
                        $conta_numero_risposte->closeCursor();
                    }
                    echo $domanda_sondaggio['Testo'] . ": " . $numero_risposte['NumeroRisposte'];
                }
                ?>
            <?php } ?>
        </div>

        <!--DISTRIBUZIONE DELLE RISPOSTE SULLE VARIE OPZIONI-->
        <div class="space">
            <h3>
                Distribuzione delle risposte sulle varie opzioni
            </h3>
            <!--idea: conto le opzioni selezionate e creo un array associativo che come chiave ha il numero progressivo dell'opzione e come valore ha il numero di occorrenze.
        A quel punto ne calcolo la percentuale-->
            
        </div>
    <?php } ?>
    <a href="premium_home.php">Torna alla home</a>
</body>

</html>