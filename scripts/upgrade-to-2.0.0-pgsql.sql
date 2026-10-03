--
-- This file is part of Galette Maps plugin (https://galette.eu).
-- SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
-- SPDX-License-Identifier: GPL-3.0-or-later
--

ALTER TABLE galette_maps_coordinates ALTER COLUMN latitude TYPE decimal(9,6);
ALTER TABLE galette_maps_coordinates ALTER COLUMN longitude TYPE decimal(9,6);