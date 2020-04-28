<html>
<head>
    <meta charset="utf-8"/>
    <title>Tiko HT - Tuntitöiden ja tarvikkeiden tallennus</title>
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

    <!--
    ---- 
    ---- HTML-osio
    ---- Otsikko
    ----
    -->

    <p>--------------------------------------------------------</p>
    <h1>Tuntitöiden ja tarvikkeiden tallennus</h1>


<?php 

/*
*
* PHP-osio
* Työkohteen valinta -lomakkeen osan luonti
*
*/

if($_POST['tyokohde_valinta']) {
    $_POST['sopimus_id'] = null;
}

echo('<p>--------------------------------------------------------</p>');
echo('<form method="post" action="Tuntityot-tarvikkeet-tallennus.php">');
echo("<h2><b>Työkohde:</b></h2>");

if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
    die("Tietokantayhteyden luominen epaonnistui.");
}

echo("<select name='tyokohde_id'>");

$tyokohteet_kysely = "SELECT * FROM tyokohde;";
$tulos = pg_query($tyokohteet_kysely);
if ($tulos) {
    while ($rivi = pg_fetch_row($tulos)) {
        if($rivi[1] === $_POST['tyokohde_id']) {
            echo("<option value='$rivi[1]' selected>$rivi[2]</option>");
        }
        else {
            echo("<option value='$rivi[1]'>$rivi[2]</option>");
        }
    }
}

pg_close($yhteys);

echo("</select><br/><br/>");

echo('<input type="submit" name="tyokohde_valinta" value="Näytä kohteen (tuntityö)sopimukset"/>');

?>

<?php 

/*
*
* PHP-osio
* Sopimuksen valinta -lomakkeen osan luonti
*
*/

$tyokohde_id = $_POST['tyokohde_id'];

if($tyokohde_id !== null) {
    echo('<p>--------------------------------------------------------</p>');
    echo("<h2><b>Sopimus:</b></h2>");

    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    echo("<select name='sopimus_id'>");

    $sopimukset_kysely = "SELECT * FROM sopimus WHERE tyokohde_id = $tyokohde_id AND on_tunti = true;";
    $tulos = pg_query($sopimukset_kysely);
    if ($tulos) {
        while ($rivi = pg_fetch_row($tulos)) {
            if($rivi[0] === $_POST['sopimus_id']) {
                echo("<option value='$rivi[0]' selected>Sopimus: ID = $rivi[0]</option>");
            }
            else {
                echo("<option value='$rivi[0]'>Sopimus: ID = $rivi[0]</option>");
            }
        }
    }

    echo("</select><br/><br/>");

    pg_close($yhteys);

    echo('<input type="submit" name="sopimus_valinta" value="Päivitä sopimuksen tarvikkeiden ja tuntitöiden tiedot"/>');
}

?>

<?php 

/*
*
* PHP-osio
* Sopimuksen tietojen päivitys -lomakkeen luonti
*
*/

$tyokohde_id = $_POST['tyokohde_id'];
$sopimus_id = $_POST['sopimus_id'];

if($tyokohde_id !== null && $sopimus_id !== null) {
    echo('<p>--------------------------------------------------------</p>');
    echo("<h2>Sopimuksen tietojen päivitys:</h2>");
    echo("_________________________<br><br>");

    echo("<h3>Käytettyjen tarvikkeiden tietojen päivitys:</h3>");
    lisaa_tarvike();
    paivita_tarvikkeet();

    echo("<h3>Tuntitöiden päivitys:</h3>");
    lisaa_tyotyyppi();
    lisaa_tyosuoritus();
    paivita_tuntityot();
}


/*
* --------- Tarvike-metodit ---------
*/


