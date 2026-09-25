<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteMaps\Controllers\tests\units;

use Analog\Analog;
use Galette\Entity\Adherent;
use Galette\Tests\GaletteRoutingTestCase;
use GaletteMaps\Coordinates;

/**
 * Maps controller tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class MapsController extends GaletteRoutingTestCase
{
    protected int $seed = 20260925101512;
    protected bool $load_plugins = true;

    /**
     * Cleanup after each test method
     */
    public function tearDown(): void
    {
        $this->login->logout();
        $this->preferences->pref_bool_groupsmanagers_edit_member = false;
        $this->zdb->execute($this->zdb->delete(MAPS_PREFIX . Coordinates::TABLE));
        parent::tearDown();
    }

    /**
     * Log in given member
     *
     * @param array<string,mixed> $mdata Member data
     */
    private function logMember(array $mdata): void
    {
        $this->assertTrue($this->login->login($mdata['login_adh'], $mdata['mdp_adh']));
    }

    /**
     * Make member two manager of a group
     *
     * @param Adherent[] $members Group members
     */
    private function makeMemberTwoManager(array $members): void
    {
        $group = new \Galette\Entity\Group();
        $group->setName('Maps group');
        $this->assertTrue($group->store());
        $this->assertTrue($group->setManagers([$this->getMemberTwo()]));
        $this->assertTrue($group->setMembers($members));
    }

    /**
     * Post coordinates for a member
     *
     * @param ?int                 $id_adh Member ID, null for logged-in one
     * @param array<string,string> $data   Posted data
     */
    private function postCoords(?int $id_adh, array $data = ['latitude' => '50.362038', 'longitude' => '3.472998']): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->createRequest(
            'maps_ilivehere',
            $id_adh === null ? [] : ['id' => (string)$id_adh],
            'POST'
        )->withParsedBody($data);
        return $this->app->handle($request);
    }

    /**
     * Assert coordinates change has been refused
     *
     * @param \Psr\Http\Message\ResponseInterface $test_response Response
     * @param int                                 $id_adh        Target member ID
     */
    private function expectCoordsRefused(\Psr\Http\Message\ResponseInterface $test_response, int $id_adh): void
    {
        $this->assertSame(403, $test_response->getStatusCode());
        $this->assertSame(
            ['res' => false, 'message' => 'You do not have enough privileges.'],
            json_decode((string)$test_response->getBody(), true)
        );
        $this->expectLogEntry(
            Analog::WARNING,
            'has tried to change coordinates of member #' . $id_adh
        );
        $this->expectNoLogEntry();
    }

    /**
     * A member cannot change nor remove coordinates of another member
     */
    public function testMemberCannotChangeOtherMemberCoords(): void
    {
        //member two speaks Catalan, member one gets messages in English
        $this->getMemberOne();
        $member_two = $this->getMemberTwo();
        $coords = new Coordinates();
        $this->assertTrue($coords->setCoords($member_two->id, 48.85, 2.35));

        $this->logMember($this->dataAdherentOne());
        $this->expectCoordsRefused($this->postCoords($member_two->id), $member_two->id);
        $this->expectCoordsRefused($this->postCoords($member_two->id, ['remove' => '1']), $member_two->id);

        $this->assertEquals(
            ['id_adh' => $member_two->id, 'latitude' => '48.850000', 'longitude' => '2.350000'],
            (array)$coords->getCoords($member_two->id)
        );
    }

    /**
     * A member can change its own coordinates, with or without its ID in the route
     */
    public function testMemberChangesOwnCoords(): void
    {
        $member_one = $this->getMemberOne();
        $this->logMember($this->dataAdherentOne());

        //storing the same position again reports a failure, hence a different latitude
        foreach ([[null, '50.362038'], [$member_one->id, '51.5']] as [$id_adh, $latitude]) {
            $test_response = $this->postCoords($id_adh, ['latitude' => $latitude, 'longitude' => '3.472998']);
            $this->assertSame(200, $test_response->getStatusCode());
            $this->assertSame(
                ['res' => true, 'message' => 'New coordinates has been stored!'],
                json_decode((string)$test_response->getBody(), true)
            );
        }
        $this->assertCount(3, (array)(new Coordinates())->getCoords($member_one->id));
    }

    /**
     * Group managers change coordinates of their members only when core allows them to edit members
     */
    public function testManagerChangesCoordsAsCoreAllows(): void
    {
        $member_one = $this->getMemberOne();
        $this->makeMemberTwoManager([$member_one]);
        $this->logMember($this->dataAdherentTwo());

        $this->expectCoordsRefused($this->postCoords($member_one->id), $member_one->id);
        $this->assertSame([], (new Coordinates())->getCoords($member_one->id));

        $this->preferences->pref_bool_groupsmanagers_edit_member = true;
        $test_response = $this->postCoords($member_one->id);
        $this->assertSame(200, $test_response->getStatusCode());
        $this->assertCount(3, (array)(new Coordinates())->getCoords($member_one->id));
    }

    /**
     * A member cannot display coordinates of another member
     */
    public function testMemberCannotShowOtherMemberCoords(): void
    {
        //member two speaks Catalan, member one gets messages in English
        $this->getMemberOne();
        $member_two = $this->getMemberTwo();
        $this->assertTrue((new Coordinates())->setCoords($member_two->id, 48.85, 2.35));

        $this->logMember($this->dataAdherentOne());
        $request = $this->createRequest('maps_localize_member', ['id' => (string)$member_two->id]);
        $test_response = $this->app->handle($request);
        $this->assertSame(
            ['Location' => [$this->routeparser->urlFor('me')]],
            $test_response->getHeaders()
        );
        $this->assertSame(301, $test_response->getStatusCode());
        $this->expectFlashData(['error_detected' => ['You do not have permission for requested URL.']]);
        $this->expectLogEntry(
            Analog::WARNING,
            'has tried to display coordinates of member #' . $member_two->id
        );
        $this->expectNoLogEntry();
    }

    /**
     * Group managers display coordinates of their members, and change them only when core allows them to
     */
    public function testManagerShowsCoords(): void
    {
        $member_one = $this->getMemberOne();
        $this->makeMemberTwoManager([$member_one]);
        $this->assertTrue((new Coordinates())->setCoords($member_one->id, 48.85, 2.35));
        $this->logMember($this->dataAdherentTwo());

        $request = $this->createRequest('maps_localize_member', ['id' => (string)$member_one->id]);
        $test_response = $this->app->handle($request);
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        $this->assertStringContainsString('48.850000', $body);
        $this->assertStringNotContainsString('id="removecoords"', $body);
        $this->assertStringNotContainsString('onMapClick', $body);

        $this->preferences->pref_bool_groupsmanagers_edit_member = true;
        $test_response = $this->app->handle($request);
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        $this->assertStringContainsString('id="removecoords"', $body);
        $this->assertStringContainsString('onMapClick', $body);
    }

    /**
     * Nicknames and company names in map popups are not interpreted as HTML
     *
     * Member form strips tags, stored values may not have been through it.
     * Names are safe anyway: Adherent::getNameWithCase() strips tags.
     */
    public function testMapEscapesNames(): void
    {
        $member_one = $this->getMemberOne();
        $update = $this->zdb->update(Adherent::TABLE);
        $update->set([
            'pseudo_adh' => '<b>nick</b>',
            'societe_adh' => '<img src=x onerror=alert(1)>',
        ])->where([Adherent::PK => $member_one->id]);
        $this->zdb->execute($update);
        $this->assertTrue((new Coordinates())->setCoords($member_one->id, 48.85, 2.35));

        $this->logSuperAdmin();
        $test_response = $this->app->handle($this->createRequest('maps_map'));
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        //a JS escaped "<" would be turned back into markup by the popup
        $this->assertStringNotContainsString('\u003Cb\u003E', $body);
        $this->assertStringNotContainsString('\u003Cimg', $body);
        $this->assertStringContainsString('\u0026lt\u003Bb\u0026gt\u003Bnick', $body);
        $this->assertStringContainsString('\u0026lt\u003Bimg\u0020src', $body);
    }

    /**
     * Superadmin changes coordinates of any member, but has none
     */
    public function testSuperAdminCoords(): void
    {
        $member_one = $this->getMemberOne();
        $this->logSuperAdmin();

        $test_response = $this->postCoords($member_one->id);
        $this->assertSame(200, $test_response->getStatusCode());
        $this->assertCount(3, (array)(new Coordinates())->getCoords($member_one->id));

        $test_response = $this->postCoords(null);
        $this->assertSame(
            ['res' => false, 'message' => 'Superadmin cannot be localized.'],
            json_decode((string)$test_response->getBody(), true)
        );
        $this->expectLogEntry(Analog::INFO, 'SuperAdmin does not live anywhere!');
    }
}
