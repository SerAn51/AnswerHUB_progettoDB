<?php
require 'config_connessione.php'; // instaura la connessione con il db

//LOGIN: se vado su questa pagina senza accesso, reindirizza al login
if (!(empty($_SESSION["email"]))) {
    $email = $_SESSION["email"];
    // ora usa l'email passata tra una pagina e l'altra (salvato nella sessione) per fare una query sql
    $query_sql = "SELECT * FROM UtenteAmministratore JOIN Utente ON UtenteAmministratore.Email=Utente.Email WHERE UtenteAmministratore.Email = ?";
    $stmt = $pdo->prepare($query_sql);
    $stmt->execute([$email]);
    //estrazione della riga cosi' da poter usare i dati
    $dati_utente = $stmt->fetch(PDO::FETCH_ASSOC);

    //se sono semplice/premium e cambio l'url per andare nella home dell'utente amministratore, rimango sulla home semplice/premium
    if (isset($dati_utente['PAS'])) {
        if ($dati_utente["PAS"] === "SEMPLICE") {
            header("Location: semplice_home.php");
        } else if ($dati_utente["PAS"] === "PREMIUM") {
            header("Location: premium_home.php");
        }
    }
} else {
    header("Location: login.php"); //inoltre, cosi' che se provo ad andare in index dall'url, mi rimanda al login
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

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
        </style>
    </head>

<body>
    <h1>Ciao <?php echo $dati_utente["Nome"]; ?></h1>

    <!--INSERIMENTO DI UN NUOVO PREMIO: due input, uno per chiedere l'altro di conferma, fa una insert in Premio-->
    <div class="space">
        <h2>Inserisci un nuovo premio</h2>
        <form action="script_php/inserimento_premio.php" method="POST" enctype="multipart/form-data">
            <?php if (isset($_GET['error'])) {
                if ($_GET['error'] == 10) {
                    echo "Formato non corretto, accettati: PNG, JPEG";
                } else if ($_GET['error'] == 11) {
                    echo "Non hai inserito la foto";
                }
            } else if (isset($_GET['success']) && $_GET['success'] == 10) {
                echo "Premio inserito con successo";
            }
            ?>
            <input type="text" name="nome" id="nome" required>
            <label for="nome">Nome</label>
            <input type="text" name="descrizione" id="descrizione" required>
            <label for="descrizione">Descrizione</label>
            <input type="file" name="foto" id="foto">
            <label for="foto">Foto</label>
            <input type="number" min="0" name="punti_necessari" id="punti_necessari" required>
            <label for="punti_necessari">Punti necessari</label>
            <input type="submit" name="inserisci" id="inserisci" value="Inserisci">
        </form>
    </div>

    <!--INSERIMENTO DI UN NUOVO DOMINIO-->
    <div class="space">
        <h2>Inserisci un nuovo dominio</h2>
        <form action="script_php/inserimento_dominio.php" method="POST">
            <?php if (isset($_GET['error']) && ($_GET['error'] == 20)) {
                echo "Errore, riprova";
            } else if (isset($_GET['success']) && $_GET['success'] == 20) {
                echo "Dominio inserito con successo";
            }
            ?>
            <input type="text" name="parola_chiave" id="parola_chiave" required>
            <label for="parola_chiave">Parola Chiave</label>
            <input type="text" name="descrizione" id="descrizione" required>
            <label for="descrizione">Descrizione</label>
            <input type="submit" name="inserisci" id="inserisci" value="Inserisci">
        </form>
    </div>

    <!--TODO:
    - Gestire meglio i messaggi
    - Gestire assegnazione premi (i due FIXME nel codice MySQL)
    -->

    <!--STATISTICHE (VISIBILI DA TUTTI GLI UTENTI)-->
    <!--inclue statistiche.php, si è optato per include in quanto le statistiche non sono fondamentali e se c'è un errore l'applicazione continua a funzionare, con require ci sarebbe un fatal error-->
    <?php
    include 'visualizza_statistiche.php';
    ?>

    <a href="logout.php">Logout</a>
</body>

</html>