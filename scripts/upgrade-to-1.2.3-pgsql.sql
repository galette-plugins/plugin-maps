--
-- This file is part of Galette Maps plugin (https://galette.eu).
-- SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
-- SPDX-License-Identifier: GPL-3.0-or-later
--

ALTER TABLE galette_maps_coordinates DROP CONSTRAINT galette_maps_coordinates_id_adh_fkey;
ALTER TABLE galette_maps_coordinates ADD CONSTRAINT galette_maps_coordinates_id_adh_fkey FOREIGN KEY (id_adh) REFERENCES galette_adherents(id_adh) ON DELETE CASCADE ON UPDATE CASCADE;
