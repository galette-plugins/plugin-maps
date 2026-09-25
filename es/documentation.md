---
title: Documentación
description: Member geolocation and public map
---

Este complemento proporciona:

* posibilidad de almacenar las coordenadas geográficas de los miembros (latitud
  y longitud),
* un mapa público que muestra a los miembros actualizados que han elegido ser
  visibles públicamente.

## Instalación

Antes que todo, descargue el complemento:

* [Get latest Maps
  plugin!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Get Maps plugin nightly
  build!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

Extraer el archivo descargado en el directorio de Galette `plugins`. Por
ejemplo, bajo linux (reemplazar `{url}` y `{version}` con valores correctos):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Inicio de la base de datos

Para funcionar, este plugin requiere varias tablas en la base de datos. Ver
[Interfaz de gestión de plugins
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

Y esto está terminado; el plugin de Mapas está instalado :)

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

Cuando se instala el plugin, se añade un grupo `Mapas` al menú de Galette cuando
un miembro está conectado, que contiene la entrada `Mi ubicación`. Esta página
permite a los miembros almacenar su ubicación.

También se ha añadido un botón de `Geolocalización` al mostrar un miembro, que
permite a los administradores establecer las coordenadas del miembro.

Además, se ha añadido una entrada `Mapa` en la lista de páginas públicas, que
muestra los miembros geolocalizados que están al día. Los administradores y los
miembros del personal verán todos los miembros, mientras que los miembros
simples y los visitantes sólo verán los miembros públicos actualizados.

En primer lugar, los miembros introducirán las coordenadas de su ubicación. Se
ofrecen varias opciones:

* si se ha fijado la ciudad en la información de los miembros, se propondrá una
  lista de posibles lugares (a través del servicio online
  [Nominatim](https://nominatim.openstreetmap.org)),
* además, una zona de búsqueda (proporcionada por
  [OpenStreetMap](https://nominatim.openstreetmap.org/)),
* y también un botón de geolocalización mediante las capacidades del navegador.

La zona de búsqueda se puede utilizar cuando se guarda la ubicación de los
miembros, y cuando se muestran los mapas.

![The list of towns proposed for a member](images/towns_list.png)

Un miembro puede definir su ubicación (con la precisión que desee) en el mapa
seleccionando una de las proposiciones:

![Selecting a location on the map](images/location_select.png)

Utilizando el botón de geolocalización se definirá su posición desde el
navegador:

![The geolocalize button](images/geoloc.png)

A continuación, la ubicación de los miembros se muestra en el mapa, y puede ser
eliminada:

![The selected location, displayed on the map](images/location_selected.png)
