---
title: Dokumentacija
description: Geolokacija članova i javna karta
---

Dodatak omogućuje:

* pohranu geografskih koordinata članova (zemljopisne širine i dužine),
* javnu kartu članova s podmirenom članarinom koji su pristali na javni prikaz.

## Instalacija

Najprije preuzmite dodatak:

* [Preuzmite najnoviji dodatak
  Maps!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Preuzmite noćnu razvojnu verziju dodatka
  Maps!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

Raspakirajte preuzetu arhivu u Galetteovu mapu `plugins`. Primjerice, na Linuxu
(zamijenite `{url}` i `{version}` odgovarajućim vrijednostima):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Inicijalizacija baze podataka

Dodatak zahtijeva nekoliko tablica u bazi. Pogledajte [sučelje za upravljanje
dodacima
Galettea](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

Gotovo; dodatak Maps je instaliran :)

## Kartografska podloga

> **Napomena** — Odabir pružatelja dodan je u verziji 2.3.0.

Pružatelja odabirete u `Postavkama karte` u izborniku `Postavke`.

![Odabir pružatelja u postavkama karte](images/tiles_settings.png)

Ponuđeni su pružatelji:

* **OpenFreeMap, svijetlosiva** — zadano. Vektorske pločice u nenametljivoj
  sivoj boji ističu oznake članova. Nisu potrebni račun ni API ključ, a uslugu
  možete [samostalno hostati](https://openfreemap.org).
* **OpenFreeMap, u boji** — ista usluga s prikazom u punoj boji.
* **OpenStreetMap** — standardni prikaz s poslužitelja zaklade OpenStreetMap.
* **OpenStreetMap France** i **Humanitarian OSM Team** — hosta ih udruga OSM-FR;
  drugi prikaz više ističe ceste i objekte.
* **OpenStreetMap Germany** — njemački prikaz koji daje prednost lokalnim
  nazivima.
* **Esri, svijetlosiva** — vrlo svijetlosivi prikaz sličan ranijem prikazu
  dodatka.

### Vlastite vrijednosti

> **Upozorenje** — Provjerite uvjete uporabe odabranog pružatelja. Većinu usluga
> vode udruge ili volonteri koji određuju uvjete dopuštenog prometa.

Zadnja stavka popisa, `Vlastite vrijednosti`, zamjenjuje ponuđene pružatelje
vašom adresom: nenavedenim pružateljem ili vlastitim poslužiteljem pločica.

![Polja stavke Vlastite vrijednosti](images/tiles_custom.png)

* **Vektorske pločice** određuje što dodatak prima: stil MapLibre ako je
  označeno ili klasične rasterske pločice ako nije.
* **Adresa** je adresa stila za vektorske pločice ili adresa rasterskih pločica,
  primjerice `https://tile.openstreetmap.org/{z}/{x}/{y}.png`.
* **Navođenje izvora** sadrži obvezno priznanje pružatelju. Dopušten je HTML. To
  nije samo formalnost: licence podataka zahtijevaju navođenje izvora.
* **Najveće povećanje** najviša je razina povećanja koju pružatelj podržava.
  Veće razine prikazuju prazne pločice.
* **Poddomene** navodi slova kojima se zamjenjuje oznaka `{s}` u adresi,
  primjerice `abc`. Vrijedi samo za rasterske pločice.

## Preciznost položaja

Pohranjeni položaj člana često je kućna adresa. Karta ga ne prikazuje izravno,
nego položaje prilagođava mreži kako bi oznaka bila blizu člana, a ne na
njegovim vratima.

Preciznost određujete u `Postavkama karte` u izborniku `Postavke`:

* **Oko 100 km**, **Oko 50 km**, **Oko 5 km** — za regiju, županiju ili mjesto;
* **Oko 500 m** — zadano, za susjedstvo;
* **Oko 50 m** — za ulicu;
* **Točan položaj** — bez prilagodbe mreži, kao u ranijim verzijama.

Vrijednost je najveća moguća udaljenost oznake od pohranjenog položaja.

Prilagodba položaja vrijedi za posjetitelje i članove, uključujući voditelje
grupa. Uprava i administratori vide točne položaje, a svaki član vidi svoj
položaj kako ga je pohranio. Stranica za postavljanje položaja nije obuhvaćena
time: točan položaj prikazuje svima koji je smiju otvoriti.

Ako karta prikazuje prilagođene položaje, povećanje je ograničeno prema
preciznosti kako oznaka ne bi izgledala kao da pokazuje točnu adresu.

## Uporaba dodatka

Nakon instalacije prijavljenom se članu u izborniku pojavljuje odjeljak `Karte`
sa stavkom `Moja lokacija`. Na toj stranici član može pohraniti svoj položaj.

Pri prikazu člana dodaje se i gumb `Odredi lokaciju`, kojim administratori mogu
zadati koordinate člana.

Na javne se stranice dodaje stavka `Karta`, koja prikazuje članove s lokacijom i
podmirenom članarinom. Administratori i uprava vide sve članove, a obični
članovi i posjetitelji samo javno vidljive članove s podmirenom članarinom.

Član najprije unosi koordinate svojeg položaja. Dostupne su mogućnosti:

* ako je u podacima člana navedeno mjesto, predlaže se popis mogućih lokacija
  putem [mrežne usluge Nominatim](https://nominatim.openstreetmap.org),
* dodatno polje pretraživanja koje pruža
  [OpenStreetMap](https://nominatim.openstreetmap.org/),
* gumb za određivanje lokacije pomoću preglednika.

Polje pretraživanja dostupno je pri spremanju lokacije člana i prikazu karte.

![Popis mjesta predloženih članu](images/towns_list.png)

Član može odrediti lokaciju na karti uz željenu preciznost odabirom jedne od
ponuđenih mogućnosti:

![Odabir lokacije na karti](images/location_select.png)

Gumb za određivanje lokacije postavlja položaj koji utvrdi preglednik:

![Gumb za određivanje lokacije](images/geoloc.png)

Lokacija člana zatim se prikazuje na karti i može se ukloniti:

![Odabrana lokacija prikazana na karti](images/location_selected.png)
