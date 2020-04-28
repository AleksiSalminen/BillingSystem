<html>
<head>
    <meta charset="utf-8"/>
    <title>Tiko HT - Urakkatarjouksen muodostus</title>
</head>

<body>
<?php

/*
*
* PHP-osio
* Super-globaalien muuttujien määrittely
*
*/

// Tietokantaan yhdistämiseen vaadittavat tiedot
$GLOBALS['y_tiedot'] = "host=() port=() dbname=() user=() password=()";

?>

    <p>--------------------------------------------------------</p>
    <h1>Urakkatarjouksen muodostus</h1>
    <?php 
        if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
            echo("<h2>Tietokantayhteyden luominen epäonnistui.</h2>");
            echo('<button onClick="window.location.reload();">Yritä uudelleen</button>');
        } else {
            // Eri vaiheissa ajetaan eri aliohjelmat
            switch ($_POST['vaihe']) {
                case null:
                    asiakasvalinta();
                    break;
                case 0:
                    tyokohdevalinta();
                    break;
                case 1:
                    tyovalinta();
                    break;
                case 2:
                    tarvikevalinta();
                    break;
                case 3:
                    tarjous();
                    break;
                case 4:
                    hyvaksyminen();
                    break;
            }
        }
    ?>
    <p>--------------------------------------------------------</p>
    <form method="post" action="../HT.html">
        <table>
            <tr>
            <td><input type="submit" name="etusivu" value="Etusivulle"/></td>
            </tr> 
        </table>
    </form>
    <p>--------------------------------------------------------</p>

</body>
</html>


