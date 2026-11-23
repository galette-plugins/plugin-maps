---
title: Galette Maps
description: Géolocalisation des membres et carte publique
---

Un plugin [Galette](https://galette.eu) pour géolocaliser les adhérents : il
stocke les coordonnées géographiques de chaque adhérent, et affiche ceux qui ont
accepté que leur profil soit publiquement visible sur une carte.

Le rendu des cartes est assuré par [MapLibre GL](https://maplibre.org/) de
[OpenFreeMap](https://openfreemap.org/) tuiles vectorielles par défaut — pas de
clé API, pas d'enregistrement, et l'ensemble peut-être auto-hébergé. Un
administrateur peut sélectionner un autre fournisseur, ou entrer leur propre
adresse, depuis la configuration. La recherche est fournie via l'API
[Nominatim](https://nominatim.openstreetmap.org/).

* [documentation](documentation.html)
* [bogues et
  fonctionnalités](https://bugs.galette.eu/projects/galette-plugin-maps)
* [code source](https://github.com/galette-plugins/plugin-maps)
* listes de diffusion :
  [utilisateurs](https://lists.mailman3.com/postorius/lists/galette-users.mailman3.com/),
  [développeurs](https://lists.mailman3.com/postorius/lists/galette-devel.mailman3.com/)

Pour l'utiliser, vous avez besoin d'une Galette assez récente — la version
requise est affichée en haut de cette page — et le plugin lui-même, soit par :

* le télécharger à l'aide des boutons en haut de la page,
* ou utiliser le [code source depuis le
  dépôt](https://github.com/galette-plugins/plugin-maps), ce qui demandes
  quelques compétences techniques.

Voir la [documentation](documentation.html) pour les étapes d'installation.
