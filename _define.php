<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

/** @var \Galette\Core\Plugins $this */
$this->register(
    name: 'Galette Maps',     //Name
    desc: 'Maps features',    //Short description
    author: 'Johan Cwiklinski', //Author
    version: '2.3.0',            //Version
    compver:  '1.3.0',            //Galette compatible version
    route: 'maps',             //routing name and translation domain
    date: '2026-09-30',       //Release date
    acls: [   //Permissions needed
        'maps_localize_member'   => 'member',
        'maps_mymap'             => 'member',
        'maps_ilivehere'         => 'member',
        'maps_preferences'       => 'admin',
        'maps_store_preferences' => 'admin'
    ],
    dbver: 1.00
);
