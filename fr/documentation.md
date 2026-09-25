---
title: Documentation
description: Géolocalisation des membres et carte publique
---

Ce plugin fournit :

* la possibilité d'enregistrer les coordonnées géographiques des adhérents
  (latitude et longitude),
* une carte publique pour afficher les membres à jour qui ont choisi d'être
  affichés publiquement.

## Installation

Tout d'abord, téléchargez le plugin :

* [Obtenir le dernier plugin Maps
  !](https://github.com/galette-plugins/plugin-maps/releases/latest)
* [Obtenir la nightly du plugin Maps
  !](https://github.com/galette-plugins/plugin-maps/releases/tag/nightly)

Extrayez l'archive téléchargée dans le dossier `plugins` de Galette. Par
exemple, sous linux (en remplaçant `{url}` et `{version}` par les valeurs
adéquates) :

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-maps-{version}.tar.bz2
```

## Initialisation de la base de données

Pour fonctionner, ce plugin requiert des tables dans la base de données.
Référez-vous [à l'interface de gestion des plugins de
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

Et c'est terminé, le plugin Maps est installé :)

## Fond de carte

> **Note** — Le paramètre du fournisseur apparaît dans la version 2.3.0.

Le fournisseur est un paramètre depuis `Configuration Maps`, dans le menu
`Configuration`.

![Le réglage du fournisseur, dans les paramètres
Maps](images/tiles_settings.png)

Plusieurs fournisseurs sont proposés :

* **OpenFreeMap, gris clair** — par défaut. Les tuiles vectorisées, dans un gris
  discret qui permet aux marqueurs membres de se démarquer. Aucun compte, aucune
  clé API, et le service peut être [auto-hébergé](https://openfreemap.org).
* **OpenFreeMap, couleurs** — le même service, rendu en couleur.
* **OpenStreetMap** — le rendu standard, depuis serveurs de la fondation
  OpenStreetMap.
* **OpenStreetMap France** et **Équipe Humanitaire OSM** - hébergés par
  l'association OSM-FR ; le second donne plus de poids aux routes et aux
  installations.
* **OpenStreetMap Allemagne** — Le rendu allemand, en faveur des noms locaux.
* **Esri, gris clair** — un rendu gris très clair, proche de ce que le plugin
  affichait avant.

### Vos propres valeurs

> **Avertissement** — Vérifiez la politique d'utilisation du fournisseur que
> vous choisissez. La plupart d'entre eux sont gérés par des associations ou des
> bénévoles, et ils établissent des conditions sur le trafic qu'ils acceptent.

La dernière entrée de la liste, « Vos propres valeurs », remplace les
fournisseurs proposés par votre propre adresse — un fournisseur qui n'est pas
répertorié, ou votre propre serveur de tuiles.

![Les champs de l'entrée Vos propres valeurs](images/tiles_custom.png)

* **Tuiles vectorisées** configure le plugin pour ce qu'il va recevoir : un
  style MapLibre si coché, des tuiles classiques sinon.
* **Adresse** est l'adresse de style pour les tuiles vectorisées, et l'adresse
  des tuiles classiques, comme `https://tile.openstreetmap.org/{z}/{x}/{y}.png`.
* **Attribution** est le crédit demandé par le fournisseur. Le HTML est
  autorisé. Ce n'est pas une formalité : les licences le rendent obligatoire.
* **Zoom maximal** est le niveau de zoom le plus profond que le fournisseur
  serve. Après ça il affiche des tuiles vides.
* **Les sous-domaines** Lettres par lesquelles le jeton `{s}` dans l'adresse est
  remplacé, telles que abc. Tuiles classiques uniquement.

## Précision de la position

La localisation d'un adhérent est souvent son adresse personnelle. La carte ne
le montre pas comme tel : les positions sont attachées à une grille, donc un
marqueur atterrit près de l'adhérent, pas au pas de sa porte.

La précision est un paramètre depuis `Configuration Maps`, dans le menu
`Configuration` :

* **À environ 100 km**, **À environ 50 km**, **À environ 5 km** — pour une
  région, un département ou une ville ;
* **À environ 500 m** — par défaut, un quartier ;
* **À environ 50 m** — une rue ;
* **Position exacte** — pas d'adaptation, comme le faisait le plugin auparavant.

La valeur est à quelle distance, au plus, un marqueur peut être de la position
stockée.

L'adaptation s'applique aux visiteurs et aux adhérents, gestionnaires de groupe
inclus. Les membres du bureau et les administrateurs voient les localisations
exactes, et chaque adhérent voit sa propre position comme il l'a stockée. La
page utilisée pour définir la position d'un membre n'est pas concernée : elle
montre la position exacte des personnes autorisées à l'ouvrir.

Lorsque la carte montre des positions adaptées, le zoom s'arrête à un niveau
correspondant à la précision, de sorte qu'un marqueur ne semble pas pointer à
une adresse précise.

## Utilisation du plugin

Lorsque le plugin est installé, un groupe `Cartes` est ajouté au menu de Galette
lorsqu'un adhérent est connecté, qui contient l'entrée `Ma localisation`. Cette
page permet à l'adhérent d'enregistrer sa localisation.

Un bouton `Géolocaliser` est également ajouté à l'affichage d'une fiche
adhérent, qui permet aux administrateurs de définir les coordonnées de
l'adhérent.

De plus, une entrée `Carte` est ajoutée à la liste des pages publiques, qui
affiche la géolocalisation des adhérents à jour. Les administrateurs et membres
du bureau verront tous les adhérents, alors que les simples adhérents et les
visiteurs verront uniquement ceux à jour et publics.

Tout d'abord, les adhérents entreront leurs coordonnées géographiques. Plusieurs
options sont fournies :

* si la ville a été renseignée dans la fiche de l'adhérent, une liste des
  localisations possibles sera proposée (via [le service en ligne
  Nominatim](https://nominatim.openstreetmap.org)),
* en complément, une zone de recherche (fournie depuis
  [OpenStreetMap](https://nominatim.openstreetmap.org/)),
* et également une bouton de géolocalisation qui utilise les possibilités du
  navigateur.

La zone de recherche peut être utilisée lorsque vous renseignez la localisation
d'un adhérent, et aussi à l'affichage des cartes.

![La liste des villes proposées pour un adhérent](images/towns_list.png)

Un adhérent peut définir a propre localisation (avec la précision qu'il
souhaite) sur la carte en sélectionnant une des propositions :

![Sélectionner un emplacement sur la carte](images/location_select.png)

Utiliser le bouton géolocalisation déterminera sa position depuis son navigateur
:

![Le bouton géolocaliser](images/geoloc.png)

Alors, la localisation de l'adhérent est affichée sur la carte, et peut être
supprimée :

![L'emplacement sélectionné, affiché sur la carte](images/location_selected.png)
