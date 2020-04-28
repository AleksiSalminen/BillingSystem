CREATE TABLE asiakas (
    asiakas_id INT NOT NULL,
    nimi VARCHAR(100),
    osoite VARCHAR(100),
    puh_nro VARCHAR(20),
    sposti VARCHAR(100),
    PRIMARY KEY (asiakas_id)
);

CREATE TABLE tyokohde (
    asiakas_id INT NOT NULL,
    tyokohde_id INT NOT NULL,
    osoite VARCHAR(100),
    PRIMARY KEY (tyokohde_id),
    FOREIGN KEY (asiakas_id) REFERENCES asiakas (asiakas_id)
);

CREATE TABLE sopimus (
    sopimus_id INT NOT NULL,
    tyokohde_id INT NOT NULL,
    laskujen_maara INT,
    on_tunti BOOLEAN,
    PRIMARY KEY (sopimus_id),
    FOREIGN KEY (tyokohde_id) REFERENCES tyokohde (tyokohde_id)
);

CREATE TABLE tyotyyppi (
    tyotyyppi_id INT NOT NULL,
    tyyppinimi VARCHAR(25),
    tuntihinta INT,
    kotitalousvahennettava BOOLEAN,
    PRIMARY KEY (tyotyyppi_id)
);

CREATE TABLE tyosuoritus (
    sopimus_id INT NOT NULL,
    tyotyyppi_id INT NOT NULL,
    on_tunti BOOLEAN,
    tuntimaara INT,
    urakkahinta INT,
    alennusprosentti INT,
    PRIMARY KEY (sopimus_id, tyotyyppi_id),
    FOREIGN KEY (sopimus_id) REFERENCES sopimus (sopimus_id),
    FOREIGN KEY (tyotyyppi_id) REFERENCES tyotyyppi (tyotyyppi_id)
);

CREATE TABLE tarvike (
    tarvike_id INT NOT NULL,
    nimi VARCHAR(50),
    sisaanostohinta NUMERIC(16, 2),
    yksikko VARCHAR(25),
    varastomaara INT,
    myyntihinta NUMERIC(16, 2),
    arvonlisavero INT,
    PRIMARY KEY (tarvike_id)
);

CREATE TABLE tarvikelista (
    sopimus_id INT NOT NULL,
    tarvike_id INT NOT NULL,
    maara INT,
    alennusprosentti INT,
    PRIMARY KEY (sopimus_id, tarvike_id),
    FOREIGN KEY (sopimus_id) REFERENCES sopimus (sopimus_id),
    FOREIGN KEY (tarvike_id) REFERENCES tarvike (tarvike_id)
);

CREATE TABLE lasku (
    sopimus_id INT NOT NULL,
    lasku_nro INT NOT NULL,
    muistutus_nro INT NOT NULL,
    pvm DATE,
    erapvm DATE,
    maksu_pvm DATE,
    maksettavaa NUMERIC(16, 2),
    PRIMARY KEY (sopimus_id, lasku_nro, muistutus_nro),
    FOREIGN KEY (sopimus_id) REFERENCES sopimus (sopimus_id)
);
