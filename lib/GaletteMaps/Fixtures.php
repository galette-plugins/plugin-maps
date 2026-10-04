<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteMaps;

use Galette\Core\Plugins\FixturesContext;
use Galette\Core\Plugins\FixturesProviderInterface;

/**
 * Sample positions for fixture members, run by galette:seed-fixtures
 *
 * No geocoding: towns positions are known, members are spread around them.
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class Fixtures implements FixturesProviderInterface
{
    /** @var list<array{0: float, 1: float}> Towns latitude and longitude */
    private const array TOWNS = [
        [48.856614, 2.352222],  //Paris
        [45.764043, 4.835659],  //Lyon
        [43.296482, 5.369780],  //Marseille
        [50.629250, 3.057256],  //Lille
        [44.837789, -0.579180], //Bordeaux
        [47.218371, -1.553621], //Nantes
        [48.573405, 7.752111],  //Strasbourg
        [43.604652, 1.444209],  //Toulouse
        [48.117266, -1.677793], //Rennes
        [47.322047, 5.041480],  //Dijon
        [43.710173, 7.261953],  //Nice
        [45.188529, 5.724524],  //Grenoble
    ];

    /**
     * Locate two members out of three
     *
     * @param FixturesContext $context Fixtures context
     */
    public function seedFixtures(FixturesContext $context): string
    {
        $coordinates = new Coordinates($context->zdb, $context->login);
        $count = 0;
        foreach (array_values($context->members) as $position => $id_adh) {
            if ($position % 3 === 2) {
                continue;
            }

            [$latitude, $longitude] = self::TOWNS[$position % count(self::TOWNS)];
            //spread members of a same town, within a few kilometers
            $coordinates->set(
                $id_adh,
                $latitude + ((($position * 37) % 21) - 10) / 300,
                $longitude + ((($position * 53) % 21) - 10) / 300
            );
            ++$count;
        }

        return sprintf('Located %d members', $count);
    }

    /**
     * Remove fixture members positions
     *
     * @param FixturesContext $context Fixtures context
     */
    public function cleanFixtures(FixturesContext $context): void
    {
        $coordinates = new Coordinates($context->zdb, $context->login);
        foreach ($context->members as $id_adh) {
            $coordinates->remove($id_adh);
        }
    }
}
