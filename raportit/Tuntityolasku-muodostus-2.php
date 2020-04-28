<html>
<head>
    <meta charset="utf-8"/>
    <title>Tiko HT - Tuntityölaskun muodostus</title>
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
    <h1>Tuntityölaskun muodostus</h1>


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
echo('<form method="post" action="Tuntityolasku-muodostus-2.php">');
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

    echo('<input type="submit" name="sopimus_valinta" value="Muodosta sopimukselle uusi tuntityölasku"/>');
}

?>

<?php 

/*
*
* PHP-osio
* Uuden tuntityölaskun muodostus
*
*/

$tyokohde_id = $_POST['tyokohde_id'];
$sopimus_id = $_POST['sopimus_id'];

if($tyokohde_id !== null && $sopimus_id !== null) {
    echo('<p>--------------------------------------------------------</p>');
    echo("<h2>Tuntityölasku</h2>");

    esita_asiakas_tiedot($tyokohde_id);
    $tarvikkeet_tiedot = esita_tarvikkeiden_tiedot($sopimus_id);
    $tyot_tiedot = esita_tuntityot_tiedot($sopimus_id);

    $summa_ilman_alv = $tarvikkeet_tiedot[0] + $tyot_tiedot[0];
    $alv_osuus = $tarvikkeet_tiedot[1] + $tyot_tiedot[1];
    $kokonaissumma = $tarvikkeet_tiedot[2] + $tyot_tiedot[2];

    echo("<p>-----------</p>");
    echo("<h3>YHTEENVETO</h3>");
    echo("<p><b>SUMMA ILMAN ALV: </b>{$summa_ilman_alv}€</p>");
    echo("<p><b>ALV-OSUUS: </b>{$alv_osuus}€</p>");
    echo("<p><b>KOKONAISSUMMA: </b>{$kokonaissumma}€</p>");
    echo("<p><b>KOTITALOUSVÄHENNETTÄVÄ: </b>{$tyot_tiedot[3]}€</p>");

    selvita_taytettavat_tiedot();

    tallennaLasku($kokonaissumma);
}


