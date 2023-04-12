<?php
require 'config_connessione.php'; // instaura la connessione con il db

if (isset($_POST["gestisci_opzioni"])) {
    $codice_sondaggio = $_POST['codice_sondaggio'];
    $id_domanda = $_POST['gestisci_opzioni'];

    $mostra_opzioni_domanda = $pdo->prepare("CALL MostraOpzioni(:id_domanda)");
$mostra_opzioni_domanda->bindParam(':id_domanda', $id_domanda, PDO::PARAM_INT);
$mostra_opzioni_domanda->execute();
$opzioni_domanda = $mostra_opzioni_domanda->fetchAll(PDO::FETCH_ASSOC);
$mostra_opzioni_domanda->closeCursor();
}
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
    <div class="space">
        <h2>Opzioni</h2>
        <ul>
            <?php foreach($opzioni_domanda as $opzione) {?>
            <li>
                <?php $opzione['Numeroprogressivo']?> <?php $opzione['Testo']?> 
            </li>
            <?php }?>
        </ul>
    </div>

    <!--TODO-->
    <div class="space">
        <h2>Aggiungi opzione</h2>
    </div>
</body>

</html>