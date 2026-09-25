<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteMaps\tests\units;

use Galette\Tests\GaletteTestCase;

/**
 * Color tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class Coordinates extends GaletteTestCase
{
    protected int $seed = 20240517214956;

    /**
     * Cleanup after each test method
     */
    public function tearDown(): void
    {
        $delete = $this->zdb->delete(MAPS_PREFIX . \GaletteMaps\Coordinates::TABLE);
        $this->zdb->execute($delete);
        parent::tearDown();
    }

    /**
     * Test coordinates
     */
    public function testCoordinates(): void
    {
        $member = $this->getMemberOne();
        $coords = new \GaletteMaps\Coordinates();
        $this->assertSame([], $coords->getCoords($member->id));
        $this->assertSame([], $coords->listCoords());

        $this->logSuperAdmin();
        $this->assertSame([], $coords->getCoords($member->id));
        $this->assertSame([], $coords->listCoords());

        //set coordinates for member one
        $this->assertTrue($coords->setCoords($member->id, 50.362038, 3.472998));
        $this->assertEquals(
            [
                'id_adh' => $member->id,
                'latitude' => '50.362038',
                'longitude' => '3.472998'
            ],
            (array)$coords->getCoords($member->id)
        );
        $this->assertEquals(
            [
                [
                    'id_adh' => $member->id,
                    'lat' => '50.362038',
                    'lng' => '3.472998',
                    'name' => 'DURAND René',
                    'nickname' => 'ubertrand'
                ]
            ],
            $coords->listCoords()
        );

        //update coordinates for member one
        $this->assertTrue($coords->setCoords($member->id, 51.362038, 3.572998));

        //remove coordinates for member one
        $this->assertTrue($coords->removeCoords($member->id));
        $this->assertSame([], $coords->getCoords($member->id));
    }

    /**
     * Storing the same position again is not a failure
     */
    public function testSetSamePosition(): void
    {
        $member = $this->getMemberOne();
        $coords = new \GaletteMaps\Coordinates();
        $this->assertTrue($coords->setCoords($member->id, 50.362038, 3.472998));
        $this->assertTrue($coords->setCoords($member->id, 50.362038, 3.472998));
    }
}
