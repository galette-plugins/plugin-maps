--
-- This file is part of Galette Maps plugin (https://galette.eu).
-- SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
-- SPDX-License-Identifier: GPL-3.0-or-later
--

ALTER TABLE galette_maps_coordinates CHANGE latitude latitude decimal(9,6) NOT NULL;
ALTER TABLE galette_maps_coordinates CHANGE longitude longitude decimal(9,6) NOT NULL;