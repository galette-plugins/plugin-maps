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

## Positions precision

A member's stored position is often their home address. The map does not show it
as is: positions are snapped to a grid, so a marker lands near the member, not
on their door.

The precision is a setting from `Maps settings`, in the `Configuration` menu:

* **About 100 km**, **About 50 km**, **About 5 km** — for a region, a
  département or a town;
* **About 500 m** — the default, a neighbourhood;
* **About 50 m** — a street;
* **Exact position** — no snapping, as the plugin behaved before.

The value is how far, at most, a marker may be from the stored position.

Snapping applies to visitors and to members, group managers included. Staff
members and administrators see exact positions, and every member sees their own
position as they stored it. The page used to set a member's position is not
concerned: it shows the exact position to the people allowed to open it.

When the map shows snapped positions, zooming stops at a level matching the
precision, so that a marker does not seem to point at a precise address.

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
