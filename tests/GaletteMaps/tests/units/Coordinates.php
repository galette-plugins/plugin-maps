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

    /**
     * Set member visibility fields
     *
     * @param int  $id_adh   Member ID
     * @param bool $active   Is member active
     * @param bool $public   Does member display its information
     * @param bool $uptodate Is member up to date
     */
    private function setVisibility(int $id_adh, bool $active, bool $public, bool $uptodate): void
    {
        $bool = fn(bool $value): \Laminas\Db\Sql\Expression => new \Laminas\Db\Sql\Expression($value ? 'true' : 'false');
        $update = $this->zdb->update(\Galette\Entity\Adherent::TABLE);
        $update->set([
            'activite_adh' => $bool($active),
            'bool_display_info' => $bool($public),
            'bool_exempt_adh' => $bool(false),
            'date_echeance' => $uptodate ? date('Y-m-d', strtotime('+1 month')) : date('Y-m-d', strtotime('-1 month')),
        ])->where([\Galette\Entity\Adherent::PK => $id_adh]);
        $this->zdb->execute($update);
    }

    /**
     * Get IDs of listed members
     *
     * @return array<int>
     */
    private function listedIds(): array
    {
        $ids = array_column((new \GaletteMaps\Coordinates())->listCoords(), 'id_adh');
        sort($ids);
        return $ids;
    }

    /**
     * Visitors see public up-to-date profiles, members also see their own one, if active
     */
    public function testListVisibility(): void
    {
        $member_one = $this->getMemberOne();
        $member_two = $this->getMemberTwo();
        $coords = new \GaletteMaps\Coordinates();
        $this->assertTrue($coords->setCoords($member_one->id, 50.36, 3.47));
        $this->assertTrue($coords->setCoords($member_two->id, 48.85, 2.35));

        $this->setVisibility($member_one->id, active: true, public: false, uptodate: false);
        $this->setVisibility($member_two->id, active: true, public: true, uptodate: true);
        $this->assertSame([$member_two->id], $this->listedIds());

        $this->assertTrue($this->login->login($this->dataAdherentOne()['login_adh'], $this->dataAdherentOne()['mdp_adh']));
        $expected = [$member_one->id, $member_two->id];
        sort($expected);
        $this->assertSame($expected, $this->listedIds());

        //an inactive member is never listed, not even to itself
        $this->setVisibility($member_one->id, active: false, public: false, uptodate: false);
        $this->assertSame([$member_two->id], $this->listedIds());

        $this->setVisibility($member_two->id, active: false, public: true, uptodate: true);
        $this->assertSame([], $this->listedIds());
        $this->login->logout();
    }
}
