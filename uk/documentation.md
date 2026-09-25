---
title: Документація
description: Member geolocation and public map
---

Це розширення надає:

* можливість зберігати географічні координати для членів (широта та довгота),
* прилюдна мапа, на якій показані актуальні члени, яких обрали як прилюдно
  видимих.

## Встановлення

Перш за все, завантажте плагін:

* [Get latest Maps
  plugin!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Get Maps plugin nightly
  build!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

Розпакуйте завантажений архів у каталог Galette `plugins`. Наприклад, під Linux
(замінивши `{url}` і `{version}` на правильні значення):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Ініціалізація бази даних

Для роботи цього плагіна потрібно кілька таблиць у базі даних. Перегляньте
[Інтерфейс керування плагінами
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

Усе завершено. Розширення "Maps" установлено :)

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

## Використання розширення

Коли розширення встановлено, група `Maps` додається до меню Galette, коли член
увійшов у систему, яка містить поле `Моє розташування`. Ця сторінка дозволяє
членові зберігати своє місцеперебування.

Кнопка `Geolocalize` також додається при відображенні члена, що дозволяє
адміністраторам встановлювати координати членів.

Також до списку прилюдних сторінок додано запис `Мапа`, який відображає
геолокалізованих актуальних членів. Адміністратори та співробітники бачитимуть
усіх членів, тоді як прості члени та відвідувачі бачитимуть лише оновлених
прилюдних.

Перш за все, члени вводять координати свого місцеперебування. Надано кілька
варіантів:

* Якщо місто було вказано у даних члена, то буде запропонований список можливих
  місць (через [Онлайн-сервіс Nominatim](https://nominatim.openstreetmap.org)),
* крім того, зона пошуку (надана з
  [OpenStreetMap](https://nominatim.openstreetmap.org/)),
* а також кнопка геолокалізації за допомогою можливостей браузера.

Зона пошуку може бути використана під час збереження місцеперебування членів та
під час відображення мап.

![The list of towns proposed for a member](images/towns_list.png)

Член може визначити своє місце розташування (з бажаною точністю) на мапі,
вибравши одну з пропозицій:

![Selecting a location on the map](images/location_select.png)

За допомогою кнопки геолокалізації буде визначено його розташування з браузера:

![The geolocalize button](images/geoloc.png)

Потім місцеперебування члена відображається на мапі та може бути видалено:

![The selected location, displayed on the map](images/location_selected.png)
