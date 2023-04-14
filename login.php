<?php
require 'config_connessione.php'; // instaura la connessione con il db

//con accesso fatto, se provo a cambiare url e mettere login.php il primo codice che esegue e' questo e mi reindirizza a index.php
if ((!(empty($_SESSION["email"]))) || (!(empty($_SESSION["cf_azienda"])))) {
    header("Location: index.php");
}


// se submit ha inviato i dati
if (isset($_POST["submit"])) {
    if (isset($_POST['checkbox_accesso_azienda']) && $_POST['checkbox_accesso_azienda'] == 'on') { // La checkbox è stata selezionata
        $cf_azienda = $_POST["cf_azienda"];
        $password = $_POST["password"];
        //usa una query per controllare se la email inserita esiste nel db (esiste l'utente)
        $query_sql = "SELECT * FROM Azienda WHERE CF = ?";
        $stmt = $pdo->prepare($query_sql);
        $stmt->execute([$cf_azienda]);
        //estrazione della riga e confronto i dati della riga con i dati inseriti dall'utente
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($row['CF'])) { //il CF esiste nel db
            if ($password != $row['Pwd']) { //se ha sbagliato password
                $messaggio = "Password sbagliata, riprova";
                $tipo_messaggio = "errore";
            } else { //se la password e' corretta
                $_SESSION["login"] = true;
                $_SESSION["cf_azienda"] = $row["CF"];
                $_SESSION["email"] = null;
                header("Location: index.php"); //reindirizza a index.php
            }
        } else {
            $messaggio = "Errore, azienda non registrata";
            $tipo_messaggio = "errore";
        }
    } else {
        $email = $_POST["email"];
        $password = $_POST["password"];
        //usa una query per controllare se la email inserita esiste nel db (esiste l'utente)
        $query_sql = "SELECT * FROM Utente WHERE Email = ?";
        $stmt = $pdo->prepare($query_sql);
        $stmt->execute([$email]);
        //estrazione della riga e confronto i dati della riga con i dati inseriti dall'utente
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (isset($row['Email'])) { //l'email esiste nel db
            if ($password != $row['Pwd']) { //se ha sbagliato password
                $messaggio = "Password sbagliata, riprova";
                $tipo_messaggio = "errore";
            } else { //se la password e' corretta
                $_SESSION["login"] = true;
                $_SESSION["email"] = $row["Email"];
                $_SESSION["cf_azienda"] = null;
                header("Location: index.php"); //reindirizza a index.php
            }
        } else {
            $messaggio = "Errore, non sei registrato/a";
            $tipo_messaggio = "errore";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnswerHUB | Login</title>

    <link rel="icon" type="image/png" href="images/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="stile_css/login.css">

</head>

<!--
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="" method="POST">
            <label for="email">Email</label>
            <input class="campo_inserimento" type="email" name="email" id="email" required>
            <label for="passowrd">Password</label>
            <input class="campo_inserimento" type="password" name="password" id="password" required>
            <input class="submit" type="submit" name="submit" id="submit" value="Accedi">
            <a href="registrazione.php">Registrati</a>
        </form>
    </div>
</body>
    -->

<body>
    <section>
        <div class="login-box">
            <img src="images/logo.png" alt="Logo AnswerHUB" type="image/png" class="img-logo">
            <div class="form-box">
                <div class="form-value">
                    <form action="" method="POST">
                        <h2>Login</h2>
                        <!--Eventuale messaggio-->
                        <div class="message <?php echo $tipo_messaggio; ?>"><?php echo isset($messaggio) ? $messaggio : ''; ?></div>
                        <!--checkbox per accedere come azienda-->
                        <label name="label_checkbox_accesso_azienda" id="label_checkbox_accesso_azienda"
                            for="checkbox_accesso_azienda">Sei un'azienda?</label>
                        <input type="checkbox" name="checkbox_accesso_azienda" id="checkbox_accesso_azienda">
                        <!--input box email utente-->
                        <div class="inputbox" name="inputbox_email_utente" id="inputbox_email_utente">
                            <ion-icon name="mail-outline"></ion-icon>
                            <input type="email" name="email" id="email">
                            <label for="email">Email</label>
                        </div>
                        <!--input box accesso azienda TODO: controllare struttura cf-->
                        <div name="inputbox_accesso_azienda" id="inputbox_accesso_azienda" class="inputbox">
                            <ion-icon name="card-outline"></ion-icon>
                            <input type="text" name="cf_azienda" id="cf_azienda">
                            <label for="accesso_azienda">CF Azienda</label>
                        </div>
                        <!--input box password-->
                        <div class="inputbox">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                            <input type="password" name="password" id="password" required>
                            <label for="passowrd">Password</label>
                        </div>
                        <!--bottone accedi-->
                        <button class="submit" type="submit" name="submit" id="submit"> Accedi
                            <svg viewBox="0 0 16 16" class="bi bi-arrow-right" height="20" width="20"
                                xmlns="http://www.w3.org/2000/svg" style="background-color: #00f2fe" fill="#0c2840">
                                <path
                                    d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"
                                    fill-rule="evenodd"></path>
                            </svg>
                        </button>
                        <!--Vai alla registrazione per un nuovo utente-->
                        <div class="register" name="registrazione_utente" id="registrazione_utente">
                            <p>Non hai un account? <a href="registrazione_utente.php">Registrati</a></p>
                        </div>
                        <!--Vai alla registrazione per una nuova azienda-->
                        <div class="register" name="registrazione_azienda" id="registrazione_azienda">
                            <p>L'azienda non ha un account? <a href="registrazione_azienda.php">Registrala</a></p>
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