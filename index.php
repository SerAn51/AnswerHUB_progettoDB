<?php
try {
    require 'config_connessione.php'; // instaura la connessione con il db

    //LOGIN
// se ho fatto accesso con email, sono un utente
    if (!(empty($_SESSION["email"]))) { //se la variabile di sessione email non e' vuota (esiste) fai cose, altrimenti reindirizza a login.php
        $email = $_SESSION["email"];
        // ora usa l'email passata tra una pagina e l'altra (salvato nella sessione) per fare una query sql
        $query_sql = "SELECT * FROM Utente WHERE email = ?";
        $stmt = $pdo->prepare($query_sql);
        $stmt->execute([$email]);
        //estrazione della riga cosi' da poter usare i dati
        $dati_utente = $stmt->fetch(PDO::FETCH_ASSOC);

        // Reindirizza alla pagina home dell'utente semplice/amministratore/premium
        if ($dati_utente["PAS"] === "SEMPLICE") {
            header("Location: semplice_home.php");
        } else if ($dati_utente["PAS"] === "AMMINISTRATORE") {
            header("Location: amministratore_home.php");
        } else {
            header("Location: premium_home.php");
        }
        // se ho fatto accesso con cf sono un'azienda
    } else if (!(empty($_SESSION["cf_azienda"]))) {
        header("Location: azienda_home.php");
    } else {
        header("Location: login.php"); // se sono in login e provo ad andare in index dall'url, mi rimanda al login
    }
} catch (PDOException $e) {
    echo "Errore Stored Procedure: " . $e->getMessage();
    header("Location: logout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Home</title>

    <link rel="icon" type="image/png" href="images/favicon.png">
</head>


<body>
</body>

</html>