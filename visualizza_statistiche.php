<!--VISUALIZZARE I PREMI DISPONIBILI-->
<div class="space">
    <h2>Premi disponibili</h2>
    <ul>
        <?php
        //array con tutti i premi vinti dall'utente di sessione
        $prep_proc_premi = $pdo->prepare('CALL VisualizzaPremi()');
        $prep_proc_premi->execute();
        $premi = $prep_proc_premi->fetchAll(PDO::FETCH_ASSOC);

        $prep_proc_premi->closeCursor();

        foreach ($premi as $premio) {
            // leggi il contenuto del blob dal database
            $blob = $premio["Foto"];

            // decodifica il contenuto del blob in una stringa base64
            $base64 = base64_encode($blob);

            // determina il tipo di immagine dal contenuto del blob con la funzione getimagesizefromstring e prendendo il valore della chiave mime che dice il tipo dell'immagine
            $image_info = getimagesizefromstring($blob);
            $mime_type = $image_info["mime"];

            // visualizza l'elememento di lista con l'immagine
            echo '<li><label name="premio_disponibile" value="' . $premio["Nome"] . '"';
            echo '>' . $premio["Nome"] . ' ' . $premio["Descrizione"] . ' <img width="50px" src="data:' . $mime_type . ';base64,' . $base64 . '"> ';
            echo $premio["Puntinecessari"] . ' ' . $premio["EmailUtenteAmministratore"] . '</li>';
        }
        ?>
    </ul>
</div>

<!--VISUALIZZARE LA CLASSIFICA DEGLI UTENTI IN BASE AL CAMPO TOTALEBONUS-->
<div class="space">
    <h2>Classifica utenti</h2>
    <ul>
        <?php
        //array con tutti i premi vinti dall'utente di sessione
        $prep_proc_classifica = $pdo->prepare('CALL VisualizzaClassifica()');
        $prep_proc_classifica->execute();
        $classifica_utenti = $prep_proc_classifica->fetchAll(PDO::FETCH_ASSOC);

        $prep_proc_classifica->closeCursor();

        foreach ($classifica_utenti as $classifica_utente) {
            echo '<li><label name="utente_in_classifica" value="' . $classifica_utente["Email"] . '"';
            echo '>' . $classifica_utente["Email"] . ' ' . $classifica_utente["Totalebonus"];
        }
        ?>
    </ul>
</div>