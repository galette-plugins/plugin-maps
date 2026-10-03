# Quick fix — "API KEY REQUIRED" tiles

## Symptom

Every map in the plugin shows the **"API KEY REQUIRED —
carto.com/basemaps/apikey"** watermark across the tiles.

## Cause

The background map comes from CARTO (`basemaps.cartocdn.com`), which now
requires an API key for its basemaps.

## Fix

A single file to edit:

```
galette/plugins/plugin-maps/templates/default/common_scripts.html.twig
```

### The diff

```diff
--- a/templates/default/common_scripts.html.twig
+++ b/templates/default/common_scripts.html.twig
@@ -132,9 +132,9 @@
         }).addTo(map);
 {% endif %}
 
-        L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
-            maxZoom: 18,
-            attribution: '{{ _T("Map data (c)", "maps")|e("js") }} <a href="http://openstreetmap.org">{{ _T("OpenStreetMap contributors", "maps")|e("js") }}</a>, {{ _T("Imagery (c)", "maps")|e("js") }} <a href="https://cartodb.com/attributions">CartoDB</a>'
+        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
+            maxZoom: 19,
+            attribution: '{{ _T("Map data (c)", "maps")|e("js") }} <a href="https://openstreetmap.org">{{ _T("OpenStreetMap contributors", "maps")|e("js") }}</a>'
         }).addTo(map);
 
         try {
```

Copy the diff above into a file (`tiles-quickfix.diff` for example) at plugin top directory, and apply it from there with:

```
patch -p1 < tiles-quickfix.diff
```

### Clearing the Twig cache — mandatory

**Without this step, the change has no effect**: Galette serves compiled
templates and does not notice that a `.twig` file has changed.

Back at Galette top directory:

```
rm -rf galette/data/cache/*/templates/
```

The directory usually belongs to the web server user (`www-data`, `apache`), so
the command has to be run as `root` or through `sudo`. It is rebuilt on its own
on the next visit; neither a restart nor a reload of the web server is needed.

## Other possible background maps

Every URL below has been tested and answers without an API key. The
`attribution` block is **mandatory**: keeping it is not a formality, it is the
condition of the data licence.

### OpenStreetMap — the standard rendering

The one used in the fix above. The simplest, the best known. It renders in
colour, where CARTO `light_all` was light grey: the Galette markers stand out
slightly less.

```js
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
```

Worth knowing: the OSMF usage policy does cover low traffic sites — which an
association's Galette instance is — but explicitly asks *not* to hardcode the
tile URL in distributed software. Which is exactly the trap CARTO has just
sprung on us.

### OpenStreetMap France

Servers of the OSM-FR association, French rendering, one more zoom level.

```js
L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
    maxZoom: 20,
    subdomains: 'abc',
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> — OSM-FR tiles'
}).addTo(map);
```

### Humanitarian OSM Team

Same host as OSM-FR, more contrasted style, roads and facilities are very
readable.

```js
L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
    maxZoom: 20,
    subdomains: 'abc',
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> — <a href="https://www.hotosm.org/">HOT</a> tiles'
}).addTo(map);
```

### OpenStreetMap.de

German rendering, local names favoured. Beware, it stops at zoom 18: beyond
that the server answers a 404 (checked).

```js
L.tileLayer('https://tile.openstreetmap.de/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
```

### Esri World Light Gray Base

Visually the closest to what the plugin used to display: very light grey, almost
without colour, the markers stand out perfectly. Note the unusual `{z}/{y}/{x}`
order, and the JPEG format.

```js
L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 16,
    attribution: 'Tiles &copy; Esri — Esri, DeLorme, NAVTEQ'
}).addTo(map);
```

One reservation: this is a commercial vendor's courtesy service, with no
availability commitment. Nothing guarantees it will not do tomorrow what CARTO
has just done.

### Staying on CARTO

That is possible, but it now means opening an account on
<https://carto.com/basemaps> and adding the key to the URL. The exact form of
the URL with a key is given by their interface — we do not reproduce it here, it
is specific to each account.

## Newer versions

From the next release the background map is a setting: Configuration holds every
provider listed above as a ready-made choice, plus your own address, attribution
and maximum zoom. Editing a template by hand, as described here, is only needed
on the releases that came before it.