/*
* Metodi uuden tarvikkeen lisäämiselle
*/
function lisaa_tarvike() {
    echo("_________________________<br><br>");
    echo("<details>");

    if($_POST['tarvike_nimi'] !== null) {
        tallenna_tarvike();
    }

    echo("<summary><b>Lisää uusi tarvike:</b><br/><br/></summary>");

    echo('<b>Nimi:</b> ');
    echo('<input type="text" name="tarvike_nimi" value=""/><br/><br/>');

    echo('<b>Yksikkö:</b> ');
    echo('<input type="text" name="tarvike_yksikko" value=""/><br/><br/>');

    echo('<b>Varastomäärä:</b> ');
    echo('<input type="text" name="tarvike_varastomaara" value=""/><br/><br/>');

    echo('<b>Sisäänostohinta:</b> ');
    echo('<input type="text" name="tarvike_sisaanostohinta" value=""/><br/><br/>');

    echo('<b>Myyntihinta:</b> ');
    echo('<input type="text" name="tarvike_myyntihinta" value=""/><br/><br/>');

    echo('<b>Arvonlisävero:</b> ');
    echo('<input type="text" name="tarvike_arvonlisavero" value=""/><br/><br/>');

    echo('<b>Alennusprosentti:</b> ');
    echo('<input type="text" name="tarvike_alennusprosentti" value=""/><br/><br/>');

    echo('<input type="submit" name="tarvike_lisays" value="Lisää"/><br/>');

    echo("</details>");
    echo("_________________________<br/><br/>");
}


/*
* Metodi uuden tarvikkeen tallentamiselle tietokantaan
*/
function tallenna_tarvike() {
    define("LISÄYS_ONNISTUMIS_ILMOITUS", "<b>TARVIKKEEN LISÄYS ONNISTUI</b><br/><br/>");
    define("LISÄYS_VIRHE_ILMOITUS", "<b>TARVIKKEEN LISÄYS EPÄONNISTUI</b><br/><br/>");

    $sopimus_id = $_POST['sopimus_id'];
    $tarvike_nimi = pg_escape_string($_POST['tarvike_nimi']);
    $tarvike_yksikko = pg_escape_string($_POST['tarvike_yksikko']);
    $tarvike_varastomaara = intval($_POST['tarvike_varastomaara']);
    $tarvike_sisaanostohinta = intval($_POST['tarvike_sisaanostohinta']);
    $tarvike_myyntihinta = intval($_POST['tarvike_myyntihinta']);
    $tarvike_arvonlisavero = intval($_POST['tarvike_arvonlisavero']);
    $tarvike_alennusprosentti = intval($_POST['tarvike_alennusprosentti']);

    if(!($sopimus_id === 0 || $tarvike_nimi === '' || $tarvike_yksikko === '')) {
        if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
            die("Tietokantayhteyden luominen epaonnistui.");
        }
    
        $tarvike_lkm_kysely = "SELECT COUNT(*) AS lkm FROM tarvike;";
        $lkm_tulos = pg_query($tarvike_lkm_kysely);
        if(!$rivi = pg_fetch_row($lkm_tulos)) {
            echo(LISÄYS_VIRHE_ILMOITUS);
            echo("Tietokantavirhe: ID<br/><br/>");
            exit;
        }
        $lkm = $rivi[0];
    
        $tarvike_id = $lkm + 1;
        
        $tarvike_lisays_kysely = "INSERT INTO tarvike VALUES ($tarvike_id, '$tarvike_nimi', $tarvike_sisaanostohinta, '$tarvike_yksikko', $tarvike_varastomaara, $tarvike_myyntihinta, $tarvike_arvonlisavero); INSERT INTO tarvikelista VALUES ($sopimus_id, $tarvike_id, 0, $tarvike_alennusprosentti);";
        $tarvike_lisays_tulos = pg_query($tarvike_lisays_kysely);
        if(!$tarvike_lisays_tulos) {
            echo(LISÄYS_VIRHE_ILMOITUS);
            echo("Tietokantavirhe: tarvike<br/><br/>");
            exit;
        }
    
        pg_close($yhteys);
    
        echo(LISÄYS_ONNISTUMIS_ILMOITUS);
    }

}


