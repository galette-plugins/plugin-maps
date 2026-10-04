<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteMaps\tests\units;

use Galette\Core\Plugins\FixturesContext;
use Galette\Tests\GaletteTestCase;

/**
 * Fixtures tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class Fixtures extends GaletteTestCase
{
    protected int $seed = 20261004120000;

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
     * Test seeding and cleaning fixtures
     */
    public function testFixtures(): void
    {
        $member_one = $this->getMemberOne();
        $member_two = $this->getMemberTwo();
        $this->logSuperAdmin();

        $context = new FixturesContext(
            zdb: $this->zdb,
            login: $this->login,
            preferences: $this->preferences,
            history: $this->history,
            plugins: $this->plugins,
            members: [
                'one' => $member_one->id,
                'two' => $member_two->id,
                //every third member is not located: an unknown one would fail on foreign key
                'unknown' => 999999,
            ]
        );

        $fixtures = new \GaletteMaps\Fixtures();
        $this->assertSame('Located 2 members', $fixtures->seedFixtures($context));

        $coords = new \GaletteMaps\Coordinates($this->zdb, $this->login);
        $one = $coords->get($member_one->id);
        $this->assertNotNull($one);
        //around Paris
        $this->assertEqualsWithDelta(48.856614, (float)$one['latitude'], 0.05);
        $this->assertEqualsWithDelta(2.352222, (float)$one['longitude'], 0.05);
        $two = $coords->get($member_two->id);
        $this->assertNotNull($two);
        //around Lyon
        $this->assertEqualsWithDelta(45.764043, (float)$two['latitude'], 0.05);
        $this->assertEqualsWithDelta(4.835659, (float)$two['longitude'], 0.05);

        //seeding again does not fail
        $this->assertSame('Located 2 members', $fixtures->seedFixtures($context));

        $fixtures->cleanFixtures($context);
        $this->assertNull($coords->get($member_one->id));
        $this->assertNull($coords->get($member_two->id));
    }
}