/*
* Metodi asiakkaan tietojen esittämiselle
*/
function esita_asiakas_tiedot($tyokohde_id) {
    if(!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $asiakas_tiedot_kysely = "SELECT a.asiakas_id, nimi, a.osoite, puh_nro, sposti, t.osoite  FROM tyokohde AS t JOIN asiakas AS a ON t.asiakas_id = a.asiakas_id WHERE tyokohde_id = $tyokohde_id;";
    $asiakas_tiedot_tulos = pg_query($asiakas_tiedot_kysely);
    if($asiakas_tiedot_tulos) {
        $tiedot = pg_fetch_row($asiakas_tiedot_tulos);

        $asiakas_id = $tiedot[0];
        $asiakas_nimi = $tiedot[1];
        $asiakas_osoite = $tiedot[2];
        $asiakas_puh_nro = $tiedot[3];
        $asiakas_sposti = $tiedot[4];
        $tyokohde_osoite = $tiedot[5];
        $pvm = date('d.m.Y', time());

        echo("<p>-----------</p>");
        echo("<p><b>Päivämäärä:</b> $pvm</p>");
        echo("<br/>");
        echo("<p><b>ASIAKKAAN TIEDOT</b></p>");
        echo("<p><b>Nimi:</b> $asiakas_nimi</p>");
        echo("<p><b>Osoite:</b> $asiakas_osoite</p>");
        echo("<p><b>Puhelinnumero:</b> $asiakas_puh_nro</p>");
        echo("<p><b>Sähköpostiosoite:</b> $asiakas_sposti</p>");
        echo("<p><b>Kohteen osoite:</b> $tyokohde_osoite</p>");
    }

    pg_close($yhteys);
}


/*
* Metodi tarvikkeiden tietojen esittämiselle
*/
function esita_tarvikkeiden_tiedot($sopimus_id) {
    echo("<p>-----------</p>");
    echo("<p><b>KÄYTETTYJEN TARVIKKEIDEN TIEDOT</b></p>");
    echo("<table border='1'>");
    echo("<tr><th>Nimi</th><th>Käytetty määrä</th><th>Yksikköhinta</th><th>Alkuperäinen hinta</th><th>Alennus</th><th>Alennettu hinta ilman ALV</th><th>ALV</th><th>ALV-osuus</th><th>Lopullinen hinta</th></tr>");

    if(!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $tarvikkeet_hinta_ilman_alv = 0;
    $tarvikkeet_alv_osuus = 0;
    $tarvikkeet_lopullinen_hinta = 0;

    $tarvike_tiedot_kysely = "SELECT t.tarvike_id, nimi, yksikko, maara, myyntihinta, alennusprosentti, arvonlisavero FROM tarvikelista AS tl JOIN tarvike AS t ON tl.tarvike_id = t.tarvike_id WHERE tl.sopimus_id = $sopimus_id AND tl.maara > 0;";
    $tarvike_tiedot_tulos = pg_query($tarvike_tiedot_kysely);
    if($tarvike_tiedot_tulos) {
        while($rivi = pg_fetch_row($tarvike_tiedot_tulos)) {
            echo("<tr>");
            $tarvike_id = $rivi[0];
            $tarvike_nimi = $rivi[1];
            $tarvike_yksikko = $rivi[2];
            $tarvike_maara = $rivi[3];
            $tarvike_hinta = $rivi[4];
            $tarvike_alennus = $rivi[5];
            $tarvike_alv = $rivi[6];

            $alkup_hinta = $tarvike_hinta * $tarvike_maara;
            $hinta_ilman_alv = $alkup_hinta * ((100 - $tarvike_alennus) / 100);
            $alv_osuus = ($tarvike_alv / 100) * $hinta_ilman_alv;
            $lopullinen_hinta = $hinta_ilman_alv + $alv_osuus;

            echo("<td>$tarvike_nimi</td>");
            echo("<td>$tarvike_maara $tarvike_yksikko</td>");
            echo("<td>{$tarvike_hinta}€</td>");
            echo("<td>{$alkup_hinta}€</td>");
            if($tarvike_alennus > 0) {
                echo("<td>{$tarvike_alennus}%</td>");
            }
            else {
                echo("<td>Ei</td>");
            }
            echo("<td>{$hinta_ilman_alv}€</td>");
            echo("<td>{$tarvike_alv}%</td>");
            echo("<td>{$alv_osuus}€</td>");
            echo("<td>{$lopullinen_hinta}€</td>");
            
            echo("</tr>");

            $tarvikkeet_hinta_ilman_alv += $hinta_ilman_alv;
            $tarvikkeet_alv_osuus += $alv_osuus;
            $tarvikkeet_lopullinen_hinta += $lopullinen_hinta;
        }
    }

    pg_close($yhteys);

    echo("</table>");

    echo("<p><b>Tarvikkeiden hinta ilman ALV: </b>{$tarvikkeet_hinta_ilman_alv}€</p>");
    echo("<p><b>Tarvikkeiden ALV-osuus: </b>{$tarvikkeet_alv_osuus}€</p>");
    echo("<p><b>Tarvikkeiden lopullinen hinta: </b>{$tarvikkeet_lopullinen_hinta}€</p>");
    
    $tarvikkeet_tiedot = array($tarvikkeet_hinta_ilman_alv, $tarvikkeet_alv_osuus, $tarvikkeet_lopullinen_hinta);
    return $tarvikkeet_tiedot;
}


/*
* Metodi tuntitöiden tietojen esittämiselle
*/
function esita_tuntityot_tiedot($sopimus_id) {
    echo("<p>-----------</p>");
    echo("<p><b>TUNTIERITTELY</b> (ALV-kanta 24%)</p>");
    echo("<table border='1'>");
    echo("<tr><th>Nimi</th><th>Tunnit</th><th>Tuntihinta</th><th>Alkuperäinen hinta</th><th>Alennus</th><th>Alennettu hinta ilman ALV</th><th>ALV-osuus</th><th>Lopullinen hinta</th><th>Kotitalousvähennettävä</th></tr>");

    $tyotunnit_hinta_ilman_alv = 0;
    $tyotunnit_alv_osuus = 0;
    $tyotunnit_lopullinen_hinta = 0;
    $tyotunnit_kotitalousvahennys = 0;
    
    if(!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $tyotunnit_tiedot_kysely = "SELECT tt.tyotyyppi_id, tyyppinimi, tuntimaara, tuntihinta, kotitalousvahennettava, alennusprosentti FROM tyosuoritus AS ts JOIN tyotyyppi AS tt ON ts.tyotyyppi_id = tt.tyotyyppi_id WHERE ts.sopimus_id = $sopimus_id AND tuntimaara > 0;";
    $tyotunnit_tiedot_tulos = pg_query($tyotunnit_tiedot_kysely);
    if($tyotunnit_tiedot_tulos) {
        while($rivi = pg_fetch_row($tyotunnit_tiedot_tulos)) {
            $tyotyyppi_id = $rivi[0];
            $tyotyyppi_nimi = $rivi[1];
            $tyotyyppi_tunnit = $rivi[2];
            $tyotyyppi_hinta = $rivi[3];
            $tyotyyppi_ktv = $rivi[4];
            $tyotyyppi_alennus = $rivi[5];

            $alkup_hinta = $tyotyyppi_hinta * $tyotyyppi_tunnit;
            $hinta_ilman_alv = ((100 - $tyotyyppi_alennus) / 100) * $alkup_hinta;
            $alv_osuus = 0.24 * $hinta_ilman_alv;
            $lopullinen_hinta = $hinta_ilman_alv + $alv_osuus;

            echo("<tr>");
            echo("<td>$tyotyyppi_nimi</td>");
            echo("<td>$tyotyyppi_tunnit</td>");
            echo("<td>{$tyotyyppi_hinta}€</td>");
            echo("<td>{$alkup_hinta}€</td>");
            if($tyotyyppi_alennus > 0) {
                echo("<td>{$tyotyyppi_alennus}%</td>");
            }
            else {
                echo("<td>Ei</td>");
            }
            echo("<td>{$hinta_ilman_alv}€</td>");
            echo("<td>{$alv_osuus}€</td>");
            echo("<td>{$lopullinen_hinta}€</td>");
            if($tyotyyppi_ktv === 't') {
                echo("<td>Kyllä</td>");
                $tyotunnit_kotitalousvahennys += $lopullinen_hinta;
            }
            else {
                echo("<td>Ei</td>");
            }
            echo("</tr>");

            $tyotunnit_hinta_ilman_alv += $hinta_ilman_alv;
            $tyotunnit_alv_osuus += $alv_osuus;
            $tyotunnit_lopullinen_hinta += $lopullinen_hinta;
        }
    }

    pg_close($yhteys);

    echo("</table>");

    echo("<p><b>Tuntitöiden hinta ilman ALV: </b>{$tyotunnit_hinta_ilman_alv}€</p>");
    echo("<p><b>Tuntitöiden ALV-osuus: </b>{$tyotunnit_alv_osuus}€</p>");
    echo("<p><b>Tuntitöiden lopullinen hinta: </b>{$tyotunnit_lopullinen_hinta}€</p>");
    echo("<p><b>Kotitalousvähennykseen kelpaava osuus: </b>{$tyotunnit_kotitalousvahennys}€</p>");

    $tuntityot_tiedot = array($tyotunnit_hinta_ilman_alv, $tyotunnit_alv_osuus, $tyotunnit_lopullinen_hinta, $tyotunnit_kotitalousvahennys);
    return $tuntityot_tiedot;
}


/*
* Metodi täytettävien tietojen selvittämiselle
*/
function selvita_taytettavat_tiedot() {
    $pvm = date('Y-m-d', time());

    echo("<p>-----------</p>");
    echo("<p><b>TÄYTETTÄVÄT TIEDOT</b></p>");
    if($_POST['erapvm']) {
        echo("<b>Eräpäivä:</b> <input type='date' name='erapvm' min='$pvm' value='$_POST[erapvm]'>");
    }
    else {
        echo("<b>Eräpäivä:</b> <input type='date' name='erapvm' min='$pvm' value='$pvm'>");
    }
}


/*
* Metodi laskun tallentamiselle
*/
function tallennaLasku($hinta) {
    echo("<p>-----------</p>");

    if($_POST['laskun_tallennus']) {
        $sopimus_id = $_POST['sopimus_id'];
        $lasku_nro = selvita_lasku_nro($sopimus_id);
        $muistutus_nro = 0;
        $maksettavaa = $hinta;
        $pvm = date('Y-m-d H:i:s', time());
        $erapvm = date_create($_POST['erapvm'] . ' 23:59:59');
        $erapvm = date_format($erapvm, 'Y-m-d H:i:s');
        $maksu_pvm = null;

        if(!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
            die("Tietokantayhteyden luominen epaonnistui.");
        }
    
        $lasku_lisays_kysely = "INSERT INTO lasku VALUES ($sopimus_id, $lasku_nro, $muistutus_nro, '$pvm', '$erapvm', null, $maksettavaa);";
        $lasku_lisays_tulos = pg_query($lasku_lisays_kysely);

        if($lasku_lisays_tulos) {
            echo("<p><b>Lasku tallennettu (ja lähetetty)</b></p>");
        }
        else {
            echo("<p><b>! Laskun tallennus (ja lähetys) epäonnistui !</b></p>");
        }

        pg_close($yhteys);
    }

    echo("<input type='submit' name='laskun_tallennus' value='Tallenna (ja lähetä)'/>");
}


/*
* Metodi laskun numeron selvittämiseksi
*/
function selvita_lasku_nro($sopimus_id) {
    if(!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        die("Tietokantayhteyden luominen epaonnistui.");
    }

    $lasku_lkm_kysely = "SELECT COUNT(*) AS lkm FROM lasku WHERE sopimus_id = $sopimus_id;";
    $lkm_tulos = pg_query($lasku_lkm_kysely);
    $rivi = pg_fetch_row($lkm_tulos);
    $lkm = $rivi[0];
    $lasku_nro = $lkm + 1;

    pg_close($yhteys);

    return $lasku_nro;
}


echo("</form>");

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