/*
* Metodi tarvikkeiden päivittämiselle
*/
function paivita_tarvikkeet() {
    echo("<p><b>Päivitä käytettyjen tarvikkeiden tiedot:</b></p>");

    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $tarvikkeet_kysely = "SELECT tarvike_id, nimi, yksikko FROM tarvike;";
    $tulos = pg_query($tarvikkeet_kysely);

    if ($tulos) {
        while ($rivi = pg_fetch_row($tulos)) {
            echo("<b>$rivi[1]:</b> <input type='number' name='maara$rivi[0]' value='0' min='0' max='9999'/> $rivi[2]");
            
            $kaytetty_maara = $_POST["maara$rivi[0]"];
            if($kaytetty_maara !== null && $kaytetty_maara > 0) {
                paivita_tarvike($_POST['sopimus_id'], $rivi[0], $kaytetty_maara);
            }

            echo "<br/><br/>\n";
        }
    }

    pg_close($yhteys);

    echo('<input type="submit" name="tarvikkeet_paivitys" value="Päivitä"/><br/>');
    echo("_________________________<br/><br/>");
}


/*
* Metodi tarvikkeen päivittämiselle
*/
function paivita_tarvike($sopimus_id, $tarvike_id, $kaytetty_maara) {
    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $tarvike_maara_kysely = "SELECT maara, varastomaara FROM tarvike JOIN (tarvikelista JOIN sopimus ON tarvikelista.sopimus_id = sopimus.sopimus_id) ON tarvike.tarvike_id = tarvikelista.tarvike_id WHERE sopimus.sopimus_id = $sopimus_id AND tarvike.tarvike_id = $tarvike_id;";
    $tulos = pg_query($tarvike_maara_kysely);

    if(!$tulos || pg_num_rows($tulos) === 0) {
        $tarvikelista_lisays_kysely = "INSERT INTO tarvikelista VALUES ($sopimus_id, $tarvike_id, 0, 0);";
        $tarvikelista_lisays_tulos = pg_query($tarvikelista_lisays_kysely);
        
        if($tarvikelista_lisays_tulos) {
            paivita_tarvike($sopimus_id, $tarvike_id, $kaytetty_maara);
        }
        else {
            echo("<em> !~! Päivitys epäonnistui</em>");
        }
    }
    else {
        $maara = pg_fetch_result($tulos, 0);
        $varastomaara = pg_fetch_result($tulos, 1);
        
        $uusi_maara = $maara + $kaytetty_maara;
        $uusi_varastomaara = $varastomaara - $kaytetty_maara;

        $tarvike_paivitys_kysely = "UPDATE tarvike SET varastomaara = $uusi_varastomaara WHERE tarvike.tarvike_id = $tarvike_id; UPDATE tarvikelista SET maara = $uusi_maara WHERE tarvikelista.sopimus_id = $sopimus_id AND tarvikelista.tarvike_id = $tarvike_id;";
        $tarvike_paivitys_tulos = pg_query($tarvike_paivitys_kysely);
        
        if(!$tarvike_paivitys_tulos) {
            echo("<em> !~! Päivitys epäonnistui</em>");
        }
        else {
            echo("<em> ~ Päivitetty</em>");
        }
    }

    pg_close($yhteys);
}


/*
* --------- Työtyyppi/työsuoritus-metodit ---------
*/


/*
* Metodi työtyypin lisäämiselle
*/
function lisaa_tyotyyppi() {
    echo("_________________________<br><br>");

    echo("<details>");

    if($_POST['tyotyyppi_lisays'] !== null) {
        tallenna_tyotyyppi();
    }

    echo("<summary><b>Lisää uusi työtyyppi:</b><br/><br/></summary>");

    echo('<b>Tyyppinimi:</b> ');
    echo('<input type="text" name="tyotyyppi_nimi" value=""/><br/><br/>');

    echo('<b>Tuntihinta:</b> ');
    echo('<input type="number" name="tyotyyppi_tuntihinta" value="0" min="0" max="999999"/><br/><br/>');
    
    echo('<b>Kotitalousvähennettävä:</b> ');
    echo('<input type="checkbox" name="tyotyyppi_kotitalousvahennettava" value="kotitalousvahennettava"/><br/><br/>');

    echo('<input type="submit" name="tyotyyppi_lisays" value="Lisää"/><br/>');
    echo("</details>");
}


