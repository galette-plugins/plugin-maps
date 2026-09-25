---
title: Documentation
description: Member geolocation and public map
---

Bu eklenti şunları sağlar:

* üyelerin coğrafi koordinatlarını (enlem ve boylam) saklama imkanı,
* kamuya açık bir harita, kamuya açık olmayı seçmiş güncel üyeleri gösterir.

## Kurulum

Öncelikle, eklentiyi indirin:

* [Get latest Maps
  plugin!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Get Maps plugin nightly
  build!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

İndirilen arşivi Galette `plugins` dizinine çıkarın. Örneğin, Linux altında
(`{url}` ve `{version}` değerlerini doğru değerlerle değiştirerek):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Veritabanı başlatma

Çalışabilmesi için, bu eklenti veritabanında birkaç tablo gerektirir. Bkz.
[Galette eklenti yönetim
arayüzü](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

Ve bu işlem tamamlandı; Haritalar eklentisi yüklendi :)

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

## Eklenti kullanımı

Eklenti yüklendiğinde, üye oturum açtığında Galette menüsüne `Haritalar` grubu
eklenir ve bu grup `Benim konumum` girişini içerir. Bu sayfa, üyenin konumunu
kaydetmesine olanak tanır.

Bir üye görüntülenirken, yöneticilerin üye koordinatlarını ayarlamasına olanak
tanıyan bir `Geolocalize` düğmesi de eklenmiştir.

Ayrıca, güncel coğrafi konum bilgisi olan üyeleri gösteren bir `Harita` girişi
genel sayfalar listesine eklenmiştir. Yöneticiler ve personel tüm üyeleri
görebilirken, basit üyeler ve ziyaretçiler yalnızca güncel genel üyeleri
görebilir.

Öncelikle, üyeler konum koordinatlarını gireceklerdir. Birkaç seçenek
sunulmaktadır:

* üye bilgilerinde kasaba ayarlanmışsa, olası yerlerin bir listesi önerilecektir
  ([Nominatim çevrimiçi hizmeti](https://nominatim.openstreetmap.org)
  aracılığıyla),
* ek olarak, bir arama bölgesi
  ([OpenStreetMap](https://nominatim.openstreetmap.org/) tarafından
  sağlanmaktadır),
* ve ayrıca tarayıcı özelliklerini kullanan bir coğrafi konum belirleme düğmesi.

Arama bölgesi, üyelerin konumunu kaydederken ve haritaları görüntülerken
kullanılabilir.

![The list of towns proposed for a member](images/towns_list.png)

Bir üye, harita üzerinde konumunu (istediği hassasiyetle) aşağıdaki
seçeneklerden birini seçerek tanımlayabilir:

![Selecting a location on the map](images/location_select.png)

Coğrafi konum belirleme düğmesini kullanarak tarayıcıdan konumunu
belirleyebilirsiniz:

![The geolocalize button](images/geoloc.png)

Ardından, üye konumu haritada görüntülenir ve kaldırılabilir:

![The selected location, displayed on the map](images/location_selected.png)