<?php 
    function asiakasvalinta() {
        // Haetaan kaikkien asiakkaiden tiedot ja tulostetaan käyttäjälle valintalomake
        $asiakkaat_kysely = "SELECT * FROM asiakas;";
        $tulos = pg_query($asiakkaat_kysely);
        if ($tulos) {
            
            echo('<h2>Valitse asiakas:</h2>');

            echo('<form method="post" action="./Urakkatarjous-muodostus.php" id="asiakas" name="asiakas">');

            echo('<select id="asiakas-valinta" name="asiakas">');
            echo('<option value="default" selected disabled hidden>--Valitse--</option>');
            while ($rivi = pg_fetch_row($tulos)) {
                echo("<option value=$rivi[0]>$rivi[1]</option>");
            }
            echo('</select><br>');

            echo('<input type="hidden" name="vaihe" value="0">');
            echo('<input type="button" onclick="submitform()" value="Seuraava vaihe">');
            echo('</form>');

            // 'Seuraava vaihe' tarkistaa että asiakas on valittu
            echo('
                <script type="text/javascript">
                    function submitform() {
                        let asiakas = document.getElementById("asiakas-valinta");
                        let valinta = asiakas.options[asiakas.selectedIndex]
                        if (valinta.value != "default") {
                            document.asiakas.submit();
                        }
                    }
                </script>
            ');
        }
    }

    function tyokohdevalinta() {
        // Edellisestä aliohjelmasta asiakkaan id, jotka lisätään lomakkeeseen
        $asiakas_id = $_POST["asiakas"];

        // Haetaan valitun asiakkaan työkohteet ja tulostetaan käyttäjälle valintalomake
        $tyokohde_kysely = "SELECT * FROM tyokohde WHERE asiakas_id = $asiakas_id;";
        $tulos = pg_query($tyokohde_kysely);
        if ($tulos) {
            
            echo('<h2>Valitse työkohde:</h2>');

            echo('<form method="post" action="./Urakkatarjous-muodostus.php" id="tyokohde" name="tyokohde">');

            echo('<select id="tyokohde-valinta" name="tyokohde">');
            echo('<option value="default" selected disabled hidden>--Valitse--</option>');
            while ($rivi = pg_fetch_row($tulos)) {
                echo("<option value=$rivi[1]>$rivi[2]</option>");
            }
            echo('</select><br>');

            echo('<input type="hidden" name="vaihe" value="1">');
            echo("<input type='hidden' name='asiakas' value='$asiakas_id'>");
            echo('<input type="button" onclick="submitform()" value="Seuraava vaihe">');
            echo('</form>');

            // 'Seuraava vaihe' tarkistaa että työkohde on valittu
            echo('
                <script type="text/javascript">
                    function submitform() {
                        let tyokohde = document.getElementById("tyokohde-valinta");
                        let valinta = tyokohde.options[tyokohde.selectedIndex]
                        if (valinta.value != "default") {
                            document.tyokohde.submit();
                        }
                    }
                </script>
            ');
        }
    }

    function tyovalinta() {
        // Edellisten aliohjelmien tiedot, jotka lisätään lomakkeeseen
        $asiakas_id = $_POST["asiakas"];
        $tyokohde_id = $_POST["tyokohde"];


        /* 
         * Haetaan työtyypit ja tulostetaan käyttäjälle lomake, mistä voi valita eri työtyyppien 
         * tuntiarviot ja annetaanko työstä tarjousta
         */
        $tyotyyppi_kysely = "SELECT * FROM tyotyyppi;";
        $tulos = pg_query($tyotyyppi_kysely);
        if ($tulos) {
            echo('<h2>Lisää työtuntiarviot:</h2>');
            echo('
            <form method="post" action="./Urakkatarjous-muodostus.php">
                <table>
                    <thead>
                        <tr>
                            <th>Työtyyppi</th>
                            <th>Tuntimäärä</th>
                        </tr>
                    </thead>
                    <tbody>');
            while ($rivi = pg_fetch_row($tulos)) {
                echo("
                <tr>
                    <td>$rivi[1]</td>
                    <td><input type='number' name='tyo[$rivi[0]][0]' min='0' max='1000'></td>
                    <input type='hidden' name='tyo[$rivi[0]][1]' value='$rivi[2]'>
                    <td>($rivi[2]€/h)</td>
                </tr>
                ");

            }
            echo("
                    </tbody>
                </table>
                <label>Tarjousprosentti:</label>
                <input type='number' name='tarjous' min='0' max='100'>
                
                <input type='hidden' name='vaihe' value='2'>
                <input type='hidden' name='asiakas' value='$asiakas_id'>
                <input type='hidden' name='tyokohde' value='$tyokohde_id'>
                <br><br>
                <input type='submit' value='Seuraava vaihe'>
            </form>");

        }
    }

    function tarvikevalinta() {
        // Edellisten aliohjelmien tiedot, jotka lisätään lomakkeeseen
        $tyo_tiedot = "";
        $i = 0;
        foreach ($_POST["tyo"] as $tyotyyppi) {
            $tyo_tiedot .= "<input type='hidden' name='tyo[$i][0]' value='".$tyotyyppi[0]."'>";
            $tyo_tiedot .= "<input type='hidden' name='tyo[$i][1]' value='".$tyotyyppi[1]."'>";
            $i += 1;
        }
        $tyo_alennus = $_POST["tarjous"];
        $asiakas_id = $_POST["asiakas"];
        $tyokohde_id = $_POST["tyokohde"];


        /* 
         * Haetaan tarvikkeiden tiedot, ja luodaan käyttäjälle lomake mistä voi valita
         * eri tarvikkeita ja niiden määriä
         */
        $tarvike_kysely = "SELECT * FROM tarvike;";
        $tulos = pg_query($tarvike_kysely);
        if ($tulos) {
            echo('<h2>Lisää tarvikkeet:</h2>');
            // Luodaan taulukkorivi, josta käyttäjä voi valikoida tarvikkeen
            $tarvike_syote  = "
                <td>
                    <select id='asiakas-valinta' name='tarvike[indeksi][0]'>
                    <option value='default' selected disabled hidden>--Valitse--</option>";
            while ($rivi = pg_fetch_row($tulos)) {
                $yksikko = $rivi[3];
                if ($yksikko === 'metri') {
                    $yksikko = 'metriä';
                }
                $tarvike_syote .= "<option value='$rivi[0]'>$rivi[1], varastossa $rivi[4] $yksikkö</option>";
            }
            $tarvike_syote .= "
                    </select>
                </td>
                <td>
                    <input type='number' name='tarvike[indeksi][1]' min='0' max='1000'>
                </td>";

            // Ensimmäinen tarvike lisätään valmiiksi taulukkoon, ja sen indeksi asetetaan nollaksi
            $ensimmainen_tarvike = preg_replace("/indeksi/","0",$tarvike_syote);
            echo("
            <form method='post' action='./Urakkatarjous-muodostus.php' id='asiakas' name='asiakas'>
                <table>
                    <thead>
                        <tr>
                            <th>Tuote</th>
                            <th>Määrä</th>
                        </tr>
                    </thead>
                    <tbody id='tarvikkeet'>
                        <tr>
                            $ensimmainen_tarvike
                        </tr>
                    </tbody>
                </table>
                <input type='hidden' name='asiakas' value='$asiakas_id'>
                <input type='hidden' name='tyokohde' value='$tyokohde_id'>
                $tyo_tiedot
                <input type='hidden' name='tarjous' value='$tyo_alennus'>
                <input type='hidden' name='vaihe' value='3'>
                <input type='button' onclick='uusiTarvike()' value='Lisää tavike'>
                <input type='submit' value='Seuraava vaihe'>
            </form>
            ");

            // Muutetaan taulukkorivi JavaScript-ystävälliseksi.
            $tarvike_syote=preg_replace("/[\n\r]/","",$tarvike_syote);

            // 'Lisää tarvike' -nappia painamalla lomakkeeseen lisätään uusi taulukkorivi useiden tarvikkeiden valitsemiseen
            echo("<script>
                function uusiTarvike() {
                    let tarvikkeet = document.getElementById('tarvikkeet');
                    let tarvikeValinta = \"$tarvike_syote\";
                    tarvikeValinta = tarvikeValinta.replace(/indeksi/g, tarvikkeet.childElementCount);
                    console.log(tarvikeValinta);
                    tarvikeElementti = document.createElement('tr');
                    tarvikeElementti.innerHTML = tarvikeValinta;
                    tarvikkeet.appendChild(tarvikeElementti);
                }
            </script>");
        }
    }

    function tarjous() {
        echo('<h2>Tarkista tarjous:</h2>');
        $varasto_ok = True;
        $hinta_yht = 0;
        $alv_hinta = [0, 0]; // [10%, 24%]
        $tarvike_tiedot = ""; // lomakkeeseen
        $i = 0;
        $asiakas_id = $_POST["asiakas"];
        $tyokohde_id = $_POST["tyokohde"];
        
        // Asiakkaan ja työkohteen tietojen tulostaminen
        $asiakas_kysely = "SELECT * FROM asiakas WHERE asiakas_id = ".$asiakas_id.";";
        $tulos = pg_query($asiakas_kysely);
        if (!$tulos) {
            echo("Virhe.");
            return;
        }
        $rivi = pg_fetch_row($tulos);
        echo("<p>Asiakas: <br>");
        echo("$rivi[1] <br>");
        echo("$rivi[2] <br>");
        echo("$rivi[3] <br>");
        echo("$rivi[4] <br>");
        
        $tyokohde_kysely = "SELECT * FROM tyokohde WHERE tyokohde_id = ".$tyokohde_id.";";
        $tulos = pg_query($tyokohde_kysely);
        if (!$tulos) {
            echo("Virhe.");
            return;
        }
        $rivi = pg_fetch_row($tulos);
        echo("<br> Työkohde: <br>");
        echo("$rivi[2] </p>");

        // Valittujen tarvikkeiden tulostaminen
        echo("<h3>Tarvikkeet:</h3>");
        echo("
            <table>
                <thead>
                    <tr>
                        <th>Tuote</th>
                        <th>kpl</th>
                        <th>Hinta/kpl</th>
                        <th>ALV%</th>
                        <th>ALV€</th>
                        <th>Hinta</th>
                    </tr>
                </thead>
                <tbody>
        ");
        foreach ($_POST["tarvike"] as $tarvike) {
            if ($tarvike[0] !== NULL && $tarvike[1] !== "") {
                $tarvike_tiedot .= "<input type='hidden' name='tarvike[$i][0]' value='".$tarvike[0]."'>";
                $tarvike_tiedot .= "<input type='hidden' name='tarvike[$i][1]' value='".$tarvike[1]."'>";
                $i += 1;

                $kysely  = "SELECT * FROM tarvike ";
                $kysely .= "WHERE tarvike_id = $tarvike[0];";
                
                $tulos = pg_query($kysely);
                if ($tulos) {
                    $rivi = pg_fetch_row($tulos);
                    echo("<tr>");
                    echo("<td>$rivi[1]</td>");
                    echo("<td>$tarvike[1]</td>");
                    echo("<td>$rivi[5]€</td>");
                    echo("<td>$rivi[6]%</td>");
                    $hinta = $rivi[5] * $tarvike[1];
                    $alv_osuus = round($hinta * ($rivi[6] / 100), 2);
                    $hinta_yht += $hinta;
                    if ($rivi[6] === "10") {
                        $alv_hinta[0] += $alv_osuus;
                    } else if ($rivi[6] === "24") {
                        $alv_hinta[1] += $alv_osuus;
                    }
                    echo("<td>$alv_osuus"."€</td>");
                    echo("<td>$hinta"."€</td>");
                    if ($tarvike[1] > $rivi[4]) {
                        $varasto_ok = false;
                        echo("<td style='color: red'>Varastossa vain $rivi[4] kpl</td>");
                    }
                    echo("</tr>");
                } else {
                    echo("Virhe");
                    return;
                }
            }
        }

        echo("</tr></tbody></table>");

        // Työarvion tulostaminen
        echo("<h3>Työtuntiarvio:</h3>");
        echo("<p>");
        $tyo_hinta = 0;
        foreach ($_POST["tyo"] as $tyotyyppi) {
            if ($tyotyyppi[0] !== "") {
                $tyotyyppi_hinta = $tyotyyppi[0] * $tyotyyppi[1];
                $tyo_hinta += $tyotyyppi_hinta;
                switch ($tyotyyppi[1]) {
                    case 55:
                        echo("Suunnittelu");
                        break;
                    case 45:
                        echo("Työ");
                        break;
                    case 35:
                        echo("Aputyö");
                        break;
                }
                echo(" $tyotyyppi[0] tuntia, $tyotyyppi_hinta"."€<br>");
            }
        }
        $alennus = $tyo_hinta * ($_POST["tarjous"] / 100);
        echo("<br>Tarjous -".$_POST["tarjous"]."%, -$alennus"."€<br>");
        $tyo_alv = round(($tyo_hinta - $alennus) * 0.24, 2);
        $alv_hinta[1] += $tyo_alv;
        $hinta_yht += $tyo_hinta - $alennus;
        echo("Alv 24% $tyo_alv"."€<br>Yhteensä ".($tyo_hinta - $alennus)."€</p>");

        // ALV-erittelyn tulostaminen
        echo("<h3>Hinta:</h3> <table> <tbody>");
        if ($alv_hinta[0] !== 0) {
            echo("<tr><td>Alv 10%</td><td>$alv_hinta[0]"."€</td></tr>");    
        }
        if ($alv_hinta[1] !== 0) {
            echo("<tr><td>Alv 24%</td><td>$alv_hinta[1]"."€</td></tr>");    
        }
        echo("<tr><td>Hinta yht.</td><td>$hinta_yht"."€</td></tr>");
        echo("</tbody> </table>");


        if ($varasto_ok)
        {
            // Edellisten aliohjelmien tiedot, jotka lisätään lomakkeeseen
            $tyo_alennus = $_POST["tarjous"];
            echo("
                <form method='post' action='./Urakkatarjous-muodostus.php'>
                    <input type='hidden' name='asiakas' value='$asiakas_id'>
                    <input type='hidden' name='tyokohde' value='$tyokohde_id'>
                    $tarvike_tiedot
                    <input type='hidden' name='tyo' value='$tyo_hinta'>
                    <input type='hidden' name='tarjous' value='$tyo_alennus'>
                    <input type='hidden' name='vaihe' value='4'>
                    <input type='submit' value='Hyväksy tarjous ja luo sopimus'>
                </form>
            ");
        } else
        {
            echo("<p style='color: red'>Virhe: Tuotteita ei tarpeeksi varastossa.</p>");
        }
    }

    function hyvaksyminen() {
        // sopimuksen luominen
        $sopimus_id = "BEGIN; ";
        $sopimus_id .= "SELECT COUNT(*) FROM sopimus;";
        $sopimus_id_kysely = pg_query($sopimus_id);
        if ($sopimus_id_kysely) {
            $sopimus_id = pg_fetch_row($sopimus_id_kysely);
            $sopimus_id = $sopimus_id[0];
            $sopimus_id += 1;
            
            $sopimus_luonti  = "INSERT INTO sopimus VALUES (";
            $sopimus_luonti .= $sopimus_id.", ";
            $sopimus_luonti .= $_POST["tyokohde"].", ";
            $sopimus_luonti .= "0, false);";
            $sopimus_luonti = pg_query($sopimus_luonti);
            if (!$sopimus_luonti) {
                pg_query("ROLLBACK;");
                echo("Virhe");
                return;
            }
            $sopimus_id -= 1;
        } else {
            pg_query("ROLLBACK;");
            echo("Virhe");
            return;
        }

        // Sopimuksen tarvikkeiden tallentaminen
        $onnistui = true;
        if (isset($_POST["tarvike"])) {
            foreach ($_POST["tarvike"] as $tarvike) {
                $tarvike_paivitys  = "UPDATE tarvike ";
                $tarvike_paivitys .= "SET varastomaara = varastomaara - $tarvike[1] ";
                $tarvike_paivitys .= "WHERE tarvike_id = $tarvike[0];";

                $tarvike_lisays  = "INSERT INTO tarvikelista VALUES (";
                $tarvike_lisays .= "$sopimus_id, $tarvike[0], $tarvike[1], 0);";

                if (!pg_query($tarvike_paivitys) || !pg_query($tarvike_lisays)) {
                    $onnistui = false;
                }
                
            }
        }

        // Työsuorituksen tallentaminen
        $tyosuoritus_lisays  = "INSERT INTO tyosuoritus VALUES ";
        $tarjous = $_POST["tarjous"];
        if ($tarjous === "") {
            $tarjous = 0;
        }
        $tyosuoritus_lisays .= "($sopimus_id, 1, false, 0, ".$_POST["tyo"].", ".$tarjous.");";
        if (!pg_query($tyosuoritus_lisays)) {
            $onnistui = false;
        }

        pg_query("COMMIT;");
        if ($onnistui) {
            echo("Sopimuksen tallennus onnistui.");
        } else {
            echo("Tapahtui virhe!");
        }
    }
?>