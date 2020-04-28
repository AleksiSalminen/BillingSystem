--
--
-- Lisäyslauseita tietokannan helpompaan alustamiseen
--
--

-- asiakas-taulun lisäyksiä
-- [ asiakas_id | nimi | osoite | puh_nro | sposti ]
INSERT INTO asiakas VALUES (1, 'Topi Toimelias', 'Topinkatu 1', '010 010 0101', 'topi@toimelias.com');
INSERT INTO asiakas VALUES (2, 'Marja Mallikas', 'Marjankatu 2', '020 020 0202', 'marja@mallikas.com');
INSERT INTO asiakas VALUES (3, 'Matti Meikalainen', 'Matinkatu 3', '030 030 0303', 'matti@meikalainen.com');

-- tyokohde-taulun lisäyksiä
-- [ asiakas_id | tyokohde_id | osoite ]
INSERT INTO tyokohde VALUES (1, 1, 'Teollisuuskatu 1');
INSERT INTO tyokohde VALUES (2, 2, 'Kohdekatu 2');
INSERT INTO tyokohde VALUES (1, 3, 'Koivutie 15');
INSERT INTO tyokohde VALUES (3, 4, 'Maallikonkuja 7');

-- sopimus-taulun lisäyksiä
-- [ sopimus_id | tyokohde_id | laskujen_maara | on_tunti ]
INSERT INTO sopimus VALUES (1, 1, 0, true);
INSERT INTO sopimus VALUES (2, 1, 0, false);
INSERT INTO sopimus VALUES (3, 4, 0, true);
INSERT INTO sopimus VALUES (4, 2, 0, true);
INSERT INTO sopimus VALUES (5, 3, 0, false);

-- tyotyyppi-taulun lisäyksiä
-- [ tyotyyppi_id | tyyppinimi | tuntihinta | kotitalousvahennettava ]
INSERT INTO tyotyyppi VALUES (1, 'Suunnittelu', 55, FALSE);
INSERT INTO tyotyyppi VALUES (2, 'Työ',         45, TRUE);
INSERT INTO tyotyyppi VALUES (3, 'Aputyö',      35, TRUE);
INSERT INTO tyotyyppi VALUES (4, 'Jälkityö',    30, TRUE);

-- tyosuoritus-taulun lisäyksiä
-- [ sopimus_id | tyotyyppi_id | on_tunti | tuntimaara | urakkahinta | alennusprosentti ]
INSERT INTO tyosuoritus VALUES (2, 1, false, 0, 170, 3);
INSERT INTO tyosuoritus VALUES (1, 2, true, 28, 0, 0);
INSERT INTO tyosuoritus VALUES (1, 3, true, 15, 0, 5);
INSERT INTO tyosuoritus VALUES (1, 4, true, 3, 0, 0);
INSERT INTO tyosuoritus VALUES (3, 2, true, 10, 0, 8);
INSERT INTO tyosuoritus VALUES (5, 1, false, 0, 145, 0);

-- tarvike-taulun lisäyksiä
-- [ tarvike_id | nimi | sisaanostohinta | yksikko | varastomaara | myyntihinta | arvonlisavero ]
INSERT INTO tarvike VALUES (1, 'Naulakiinnike', 0.09, 'kpl', 500, 0.15, 24);
INSERT INTO tarvike VALUES (2, 'Kytkin', 9.99, 'kpl', 30, 11.99, 24);
INSERT INTO tarvike VALUES (3, 'Kaapeli', 1.50, 'metria', 430, 1.99, 24);
INSERT INTO tarvike VALUES (4, 'Kaapelipidike', 0.30, 'kpl', 215, 0.50, 24);
INSERT INTO tarvike VALUES (5, 'Pistorasia', 4.99, 'kpl', 90, 5.99, 24);
INSERT INTO tarvike VALUES (6, 'Pikaliitin', 0.70, 'kpl', 140, 0.99, 24);
INSERT INTO tarvike VALUES (7, 'Opaskirja', 14.99, 'kpl', 4, 17.99, 10);

-- tarvikelista-taulun lisäyksiä
-- [ sopimus_id | tarvike_id | maara | alennusprosentti ]
INSERT INTO tarvikelista VALUES (1, 3, 7, 5);
INSERT INTO tarvikelista VALUES (1, 4, 20, 0);
INSERT INTO tarvikelista VALUES (1, 2, 4, 10);
INSERT INTO tarvikelista VALUES (1, 1, 37, 0);
INSERT INTO tarvikelista VALUES (1, 7, 1, 5);
INSERT INTO tarvikelista VALUES (3, 3, 4, 0);
INSERT INTO tarvikelista VALUES (3, 6, 15, 0);
INSERT INTO tarvikelista VALUES (3, 4, 7, 0);
INSERT INTO tarvikelista VALUES (4, 2, 3, 0);

-- lasku-taulun lisäyksiä
-- [ sopimus_id | lasku_nro | muistutus_nro | pvm | erapvm | maksu_pvm | maksettavaa ]
INSERT INTO lasku VALUES (1, 1, 0, '2020-03-01', '2020-04-01', NULL, 999.50);
INSERT INTO lasku VALUES (2, 1, 0, '2020-02-01', '2020-03-01', NULL, 500);
INSERT INTO lasku VALUES (2, 1, 1, '2020-03-01', '2020-04-01', NULL, 500.00);
INSERT INTO lasku VALUES (4, 1, 0, '2020-03-01', '2020-05-01', NULL, 350.00);

