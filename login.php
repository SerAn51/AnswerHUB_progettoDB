<?php
require 'config_connessione.php'; // instaura la connessione con il db

//con accesso fatto, se provo a cambiare url e mettere login.php il primo codice che esegue e' questo e mi reindirizza a index.php
if (!(empty($_SESSION["email"]))) {
    header("Location: index.php");
}

// se submit ha inviato i dati
if (isset($_POST["submit"])) {
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
            header("Location: index.php"); //reindirizza a index.php
        }
    } else {
        $messaggio = "Errore, non sei registrato/a";
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
                        <!--input box-->
                        <div class="inputbox">
                            <ion-icon name="mail-outline"></ion-icon>
                            <input type="email" name="email" id="email" required>
                            <label for="email">Email</label>
                        </div>
                        <!--input box-->
                        <div class="inputbox">
                            <ion-icon name="lock-closed-outline"></ion-icon>
                            <input type="password" name="password" id="password" required>
                            <label for="passowrd">Password</label>
                        </div>
                        <!--bottone accedi-->
                        <button class="submit" type="submit" name="submit" id="submit"> Accedi
                            <svg viewBox="0 0 16 16" class="bi bi-arrow-right" height="20" width="20" xmlns="http://www.w3.org/2000/svg" style="background-color: #00f2fe" fill="#0c2840">
                                <path d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" fill-rule="evenodd"></path>
                            </svg>
                        </button>
                        <!--
                        <input class="submit" type="submit" name="submit" id="submit" value="Accedi">
                        -->
                        <!--Vai alla registrazione-->
                        <div class="register">
                            <p>Non hai un account? <a href="registrazione.php">Registrati</a></p>
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