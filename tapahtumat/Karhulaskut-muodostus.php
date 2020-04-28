<html>
<head>
    <meta charset="utf-8"/>
    <title>Tiko HT - Karhulaskujen muodostus</title>
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
    <h1>Karhulaskujen muodostus</h1>
    <p>--------------------------------------------------------</p>
<?php

if (isset($_POST['luo_laskut'])) {
    $fail = false;
    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        echo("Tietokantayhteyden luominen epaonnistui.");
    }
    else {
        $eraantyneet_kysely .= "SELECT mlasku.sopimus_id, mlasku.lasku_nro, mlasku.muistutus_nro, lasku.maksettavaa, ";
        $eraantyneet_kysely .= "date_part('days', now() - mlasku.erapvm) AS viivastys_pv, ";
        $eraantyneet_kysely .= "date_part('days', now() - lasku.erapvm) AS lisa_viivastys_pv ";
        $eraantyneet_kysely .= "FROM lasku AS mlasku ";
        $eraantyneet_kysely .= "LEFT OUTER JOIN lasku ";
        $eraantyneet_kysely .= "ON lasku.muistutus_nro = 0 ";
        $eraantyneet_kysely .= "AND mlasku.sopimus_id = lasku.sopimus_id ";
        $eraantyneet_kysely .= "AND mlasku.lasku_nro = lasku.lasku_nro ";
        $eraantyneet_kysely .= "WHERE mlasku.maksu_pvm IS NULL ";
        $eraantyneet_kysely .= "AND date_part('days', now() - mlasku.erapvm) > 0 ";
        $eraantyneet_kysely .= "AND mlasku.muistutus_nro = 1 ";
        $eraantyneet_kysely .= "AND (mlasku.sopimus_id, mlasku.lasku_nro, mlasku.muistutus_nro + 1) NOT IN ( ";
        $eraantyneet_kysely .= "    SELECT sopimus_id, lasku_nro, muistutus_nro FROM lasku ";
        $eraantyneet_kysely .= ");";


        $eraantyneet = pg_query($eraantyneet_kysely);

        if ($eraantyneet && pg_num_rows($eraantyneet) !== 0) {
            $uudetlaskut = 0;
            
            while ($rivi = pg_fetch_row($eraantyneet)) {
                // korko alkuperäiselle laskulle
                $maksettavaa = $rivi[3] * pow(1.16, $rivi[4]/365);
                // korko muistutuslaskun laskulisälle
                $maksettavaa += 5 * pow(1.16, $rivi[5]/365);
                // laskulisä
                $maksettavaa += 5;

                $muistutus = pg_query("INSERT INTO lasku VALUES ($rivi[0], $rivi[1], $rivi[2] + 1, now(), now() + interval '1 month', NULL, $maksettavaa);");
                if (!$muistutus) {
                    $fail = true;
                    break;
                }
                $uudetlaskut += 1;
            }
            if (!$fail) {
                pg_query("COMMIT;");
                if ($uudetlaskut === 1) {
                    echo("Lisättiin 1 uusi karhulasku.");
                } else {
                    echo("Lisättiin $uudetlaskut uutta karhulaskua.");
                }
            }
        } else if (!$eraantyneet) {
            $fail = true;
        } else {
            pg_query("ROLLBACK;");
            echo("Ei uusia erääntyneitä muistutuslaskuja.");
        }

    }
    if ($fail) {
        pg_query("ROLLBACK;");
        echo("Muistutuslaskujen lisääminen epäonnistui!");
    }
} else {
    echo('
        <form method="post" action="./Karhulaskut-muodostus.php">
            <table>
                <tr>
                <td><input type="submit" name="luo_laskut" luo_laskut="true" value="Muodosta uudet karhulaskut"/></td>
                </tr> 
            </table>
        </form>');
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