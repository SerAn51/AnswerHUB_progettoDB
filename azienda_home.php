<?php
require 'config_connessione.php'; // instaura la connessione con il db
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Home</title>

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

        ul {
            list-style: none;
        }
    </style>


</head>

<body>
    <h1>Home azienda
        <?php echo $_SESSION['cf_azienda'] ?>
    </h1>

    <!--CREAZIONE DI UN NUOVO SONDAGGIO-->
    <!--
        - Per Stato metto di default APERTO...appena lo creo è aperto,
        - DataCreazione imposto in automatico oggi,
        - Per ParolachiaveDominio uso una radio con la lista dei domini e ne seleziono uno,
        - Per il creante sono una azienda quindi inserisco il cf di sessione e null per il EmailUtentecreante
        - Mostra un messaggio di errore se sto creando un sondaggio di cui gia' esiste il nome-->
    <?php
    $mostra_domini = $pdo->prepare("CALL MostraDomini()");
    $mostra_domini->execute();
    $domini = $mostra_domini->fetchAll(PDO::FETCH_ASSOC);
    $mostra_domini->closeCursor();

    ?>
    <div class="space">
        <form action="script_php/crea_sondaggio.php" method="POST">
            <h2>Crea sondaggio</h2>
            <?php if (isset($_GET['error']) && ($_GET['error'] == 10)) {
                echo "Errore, titolo gia' presente";
            } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                echo "Sondaggio creato con successo";
            }
            ?>
            <input type="Text" name="titolo" id="titolo" required>
            <label for="titolo">Titolo</label>
            <input type="number" min="1" name="max_utenti" id="max_utenti" required>
            <label for="max_utenti">Max utenti</label>
            <input type="date" name="data_chiusura" id="data_chiusura" required>
            <label for="data_chiusura">Data di chiusura</label>
            <?php foreach ($domini as $dominio): ?>
                <label>
                    <input type="radio" name="dominio" id="dominio" value="<?php echo $dominio['Parolachiave']; ?>">
                    <?php echo $dominio['Parolachiave']; ?>
                </label>
            <?php endforeach; ?>
            <input type="submit" name="crea" id="crea" value="Crea">
        </form>
    </div>

    <a href="logout.php">Effettua il logout</a>
</body>

</html>