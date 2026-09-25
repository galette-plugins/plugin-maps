/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

const gulp = require('gulp');

const { series, parallel } = require('gulp');
const del = require('del');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const merge = require('ordered-read-streams');
const replace = require('gulp-replace');
const cleancss = require('gulp-clean-css');
const esbuild = require('esbuild');

const plugin = {
  'public': './webroot'
}

const main_styles = [
  './node_modules/leaflet/dist/leaflet.css',
  './node_modules/leaflet.fullscreen/dist/Control.FullScreen.css',
  './node_modules/leaflet-gesture-handling/dist/leaflet-gesture-handling.css',
  './node_modules/leaflet.markercluster/dist/MarkerCluster.css',
  './node_modules/leaflet.markercluster/dist/MarkerCluster.Default.css',
  './node_modules/leaflet-control-geocoder/dist/Control.Geocoder.css',
  './node_modules/leaflet-legend/leaflet-legend.css'
];

const main_scripts = [
  './node_modules/leaflet/dist/leaflet.js',
  './node_modules/leaflet.fullscreen/dist/Control.FullScreen.umd.js',
  './node_modules/leaflet-gesture-handling/dist/leaflet-gesture-handling.min.js',
  './node_modules/leaflet.markercluster/dist/leaflet.markercluster.js',
  './node_modules/leaflet-control-geocoder/dist/Control.Geocoder.js',
  './node_modules/leaflet-legend/leaflet-legend.js'
];

const gl_styles = [
  './node_modules/maplibre-gl/dist/maplibre-gl.css'
];

// maplibre-gl ships ES modules only since 6.x, so its bundle cannot be built by
// concatenation like the others: esbuild rolls it up, along with the Leaflet
// binding, into a classic script still exposing the `maplibregl` global.
const gl_entry = './build/maps-gl.js';
// Leaflet comes from maps-main.bundle.min.js, so the bundle must reuse the one
// on the page instead of embedding a second copy.
const gl_aliases = {
  'leaflet': './build/leaflet-global.js'
};
// maplibre-gl runs its tile parsing in a worker it fetches at runtime; it has to
// be a file of its own, next to the bundle.
const gl_worker = './node_modules/maplibre-gl/dist/maplibre-gl-worker.mjs';

const main_assets = [
  {
    'src': './node_modules/leaflet/dist/images/*',
    'dest': '/images/'
  },
  {
    'src': './node_modules/leaflet.locatecontrol/*.svg',
    'dest': '/images/'
  },
  {
    'src': './node_modules/leaflet.fullscreen/*.svg',
    'dest': '/images/'
  }
];

function clean(cb) {
  assets = [
    plugin.public + '/**',
    '!' + plugin.public,
    '!' + plugin.public + '/galette_maps.css',
    '!' + plugin.public + '/galette_maps.js',
    plugin.public + '/images/**',
    '!' + plugin.public + '/images',
    '!' + plugin.public + '/images/marker-galette.png',
    '!' + plugin.public + '/images/marker-galette-pro.png',
  ];
  return del(assets, cb);
};

function styles() {
  main = gulp.src(main_styles)
    .pipe(replace('icon-fullscreen.svg', './images/icon-fullscreen.svg'))
    .pipe(cleancss())
    .pipe(concat('maps-main.bundle.min.css'))
    .pipe(gulp.dest(plugin.public));

    locate = gulp.src([
      './node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.css'
    ])
    .pipe(replace('../location-arrow-solid.svg', './images/location-arrow-solid.svg'))
    .pipe(replace('../spinner-solid.svg', './images/spinner-solid.svg'))
    .pipe(cleancss())
    .pipe(concat('maps-locate.bundle.min.css'))
    .pipe(gulp.dest(plugin.public));

    gl = gulp.src(gl_styles)
    .pipe(cleancss())
    .pipe(concat('maps-gl.bundle.min.css'))
    .pipe(gulp.dest(plugin.public));

  return merge(main, locate, gl);
};

function scripts() {
  main = gulp.src(main_scripts)
    .pipe(concat('maps-main.bundle.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest(plugin.public));

    locate = gulp.src([
      './node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.js'
    ])
    .pipe(concat('maps-locate.bundle.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest(plugin.public));

  return merge(main, locate);
};

function gl_scripts() {
  return Promise.all([
    esbuild.build({
      entryPoints: [gl_entry],
      outfile: plugin.public + '/maps-gl.bundle.min.js',
      bundle: true,
      minify: true,
      format: 'iife',
      globalName: 'maplibregl',
      alias: gl_aliases
    }),
    esbuild.build({
      entryPoints: [gl_worker],
      outfile: plugin.public + '/maps-gl.worker.min.js',
      bundle: true,
      minify: true,
      format: 'esm'
    })
  ]);
};

function assets() {
  main = main_assets.map(function (asset) {
    return gulp.src(asset.src, {encoding: false})
      .pipe(gulp.dest(plugin.public + asset.dest));
    }
  );

  return merge(main);
};

exports.clean = clean;

exports.styles = styles;
exports.scripts = scripts;
exports.gl_scripts = gl_scripts;
exports.assets = assets;

exports.build = series(styles, scripts, gl_scripts, assets);
exports.default = exports.build;
