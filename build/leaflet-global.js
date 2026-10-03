/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

// Leaflet is already loaded by maps-main.bundle.min.js; hand the bundler that
// instance instead of a second copy, or the plugin would extend a Leaflet the
// page never sees.
module.exports = window.L;
