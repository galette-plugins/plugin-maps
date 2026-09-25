---
title: ஆவணமாக்கல்
description: Member geolocation and public map
---

இந்த சொருகி வழங்குகிறது:

* உறுப்பினர்களுக்கான புவியியல் ஆயங்களை சேமிப்பதற்கான நிகழக்கூடிய (அட்சரேகை
  மற்றும் தீர்க்கரேகை),
* பொது வரைபடம், பொதுவில் பார்க்கத் தேர்ந்தெடுக்கப்பட்ட புதுப்பித்த
  உறுப்பினர்களைக் காண்பிக்கும்.

## நிறுவல்

முதலில், சொருகி பதிவிறக்கவும்:

* [Get latest Maps
  plugin!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Get Maps plugin nightly
  build!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

பதிவிறக்கம் செய்யப்பட்ட காப்பகத்தைக் கேலட் `செருகுநிரல்கள்` கோப்பகத்தில்
பிரித்தெடுக்கவும். எடுத்துக்காட்டாக, லினக்சின் கீழ் (`{url}` மற்றும் `{version}`
ஆகியவற்றை சரியான மதிப்புகளுடன் மாற்றுகிறது):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## தரவுத்தள துவக்கம்

வேலை செய்ய, இந்தச் சொருகி தரவுத்தளத்தில் பல அட்டவணைகள் தேவை. காண்க [கேலட்
செருகுநிரல்கள் மேலாண்மை
இடைமுகம்](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

மேலும் இது முடிந்தது; வரைப்படம் செருகுநிரல் நிறுவப்பட்டது :)

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

## சொருகி பயன்பாடு

செருகுநிரல் நிறுவப்பட்டவுடன், ஒரு உறுப்பினர் உள்நுழைந்திருக்கும் போது, கேலட்
பட்டியலில் ஒரு குழு `வரைபடம்' சேர்க்கப்படும், அதில் `எனது இருப்பிடம்` உள்ளீடு
இருக்கும். இந்த பக்கம் உறுப்பினர் அதன் இருப்பிடத்தை சேமிக்க அனுமதிக்கிறது.

ஒரு உறுப்பினரைக் காண்பிக்கும் போது `சியோலோகலைச்` பொத்தானும் சேர்க்கப்படும், இது
உறுப்பினர் ஒருங்கிணைப்புகளை அமைக்க நிர்வாகிகளை அனுமதிக்கிறது.

மேலும், 'வரைபடம்' உள்ளீடு பொதுப் பக்கங்களின் பட்டியலில் சேர்க்கப்பட்டுள்ளது, இது
புதுப்பித்த நிலையில் உள்ள புவியியல்மயமாக்கப்பட்ட உறுப்பினர்களைக் காட்டுகிறது.
நிர்வாகிகள் மற்றும் பணியாளர்கள் அனைத்து உறுப்பினர்களையும் பார்ப்பார்கள், அதே
நேரத்தில் எளிய உறுப்பினர்கள் மற்றும் பார்வையாளர்கள் புதுப்பித்த பொது நபர்களை
மட்டுமே பார்ப்பார்கள்.

முதலில், உறுப்பினர்கள் தங்கள் இருப்பிட ஆயங்களை உள்ளிடுவார்கள். பல விருப்பங்கள்
வழங்கப்படுகின்றன:

* உறுப்பினர் தகவலில் நகரம் அமைக்கப்பட்டிருந்தால், சாத்தியமான இடங்களின் பட்டியல்
  பரிந்துரைக்கப்படும் ([நாமினாடிம் நிகழ்நிலை
  பணி](https://nominatim.openstreetmap.org))
* கூடுதலாக, ஒரு தேடல் மண்டலம்
  ([OpenStreetMap](https://nominatim.openstreetmap.org/) இலிருந்து
  வழங்கப்படுகிறது),
* மேலும் உலாவி திறன்களைப் பயன்படுத்தி புவியியல்மயமாக்கல் பொத்தான்.

உறுப்பினர்களின் இருப்பிடத்தைச் சேமிக்கும் போதும், வரைபடங்களைக் காண்பிக்கும்
போதும் தேடல் மண்டலத்தைப் பயன்படுத்தலாம்.

![The list of towns proposed for a member](images/towns_list.png)

ஒரு உறுப்பினர் அதன் இருப்பிடத்தை (அவர் விரும்பும் துல்லியத்துடன்) வரைபடத்தில்
முன்மொழிவுகளில் ஒன்றைத் தேர்ந்தெடுக்கலாம்:

![Selecting a location on the map](images/location_select.png)

புவிசார்மயமாக்கல் பொத்தானைப் பயன்படுத்துவது உலாவியில் இருந்து அதன் நிலையை
வரையறுக்கும்:

![The geolocalize button](images/geoloc.png)

பின்னர், உறுப்பினர் இருப்பிடம் வரைபடத்தில் காட்டப்படும், மேலும் அகற்றலாம்:

![The selected location, displayed on the map](images/location_selected.png)
