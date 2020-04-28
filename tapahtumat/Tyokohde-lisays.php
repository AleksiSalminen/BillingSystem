<html>
<head>
    <meta charset="utf-8"/>
    <title>Tiko HT - Työkohteen lisäys</title>
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
    <h1>Työkohteen lisäys</h1>
    <p>--------------------------------------------------------</p>


<?php

/*
*
* PHP-osio
* Työkohteen lisäys syötettyjen tietojen perusteella
*
*/

define("ONNISTUMIS_ILMOITUS", "<b>TYÖKOHTEEN LISÄYS ONNISTUI</b><br/><br/>");
define("VIRHE_ILMOITUS", "<b>TYÖKOHTEEN LISÄYS EPÄONNISTUI</b><br/><br/>");

if (isset($_POST['osoite'])) {
    $omistaja_id = $_POST['omistaja_id'];
    $osoite = pg_escape_string($_POST['osoite']);

    // Tarkistetaan syötteiden oikeellisuus
    if ($omistaja_id === null || intval($osoite) !== 0 || $osoite === '') {
        echo(VIRHE_ILMOITUS . "Vääränlaiset syötteet<br/><br/>");
    }
    // Syötteet hyväksyttäviä, siispä jatketaan
    else {
        lisaa_tyokohde($omistaja_id, $osoite);
    }
}

/*
* Metodi työkohteen lisäämiselle
*/
function lisaa_tyokohde($omistaja_id, $osoite) {

    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        return;
    }

    // Haetaan ja lasketaan työkohteiden lukumäärä

    $tyokohde_lkm_kysely = "SELECT COUNT(*) AS lkm FROM tyokohde;";
    $lkm_tulos = pg_query($tyokohde_lkm_kysely);
    if(!$rivi = pg_fetch_row($lkm_tulos)) {
        echo(VIRHE_ILMOITUS);
        echo("Tietokantavirhe<br/><br/>");
        exit;
    }
    $lkm = $rivi[0];

    // Työkohteen ID = työkohteiden lukumäärä + 1
    $tyokohde_id = $lkm + 1;

    // Lisätään työkohde

    $tyokohde_lisays_kysely = "INSERT INTO tyokohde VALUES($omistaja_id, $tyokohde_id, '$osoite');";
    $tulos = pg_query($tyokohde_lisays_kysely);
    
    if(!$tulos) {
        echo(VIRHE_ILMOITUS);
        echo("Tietokantavirhe<br/><br/>");
    }
    else {
        echo(ONNISTUMIS_ILMOITUS);
    }

    pg_close($yhteys);
}

?>

<?php

/*
*
* PHP-osio
* Kaikkien asiakkaiden tietojen haku sekä lomakkeen luonti
*
*/
if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
    echo("Tietokantayhteyden luominen epäonnistui.");
} else {


    echo('<form method="post" action="Tyokohde-lisays.php">');
    echo("<b>Omistaja:</b>&nbsp;");
    echo("<select name='omistaja_id'>");

    $asiakkaat_kysely = "SELECT * FROM asiakas;";
    $tulos = pg_query($asiakkaat_kysely);
    if ($tulos) {
        while ($rivi = pg_fetch_row($tulos)) {
            echo("<option value='$rivi[0]'>$rivi[1]</option>");
        }
    }

    pg_close($yhteys);

    echo("</select><br/><br/>");

    echo('<b>Osoite:</b> ');
    echo('<input type="text" name="osoite" value=""/>');
    echo('<br/><br/>');
    echo('<input type="submit" name="tyokohde_lisays" value="Lisää"/>');
    echo('</form>');
}
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