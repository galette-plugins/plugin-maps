---
title: Galette Maps
description: Member geolocation and public map
---

A [Galette](https://galette.eu) plugin to geolocalize members: it stores
geographical coordinates for each member, and displays the ones who agreed to be
publicly visible on a map.

Maps are rendered with [MapLibre GL](https://maplibre.org/) from
[OpenFreeMap](https://openfreemap.org/) vector tiles by default — no API key, no
registration, and the whole thing can be self-hosted. An administrator picks
another provider, or enters their own address, from Configuration. Search is
provided by the [Nominatim](https://nominatim.openstreetmap.org/) API.

* [documentation](documentation.html)
* [bugs and features](https://bugs.galette.eu/projects/galette-plugin-maps)
* [source code](https://github.com/galette-plugins/plugin-maps)
* mailing lists:
  [users](https://lists.mailman3.com/postorius/lists/galette-users.mailman3.com/),
  [developers](https://lists.mailman3.com/postorius/lists/galette-devel.mailman3.com/)

To use it you need a recent enough Galette — the required version is shown at
the top of this page — and the plugin itself, either by:

* downloading it with the buttons at the top of this page,
* or using the [source code from the
  repository](https://github.com/galette-plugins/plugin-maps), which requires
  some technical skills.

See the [documentation](documentation.html) for the installation steps.
