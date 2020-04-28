<html>
<head>
    <meta charset="utf-8"/>
    <title>Tiko HT - Muistutuslaskujen muodostus</title>
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
    <h1>Muistutuslaskujen muodostus</h1>
    <p>--------------------------------------------------------</p>
<?php

if (isset($_POST['luo_laskut'])) {
    $fail = false;
    if (!$yhteys = pg_connect($GLOBALS['y_tiedot'])) {
        echo("Tietokantayhteyden luominen epaonnistui.");
    }
    else {
        $eraantyneet_kysely = "BEGIN;";
        $eraantyneet_kysely .= "SELECT sopimus_id, lasku_nro, muistutus_nro, maksettavaa FROM lasku ";
        $eraantyneet_kysely .= "WHERE maksu_pvm IS NULL ";
        $eraantyneet_kysely .= "AND date_part('days', now() - erapvm) > 0 ";
        $eraantyneet_kysely .= "AND muistutus_nro = 0 ";
        $eraantyneet_kysely .= "AND (sopimus_id, lasku_nro, muistutus_nro + 1) NOT IN ( ";
        $eraantyneet_kysely .= "    SELECT sopimus_id, lasku_nro, muistutus_nro FROM lasku ";
        $eraantyneet_kysely .= ");";

        $eraantyneet = pg_query($eraantyneet_kysely);

        if ($eraantyneet && pg_num_rows($eraantyneet) !== 0) {
            $uudetlaskut = 0;
            while ($rivi = pg_fetch_row($eraantyneet)) {
                $muistutus = pg_query("INSERT INTO lasku VALUES ($rivi[0], $rivi[1], $rivi[2] + 1, now(), now() + interval '1 month', NULL, $rivi[3] + 5);");
                if (!$muistutus) {
                    $fail = true;
                    break;
                }
                $uudetlaskut += 1;
            }
            if (!$fail) {
                pg_query("COMMIT;");
                if ($uudetlaskut === 1) {
                    echo("Lisättiin 1 uusi lasku.");
                } else {
                    echo("Lisättiin $uudetlaskut uutta muistutuslaskua.");
                }
            }
        } else if (!$eraantyneet) {
            $fail = true;
        } else {
            pg_query("ROLLBACK;");
            echo("Ei uusia erääntyneitä laskuja.");
        }

    }
    if ($fail) {
        pg_query("ROLLBACK;");
        echo("Muistutuslaskujen lisääminen epäonnistui!");
    }
} else {
    echo('
        <form method="post" action="./Muistutuslaskut-muodostus.php">
            <table>
                <tr>
                <td><input type="submit" name="luo_laskut" luo_laskut="true" value="Muodosta uudet muistutuslaskut"/></td>
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