---
title: Documentazione
description: Member geolocation and public map
---

Questo componente aggiuntivo fornisce:

* possibilità di memorizzare coordinate geografiche per i membri (latitudine e
  longitudine),
* una mappa pubblica aggiornata che mostra i membri che hanno scelto di essere
  pubblicamente visibili.

## Installazione

Prima di tutto, scaricare il plugin:

* [Get latest Maps
  plugin!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Get Maps plugin nightly
  build!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

Estrarre l'archivio scaricato nella directory `plugins` di Galette. Ad esempio,
su Linux (sostituendo `{url}` e `{version}` con i valori corretti):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Inizializzazione database

Per funzionare, questo plugin richiede diverse tabelle nel database. Vedere
[Interfaccia di gestione dei plugin
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

E questo è finito; il plugin Maps è stato installato :)

## Background map

> **Note** — The provider setting appeared in version 2.3.0.

The provider is a setting from `Maps settings`, in the `Configuration` menu.

![The provider setting, in Maps settings](images/tiles_settings.png)

Several providers are proposed:

* **OpenFreeMap, light grey** — the default. Vector tiles, in a discreet grey
  that lets member markers stand out. No account, no API key, and the service
  can be [self-hosted](https://openfreemap.org).
* **OpenFreeMap, colours** — the same service, rendered in full colour.
* **OpenStreetMap** — the standard rendering, from the OpenStreetMap Foundation
  servers.
* **OpenStreetMap France** and **Humanitarian OSM Team** — hosted by the OSM-FR
  association; the second one gives more weight to roads and facilities.
* **OpenStreetMap Germany** — German rendering, favouring local names.
* **Esri, light grey** — a very light grey rendering, close to what the plugin
  displayed before.

### Your own values

> **Warning** — Check the usage policy of the provider you choose. Most of them
> are run by associations or by volunteers, and they set conditions on the
> traffic they accept.

The last entry of the list, `Your own values`, replaces the proposed providers
with an address of your own — a provider that is not listed, or your own tile
server.

![The fields of the Your own values entry](images/tiles_custom.png)

* **Vector tiles** tells the plugin what it is being given: a MapLibre style
  when ticked, classic raster tiles when not.
* **Address** is the style address for vector tiles, and the tiles address for
  raster ones, such as `https://tile.openstreetmap.org/{z}/{x}/{y}.png`.
* **Attribution** is the credit the provider requires. HTML is allowed. It is
  not a formality: data licences make it mandatory.
* **Maximum zoom** is the deepest zoom level the provider serves. Going past it
  displays empty tiles.
* **Subdomains** lists the letters the `{s}` token of the address is replaced
  with, for instance `abc`. Raster tiles only.

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

## Plugin usage

A plugin installato, si aggiunge il gruppo `Maps` al menu di Galette
ogniqualvolta che un membro si connette, contenente la voce `La mia posizione`.
Questa pagina consente all'utente di memorizzare la sua posizione.

Viene inoltre aggiunto un pulsante `Geolocalizza` durante la visualizzazione di
un membro, che consente agli amministratori di impostare le coordinate dei
membri.

Inoltre, nell'elenco delle pagine pubbliche viene aggiunta una voce "Mappa" che
mostra i membri geolocalizzati aggiornati. Gli amministratori e i membri dello
staff vedranno tutti i membri, mentre i membri semplici e i visitatori vedranno
solo quelli pubblici aggiornati.

Prima di tutto, i membri entreranno nelle loro coordinate di posizione. Sono
disponibili diverse opzioni:

* se la città è stata inserita nelle informazioni dei membri, verrà proposto un
  elenco dei posti possibili (tramite il [servizio online
  Nominatim](https://nominatim.openstreetmap.org)),
* inoltre, una zona di ricerca (fornita da
  [OpenStreetMap](https://nominatim.openstreetmap.org/)),
* e anche un pulsante geolocalizza utilizzando le capacità del browser.

La zona di ricerca può essere utilizzata durante il salvataggio della posizione
dei membri e durante la visualizzazione delle mappe.

![The list of towns proposed for a member](images/towns_list.png)

Un membro può definire la sua posizione (con la precisione che vuole) sulla
mappa selezionando una delle proposizioni:

![Selecting a location on the map](images/location_select.png)

Utilizzando il pulsante di geolocalizzazione verrà definita la sua posizione dal
browser:

![The geolocalize button](images/geoloc.png)

Poi, la posizione del membro viene visualizzata sulla mappa e può essere
rimossa:

![The selected location, displayed on the map](images/location_selected.png)
