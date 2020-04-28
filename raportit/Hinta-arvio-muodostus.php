<html>
<head>
    <meta charset="utf-8"/>
    <title>Tiko HT - Hinta-arvion muodostus</title>
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
    <h1>Hinta-arvion muodostus</h1>

<?php 

/*
*
* PHP-osio
* Lomake arvioitujen työsuoritteiden ilmoittamiseen
*
*/

echo("<p>--------------------------------------------------------</p>");
echo("<h2>Arvioidut työsuoritteet</h2>");

$suoritteet_lkm = intval($_POST['tyosuorite_maara']);

if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
    die("Tietokantayhteyden luominen epaonnistui.");
}

$tyotyypit_kysely = "SELECT tyotyyppi_id, tyyppinimi, tuntihinta FROM tyotyyppi ORDER BY tyotyyppi_id ASC;";
$tulos = pg_query($tyotyypit_kysely);
$tyotyypit_lkm = pg_num_rows($tulos);

$tyotyypit = pg_fetch_all($tulos);

pg_close($yhteys);

echo('<form method="post" action="Hinta-arvio-muodostus.php">');

echo("<b>Määrä:</b> <input type='number' name='tyosuorite_maara' min='0' max='$tyotyypit_lkm' value='$suoritteet_lkm'></input><br/><br/>");

$tyotyypit_kokonaishinta = 0;

$kierros = 1;
while($kierros <= $suoritteet_lkm) {
    echo("<select name='tyotyyppi$kierros'>");

    $indeksi = 0;
    $tyotyyppi = $tyotyypit[$indeksi];
    while($tyotyyppi !== null) {
        if($_POST["tyotyyppi$kierros"] == ($indeksi+1)) {
            echo("<option value='$tyotyyppi[tyotyyppi_id]' selected>$tyotyyppi[tyyppinimi] - tuntia:</option>");
        }
        else {
            echo("<option value='$tyotyyppi[tyotyyppi_id]'>$tyotyyppi[tyyppinimi] - tuntia:</option>");
        }

        $indeksi++;
        $tyotyyppi = $tyotyypit[$indeksi];
    }

    echo("</select>");

    $tunnit = intval($_POST["tyotyyppi_tunnit$kierros"]);
    echo(" <input type='number' name='tyotyyppi_tunnit$kierros' value='$tunnit' min='0' max='999'/>");

    $valittu_id = $_POST["tyotyyppi$kierros"];
    $hinta = $tyotyypit[($valittu_id - 1)]['tuntihinta'];
    $yhteishinta = $tunnit * $hinta;
    $tyotyypit_kokonaishinta += $yhteishinta;
    echo(" * {$hinta}€ = {$yhteishinta}€<br/><br/>");

    $kierros++;
}

echo("<p><b>Työsuoritteiden kokonaishinta:</b> {$tyotyypit_kokonaishinta}€</p>");




/*
*
* PHP-osio
* Lomake arvioitujen käytettävien tarvikkeiden ilmoittamiseen
*
*/

echo("<p>--------------------------------------------------------</p>");
echo("<h2>Arvioidut käytettävät tarvikkeet</h2>");

$tarvikkeet_maara = intval($_POST['tarvike_maara']);

if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
    die("Tietokantayhteyden luominen epaonnistui.");
}

$tarvikkeet_kysely = "SELECT tarvike_id, nimi, yksikko, myyntihinta FROM tarvike ORDER BY tarvike_id ASC;";
$tulos = pg_query($tarvikkeet_kysely);
$tarvikkeet_lkm = pg_num_rows($tulos);

$tarvikkeet = pg_fetch_all($tulos);

pg_close($yhteys);

$tarvikkeet_kokonaishinta = 0;

echo("<b>Määrä:</b> <input type='number' name='tarvike_maara' min='0' max='$tarvikkeet_lkm' value='$tarvikkeet_maara'></input><br/><br/>");

$kierros = 1;
while($kierros <= $tarvikkeet_maara) {
    echo("<select name='tarvike$kierros'>");

    $indeksi = 0;
    $tarvike = $tarvikkeet[$indeksi];
    while($tarvike !== null) {
        if($_POST["tarvike$kierros"] == ($indeksi+1)) {
            echo("<option value='$tarvike[tarvike_id]' selected>$tarvike[nimi] - $tarvike[yksikko]:</option>");
        }
        else {
            echo("<option value='$tarvike[tarvike_id]'>$tarvike[nimi] - $tarvike[yksikko]:</option>");
        }

        $indeksi++;
        $tarvike = $tarvikkeet[$indeksi];
    }

    echo("</select>");

    $maara = intval($_POST["tarvike_maara$kierros"]);
    echo(" <input type='number' name='tarvike_maara$kierros' value='$maara' min='0' max='9999'/>");

    $valittu_id = $_POST["tarvike$kierros"];
    $hinta = $tarvikkeet[($valittu_id - 1)]['myyntihinta'];
    $yhteishinta = $maara * $hinta;
    $tarvikkeet_kokonaishinta += $yhteishinta;
    echo(" * {$hinta}€ = {$yhteishinta}€<br/><br/>");

    $kierros++;
}

echo("<p><b>Tarvikkeiden kokonaishinta:</b> {$tarvikkeet_kokonaishinta}€</p>");


///////////////////////////////////////////////////////7

echo("<p>--------------------------------------------------------</p>");
$hinta_arvio = $tyotyypit_kokonaishinta + $tarvikkeet_kokonaishinta;
echo("<h3>Kokonaishinta: {$hinta_arvio}€</h3>");


echo("<input type='submit' name='hinta-arvio_muodostus' value='Päivitä'/>");


echo('</form>');

?>


    <!--
    ---- 
    ---- HTML-osio
    ---- Painike etusivulle palaamiseen
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