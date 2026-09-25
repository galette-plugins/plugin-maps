---
title: Documentação
description: Member geolocation and public map
---

Este plugin fornece:

* Possibilidade de armazenar coordenadas geográficas para membros (latitude e
  longitude),
* Um mapa público que exibe os membros atualizados que optaram por ser visíveis
  publicamente.

## Instalação

Primeiramente, baixe o plugin:

* [Get latest Maps
  plugin!](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Get Maps plugin nightly
  build!](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

Extraia o arquivo baixado no diretório `plugins` do Galette. Por exemplo, no
Linux (substituindo `{url}` e `{version}` pelos valores corretos):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Inicialização do banco de dados

Para funcionar, este plugin requer várias tabelas no banco de dados. Consulte
[Interface de gerenciamento de plugins do
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

E está concluído; o plugin de mapas está instalado :)

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

Após a instalação do plugin, um grupo chamado "Mapas" é adicionado ao menu
Galette quando o membro faz login, contendo a entrada "Minha localização". Esta
página permite que o membro armazene sua localização.

Um botão "Geolocalizar" também é adicionado ao exibir um membro, permitindo que
os administradores definam as coordenadas do membro.

Além disso, foi adicionada uma entrada de "Mapa" na lista de páginas públicas,
que exibe os membros geolocalizados e atualizados. Administradores e
funcionários verão todos os membros, enquanto membros comuns e visitantes verão
apenas os membros públicos atualizados.

Primeiramente, os membros deverão inserir suas coordenadas de localização.
Diversas opções estão disponíveis:

* Se a cidade tiver sido definida nas informações do membro, será sugerida uma
  lista de possíveis locais (através do serviço online
  [Nominatim](https://nominatim.openstreetmap.org)),
* Além disso, uma zona de pesquisa (fornecida pelo
  [OpenStreetMap](https://nominatim.openstreetmap.org/)),
* e também um botão de geolocalização usando os recursos do navegador.

A zona de pesquisa pode ser usada para salvar a localização dos membros e para
exibir os mapas.

![The list of towns proposed for a member](images/towns_list.png)

Um membro pode definir sua localização (com a precisão desejada) no mapa,
selecionando uma das opções:

![Selecting a location on the map](images/location_select.png)

Usar o botão de geolocalização definirá sua posição a partir do navegador:

![The geolocalize button](images/geoloc.png)

Em seguida, a localização do membro é exibida no mapa e pode ser removida:

![The selected location, displayed on the map](images/location_selected.png)