/*
* Metodi uuden työtyypin tallentamiselle tietokantaan
*/
function tallenna_tyotyyppi() {
    $lisays_onnistumis_ilmoitus = "<b>TYÖTYYPIN LISÄYS ONNISTUI</b><br/><br/>";
    $lisays_virhe_ilmoitus = "<b>TYÖTYYPIN LISÄYS EPÄONNISTUI</b><br/><br/>";

    $tyotyyppi_nimi = pg_escape_string($_POST['tyotyyppi_nimi']);
    $tyotyyppi_tuntihinta = intval($_POST['tyotyyppi_tuntihinta']);
    $tyotyyppi_kotitalousvahennettava = "true";
    if($_POST['tyotyyppi_kotitalousvahennettava'] === null) {
        $tyotyyppi_kotitalousvahennettava = "false";
    }
    
    if($tyotyyppi_nimi !== '' && $tyotyyppi_tuntihinta >= 0) {
        if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
            die("Tietokantayhteyden luominen epaonnistui.");
        }
    
        $tyotyyppi_lkm_kysely = "SELECT COUNT(*) AS lkm FROM tyotyyppi;";
        $lkm_tulos = pg_query($tyotyyppi_lkm_kysely);
        if(!$rivi = pg_fetch_row($lkm_tulos)) {
            echo($lisays_virhe_ilmoitus);
            echo("Tietokantavirhe: ID<br/><br/>");
            exit;
        }
        $lkm = $rivi[0];
    
        $tyotyyppi_id = $lkm + 1;

        $tyotyyppi_lisays_kysely = "INSERT INTO tyotyyppi VALUES ($tyotyyppi_id, '$tyotyyppi_nimi', $tyotyyppi_tuntihinta, $tyotyyppi_kotitalousvahennettava);";
        $tyotyyppi_lisays_tulos = pg_query($tyotyyppi_lisays_kysely);
        if(!$tyotyyppi_lisays_tulos) {
            echo($lisays_virhe_ilmoitus);
            echo("Tietokantavirhe: tyotyyppi<br/><br/>");
            exit;
        }
    
        pg_close($yhteys);
    
        echo($lisays_onnistumis_ilmoitus);
    }
}


/*
* Metodi työsuorituksen lisäämiselle
*/
function lisaa_tyosuoritus() {
    echo("_________________________<br><br>");
    echo("<details>");

    if($_POST['tyosuoritus_lisays'] !== null) {
        tallenna_tyosuoritus();
    }

    echo('<summary><b>Lisää uusi työsuoritus:</b><br/><br/></summary>');

    echo('<b>Työtyyppi:</b><br/><br/>');

    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $sopimus_id = intval($_POST['sopimus_id']);

    $tyotyypit_kysely = "SELECT tyotyyppi_id, tyyppinimi FROM tyotyyppi WHERE tyotyyppi_id NOT IN ( SELECT tyotyyppi_id FROM tyosuoritus WHERE sopimus_id = $sopimus_id );";
    $tulos = pg_query($tyotyypit_kysely);
    if ($tulos) {
        while ($rivi = pg_fetch_row($tulos)) {
            echo("<input type='radio' name='tyosuoritus_tyotyyppi_id' value='$rivi[0]'>$rivi[1]<br>");
            echo "<br/>\n";
        }
    }

    pg_close($yhteys);

    echo('<b>Alennusprosentti:</b> ');
    echo('<input type="number" name="tyosuoritus_alennusprosentti" value="0" min="0" max="100"/><br/><br/>');

    echo('<input type="submit" name="tyosuoritus_lisays" value="Lisää"/>');

    echo("</details>");
    echo("_________________________<br/><br/>");
}


