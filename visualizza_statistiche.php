<!--VISUALIZZARE I PREMI DISPONIBILI-->
<div class="space" id="premi">
    <ul>
        <h1>Premi disponibili</h1>
        <?php
        //array con tutti i premi
        try {
            $prep_proc_premi = $pdo->prepare('CALL VisualizzaPremi()');
            $prep_proc_premi->execute();
            $premi = $prep_proc_premi->fetchAll(PDO::FETCH_ASSOC);
            $prep_proc_premi->closeCursor();
        } catch (PDOException $e) {
            echo "Errore Stored Procedure: " . $e->getMessage();
            header("Location: logout.php");
            exit;
        }
        ?>

        <div class="elenco_premi">
            <div class="wrapper_premi">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrizione</th>
                            <th>Foto</th>
                            <th>Punti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($premi as $premio) {
                            // leggi il contenuto del blob dal database
                            $blob = $premio["Foto"];

                            // decodifica il contenuto del blob in una stringa base64
                            $base64 = base64_encode($blob);

                            // determina il tipo di immagine dal contenuto del blob con la funzione getimagesizefromstring e prendendo il valore della chiave mime che dice il tipo dell'immagine
                            $image_info = getimagesizefromstring($blob);
                            $mime_type = $image_info["mime"];

                            // visualizza l'elememento di lista con l'immagine
                            ?>
                            <tr>
                                <td class="nome">
                                    <?php echo $premio["Nome"] ?>
                                </td>
                                <td class="descrizione">
                                    <?php echo $premio["Descrizione"]; ?>
                                </td>
                                <td class="foto">
                                    <img src="data:<?php echo $mime_type; ?>;base64,<?php echo $base64; ?>">
                                </td>
                                <td class="punti">
                                    <?php echo $premio["Puntinecessari"]; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </ul>
</div>

<!--VISUALIZZARE LA CLASSIFICA DEGLI UTENTI IN BASE AL CAMPO TOTALEBONUS-->
<div class="space" id="classifica">
    <ul>
        <h1>Classifica utenti</h1>
        <?php
        try {
            $prep_proc_classifica = $pdo->prepare('CALL VisualizzaClassifica()');
            $prep_proc_classifica->execute();
            $classifica_utenti = $prep_proc_classifica->fetchAll(PDO::FETCH_ASSOC);
            $prep_proc_classifica->closeCursor();
        } catch (PDOException $e) {
            echo "Errore Stored Procedure: " . $e->getMessage();
            header("Location: logout.php");
            exit;
        }
        ?>

        <div class="classifica_utenti">
            <div class="wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Utente</th>
                            <th>Punti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $posizione = 0;
                        foreach ($classifica_utenti as $classifica_utente) {
                            $posizione++;
                            ?>
                            <tr>
                                <td class="posizione">
                                    <?php echo $posizione ?>
                                </td>
                                <td class="utente">
                                    <?php echo $classifica_utente["Email"]; ?>
                                </td>
                                <td class="punti">
                                    <?php echo $classifica_utente["Totalebonus"]; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </ul>
</div>