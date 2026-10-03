--
-- This file is part of Galette Maps plugin (https://galette.eu).
-- SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
-- SPDX-License-Identifier: GPL-3.0-or-later
--

DROP TABLE IF EXISTS galette_maps_coordinates CASCADE;
CREATE TABLE galette_maps_coordinates (
    id_adh int(10) unsigned NOT NULL,
    latitude decimal(9,6) NOT NULL,
    longitude decimal(9,6) NOT NULL,
    PRIMARY KEY (id_adh),
    FOREIGN KEY (id_adh) REFERENCES galette_adherents (id_adh) ON DELETE CASCADE ON UPDATE CASCADE
);
