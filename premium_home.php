<?php
require 'config_connessione.php'; // instaura la connessione con il db

//LOGIN: se vado su questa pagina senza accesso, reindirizza al login
if (!(empty($_SESSION["email"]))) {
    $email = $_SESSION["email"];
    // ora usa l'email passata tra una pagina e l'altra (salvato nella sessione) per fare una query sql
    $query_sql = "SELECT * FROM UtentePremium JOIN Utente ON UtentePremium.Email=Utente.Email WHERE UtentePremium.Email = ?";
    $stmt = $pdo->prepare($query_sql);
    $stmt->execute([$email]);
    //estrazione della riga cosi' da poter usare i dati
    $dati_utente = $stmt->fetch(PDO::FETCH_ASSOC);

    //se sono semplice/amministratore e cambio l'url per andare nella home dell'utente premium, rimango sulla home semplice/amministratore
    if ($dati_utente["PAS"] === "SEMPLICE") {
        header("Location: semplice_home.php");
    } else if ($dati_utente["PAS"] === "AMMINISTRATORE") {
        header("Location: amministratore_home.php");
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
    </head>

<body>
    <!--STATISTICHE (VISIBILI DA TUTTI GLI UTENTI)-->
    <!--inclue statistiche.php, si è optato per include in quanto le statistiche non sono fondamentali e se c'è un errore l'applicazione continua a funzionare, con require ci sarebbe un fatal error-->
    <?php
    include 'visualizza_statistiche.php';
    ?>

    <a href="logout.php">Logout</a>
</body>

</html>