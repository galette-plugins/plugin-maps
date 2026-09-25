---
title: Dokumentacija
description: Geolokacija članov in javni zemljevid
---

Ta vtičnik ponuja:

* možnost shranjevanja geografskih koordinat za člane (zemljepisna širina in
  dolžina),
* javni zemljevid, ki prikazuje posodobljene člane, ki so se odločili, da bodo
  javno vidni.

## Namestitev

Najprej prenesite vtičnik:

* [Pridobite najnovejši vtičnik
  Zemljevidi!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Pridobite nočno gradnjo vtičnika
  Zemljevidi!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

Razširite prenesen arhiv v imenik Galette `plugins`. Na primer v Linuxu
(zamenjajte `{url}` in `{version}` s pravilnimi vrednostmi):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Inicializacija baze podatkov

Za delovanje ta vtičnik potrebuje več tabel v bazi podatkov. Glejte [Vmesnik za
upravljanje vtičnikov
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

In to je končano; vtičnik Zemljevidi je nameščen :)

## Zemljevid ozadja

> **Opomba** — Nastavitev ponudnika se je pojavila v različici 2.3.0.

Ponudnik je nastavitev iz `Nastavitve zemljevidov` v meniju `Konfiguracija`.

![Nastavitev ponudnika v nastavitvah Zemljevidov](images/tiles_settings.png)

Predlaganih je več ponudnikov:

* **OpenFreeMap, svetlo siva** — privzeto. Vektorske ploščice v diskretni sivi
  barvi, ki omogoča, da oznake članov izstopajo. Brez računa, brez ključa API in
  storitev lahko [samostuje](https://openfreemap.org).
* **OpenFreeMap, barve** — ista storitev, prikazana v vseh barvah.
* **OpenStreetMap** — standardno upodabljanje iz strežnikov OpenStreetMap
  Foundation.
* **OpenStreetMap France** in **Humanitarian OSM Team** — gosti združenje
  OSM-FR; drugi daje večjo težo cestam in objektom.
* **OpenStreetMap Germany** — nemško upodabljanje, prednost lokalnim imenom.
* **Esri, svetlo siva** — upodabljanje v zelo svetlo sivi barvi, podobno temu,
  kar je vtičnik prikazal prej.

### Vaše lastne vrednote

> **Opozorilo** — Preverite politiko uporabe izbranega ponudnika. Večino jih
> vodijo društva ali prostovoljci in postavljajo pogoje glede prometa, ki ga
> sprejemajo.

Zadnji vnos na seznamu, `Vaše lastne vrednosti`, nadomesti predlagane ponudnike
z vašim lastnim naslovom – ponudnikom, ki ni na seznamu, ali vašim lastnim
strežnikom ploščic.

![Polja vnosa Vaše lastne vrednosti](images/tiles_custom.png)

* **Vektorske ploščice** povedo vtičniku, kaj je dobil: slog MapLibre, če je
  označen, klasične rastrske ploščice, če ni.
* **Naslov** je slogovni naslov za vektorske ploščice in naslov ploščic za
  rastrske, kot je `https://tile.openstreetmap.org/{z}/{x}/{y}.png`.
* **Priznanje avtorstva** je kredit, ki ga zahteva ponudnik. HTML je dovoljen.
  To ni formalnost: podatkovne licence so obvezne.
* **Največja povečava** je največja stopnja povečave, ki jo ponuja ponudnik. Če
  greste mimo, se prikažejo prazne ploščice.
* **Poddomene** navaja črke, s katerimi je zamenjan žeton `{s}` naslova, na
  primer `abc`. Samo rastrske ploščice.

## Natančnost pozicioniranja

Shranjena lokacija člana je pogosto njegov domači naslov. Zemljevid je ne
prikazuje v dejanski obliki: lokacije so prilagojene mreži, zato se oznaka
postavi v bližino člana in ne neposredno na njegov prag.

Natančnost je nastavitev v razdelku`Nastavitve zemljevidov ` v meniju
`Konfiguracija `:

* **Približno 100 km**, **približno 50 km**, **približno 5 km** – za regijo,
  departma ali mesto;
* **Približno 500 m** — privzeta vrednost, soseska;
* **Približno 50 m** — ulica;
* **Natančen položaj** – brez pripenjanja, kot se je vtičnik obnašal prej.

Vrednost predstavlja največjo možno oddaljenost označevalca od shranjenega
položaja.

Funkcija »snapping« (poravnava na mrežo) velja tako za obiskovalce kot za člane,
vključno z vodji skupin. Osebje in skrbniki vidijo natančne položaje, vsak član
pa vidi svoj položaj tako, kot ga je shranil. To ne vpliva na stran za določanje
položaja člana: ta prikazuje natančen položaj osebam, ki imajo dovoljenje za
dostop do nje.

Ko zemljevid prikazuje položaje, ki so bili poravnani z določenimi točkami (t.
i. »snapping«), se povečava ustavi na stopnji, ki ustreza natančnosti, tako da
oznaka ni videti, kot da kaže na točen naslov.

## Uporaba vtičnika

Ko je vtičnik nameščen, se v meni Galette, ko je član prijavljen, doda skupina
`Zemljevidi`, ki vsebuje vnos `Moja lokacija`. Ta stran omogoča članu
shranjevanje svoje lokacije.

Pri prikazu člana je dodan tudi gumb `Geolocalize`, ki administratorjem omogoča
nastavitev koordinat člana.

Na seznamu javnih strani je dodan tudi vnos `Zemljevid`, ki prikazuje
geolokalizirane člane, ki so posodobljeni. Administratorji in uslužbenci bodo
videli vse člane, medtem ko bodo preprosti člani in obiskovalci videli le
posodobljene javne.

Najprej bodo člani vnesli svoje koordinate lokacije. Na voljo je več možnosti:

* če je bilo mesto nastavljeno v informacijah o članih, bo predlagan seznam
  možnih krajev (prek [spletne storitve
  Nominatim](https://nominatim.openstreetmap.org)),
* dodatno iskalno območje (zagotovljeno iz
  [OpenStreetMap](https://nominatim.openstreetmap.org/)),
* in tudi gumb za geolokacijo z uporabo zmogljivosti brskalnika.

Iskalno območje se lahko uporablja pri shranjevanju lokacije članov in pri
prikazu zemljevidov.

![Seznam mest, predlaganih za člana](images/towns_list.png)

Član lahko na zemljevidu določi svojo lokacijo (z želeno natančnostjo) z izbiro
enega od predlogov:

![Izbira lokacije na zemljevidu](images/location_select.png)

Z uporabo gumba za geolokalizacijo bo brskalnik določil njegov položaj:

![Gumb za geolokalizacijo](images/geoloc.png)

Nato se lokacija člana prikaže na zemljevidu in jo je mogoče odstraniti:

![Izbrana lokacija, prikazana na zemljevidu](images/location_selected.png)
