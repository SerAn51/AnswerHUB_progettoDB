<?php
require 'config_connessione.php'; // instaura la connessione con il db

//con accesso fatto, se provo a cambiare url e mettere registrazione.php il primo codice che esegue e' questo e mi reindirizza a index.php
if (!(empty($_SESSION["cf_azienda"]))) {
    header("Location: index.php");
}

function inserisciAzienda($pdo, $cf, $password, $email, $nome, $sede)
{
    $inserisci_azienda = $pdo->prepare("CALL InserisciAzienda(:param1, :param2, :param3, :param4, :param5)");
    $inserisci_azienda->bindParam(':param1', $cf, PDO::PARAM_STR);
    $inserisci_azienda->bindParam(':param2', $password, PDO::PARAM_STR);
    $inserisci_azienda->bindParam(':param3', $email, PDO::PARAM_STR);
    $inserisci_azienda->bindParam(':param4', $nome, PDO::PARAM_STR);
    $inserisci_azienda->bindParam(':param5', $sede, PDO::PARAM_STR);
    $inserisci_azienda->execute();
}

if (isset($_POST["submit"])) { // se submit avviene con successo
    // prendi tutti i dati del form e fai una query di inserimento nel db
    $cf = $_POST["cf_azienda"];
    $email = $_POST["email"];
    $nome = $_POST["nome"];
    $sede = $_POST["sede"];
    $password = $_POST["password"];
    $conferma_password = $_POST["conferma_password"];

    //inserisco i dati nel database
    if (isset($password) && isset($conferma_password)) {
        if ($password === $conferma_password) { //controllo che la password e la conferma siano uguali

            //controlla che l'azienda già non sia registrata
            $azienda = $pdo->prepare("SELECT * FROM Azienda WHERE CF = ?");
            $azienda->execute([$cf]);
            //estrazione della riga e inserimento in db
            //TODO: transazione
            $row = $azienda->fetch(PDO::FETCH_ASSOC);

            //se e' null l'azienda non esiste, quindi la inserisco
            if (!(isset($row['CF']))) { //isset() verifica se una variabile è stata impostata e se il suo valore non è NULL. True se la variabile è stata impostata e ha un valore diverso da NULL, false in caso contrario.
                inserisciAzienda($pdo, $cf, $password, $email, $nome, $sede);
                $messaggio = "Registrazione avvenuta con successo";
                $tipo_messaggio = "successo";

            } else {
                $messaggio = "Azienda gia' esistente";
                $tipo_messaggio = "errore";
            }
        } else {
            $messaggio = "Le password non corrispondono";
            $tipo_messaggio = "errore";
        }
    } else {
        $messaggio = "Errore";
        $tipo_messaggio = "errore";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Registrazione</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/registrazione_azienda.css">

</head>

<body>
    <section>
        <div class="login-box">
            <img src="images/logo.png" alt="Logo AnswerHUB" type="image/png" class="img-logo">
            <div class="form-box">
                <div class="form-value">
                    <form action="" method="POST">
                        <h2>Registrazione</h2>
                        <!--Eventuale messaggio-->
                        <div class="message <?php echo $tipo_messaggio; ?>"><?php echo isset($messaggio) ? $messaggio : ''; ?></div>
                        <!--input box CF Azienda-->
                        <div class="inputbox">
                            <ion-icon name="card-outline"></ion-icon>
                            <input type="text" name="cf_azienda" id="cf_azienda" maxlength="16" required>
                            <label for="cf_azienda">CF Azienda<label>
                        </div>
                        <!--input box Email-->
                        <div class="inputbox">
                            <ion-icon name="mail-outline"></ion-icon>
                            <input type="email" name="email" id="email" required>
                            <label for="email">Email</label>
                        </div>
                        <!--input box Nome azienda-->
                        <div class="inputbox">
                            <ion-icon name="pencil-outline"></ion-icon>
                            <input type="text" name="nome" id="nome" required>
                            <label for="nome">Nome azienda</label>
                        </div>
                        <!--input box Sede azienda-->
                        <div class="inputbox">
                            <ion-icon name="home-outline"></ion-icon>
                            <input type="text" name="sede" id="sede" required>
                            <label for="sede">Sede azienda</label>
                        </div>
                        <!--input box Password-->
                        <div class="inputbox">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                            <input type="password" name="password" id="password" required>
                            <label for="passowrd">Password</label>
                        </div>
                        <!--input box Conferma Password-->
                        <div class="inputbox">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                            <input type="password" name="conferma_password" id="conferma_password" required>
                            <label for="conferma_password">Conferma Password</label>
                        </div>
                        <!--bottone registrati-->
                        <button class="submit" type="submit" name="submit" id="submit"> Registrati
                            <svg viewBox="0 0 16 16" class="bi bi-arrow-right" height="20" width="20"
                                xmlns="http://www.w3.org/2000/svg" style="background-color: #00f2fe" fill="#0c2840">
                                <path
                                    d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"
                                    fill-rule="evenodd"></path>
                            </svg>
                        </button>
                        <!--Vai al login-->
                        <div class="access">
                            <p>L'azienda ha gia' un account? <a href="login.php">Accedi</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!--Le icone-->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>