/*
* Metodi työsuorituksen tallentamiselle tietokantaan
*/
function tallenna_tyosuoritus() {
    $lisays_onnistumis_ilmoitus = "<b>TYÖSUORITUKSEN LISÄYS ONNISTUI</b><br/><br/>";
    $lisays_virhe_ilmoitus = "<b>TYÖSUORITUKSEN LISÄYS EPÄONNISTUI</b><br/><br/>";

    $sopimus_id = intval($_POST['sopimus_id']);
    $tyosuoritus_tyotyyppi = intval($_POST['tyosuoritus_tyotyyppi_id']);
    $tyosuoritus_alennusprosentti = intval($_POST['tyosuoritus_alennusprosentti']);
    
    if($sopimus_id !== 0 && $tyosuoritus_tyotyyppi !== 0 && $tyosuoritus_alennusprosentti >= 0) {
        if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
            die("Tietokantayhteyden luominen epaonnistui.");
        }
    
        $tyosuoritus_lisays_kysely = "INSERT INTO tyosuoritus VALUES ($sopimus_id, $tyosuoritus_tyotyyppi, true, 0, 0, $tyosuoritus_alennusprosentti);";
        $tyosuoritus_lisays_tulos = pg_query($tyosuoritus_lisays_kysely);
        if(!$tyosuoritus_lisays_tulos) {
            echo($lisays_virhe_ilmoitus);
            echo("Tietokantavirhe: tyosuoritus<br/><br/>");
            exit;
        }
    
        pg_close($yhteys);
    
        echo($lisays_onnistumis_ilmoitus);
    }
}


/*
* Metodi tuntitöiden päivittämiselle
*/
function paivita_tuntityot() {
    echo("<p><b>Päivitä tuntitöiden tiedot:</b></p>");

    $sopimus_id = intval($_POST['sopimus_id']);

    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $tyosuoritukset_kysely = "SELECT tt.tyotyyppi_id, tt.tyyppinimi FROM tyosuoritus AS ts JOIN tyotyyppi AS tt ON ts.tyotyyppi_id = tt.tyotyyppi_id WHERE ts.sopimus_id = $sopimus_id;";
    $tulos = pg_query($tyosuoritukset_kysely);

    if ($tulos) {
        while ($rivi = pg_fetch_row($tulos)) {
            echo("<b>$rivi[1]:</b> <input type='number' name='tunnit$rivi[0]' value='0' min='0' max='24'/> tuntia");
            
            $tunnit = $_POST["tunnit$rivi[0]"];
            if($tunnit !== null && $tunnit > 0) {
                paivita_tuntityosuoritus($sopimus_id, $rivi[0], $tunnit);
            }

            echo "<br/><br/>\n";
        }
    }

    pg_close($yhteys);

    echo('<input type="submit" name="tuntityot_paivitys" value="Päivitä"/><br/><br/>');
}


/*
* Metodi tuntityösuorituksen päivittämiselle
*/
function paivita_tuntityosuoritus($sopimus_id, $tyotyyppi_id, $tunnit) {
    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $tyosuoritus_tunnit_kysely = "SELECT tuntimaara FROM tyosuoritus WHERE sopimus_id = $sopimus_id AND tyotyyppi_id = $tyotyyppi_id;";
    $tyosuoritus_tunnit_tulos = pg_query($tyosuoritus_tunnit_kysely);
    $tuntimaara = pg_fetch_result($tyosuoritus_tunnit_tulos, 0);

    $uusi_tuntimaara = $tuntimaara + $tunnit;

    $tyosuoritus_paivitys_kysely = "UPDATE tyosuoritus SET tuntimaara = $uusi_tuntimaara WHERE sopimus_id = $sopimus_id AND tyotyyppi_id = $tyotyyppi_id;";
    $tyosuoritus_paivitys_tulos = pg_query($tyosuoritus_paivitys_kysely);
    
    if(!$tyosuoritus_paivitys_tulos) {
        echo("<em> !~! Päivitys epäonnistui</em>");
    }
    else {
        echo("<em> ~ Päivitetty</em>");
    }

    pg_close($yhteys);
}


echo('</form>');

?>

    <!--
    ---- 
    ---- HTML-osio
    ---- Painike, jolla päästään etusivulle
    ----
    -->


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