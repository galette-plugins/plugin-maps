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
use GaletteMaps\TileProviders;

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
        $this->preferences->pref_bool_publicpages = true;
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
            //message comes in the language of the logged-in member
            ['res' => false, 'message' => _T('You do not have permission for requested URL.')],
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
        $coords = new Coordinates($this->zdb, $this->login);
        $coords->set($member_two->id, 48.85, 2.35);

        $this->logMember($this->dataAdherentOne());
        $this->expectCoordsRefused($this->postCoords($member_two->id), $member_two->id);
        $this->expectCoordsRefused($this->postCoords($member_two->id, ['remove' => '1']), $member_two->id);

        $this->assertSame(
            ['latitude' => '48.850000', 'longitude' => '2.350000'],
            $coords->get($member_two->id)
        );
    }

    /**
     * A member can change its own coordinates, with or without its ID in the route
     */
    public function testMemberChangesOwnCoords(): void
    {
        $member_one = $this->getMemberOne();
        $this->logMember($this->dataAdherentOne());

        foreach ([null, $member_one->id] as $id_adh) {
            $test_response = $this->postCoords($id_adh);
            $this->assertSame(200, $test_response->getStatusCode());
            $this->assertSame(
                ['res' => true, 'message' => 'New coordinates has been stored!'],
                json_decode((string)$test_response->getBody(), true)
            );
        }
        $this->assertNotNull((new Coordinates($this->zdb, $this->login))->get($member_one->id));
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
        $this->assertNull((new Coordinates($this->zdb, $this->login))->get($member_one->id));

        $this->preferences->pref_bool_groupsmanagers_edit_member = true;
        $test_response = $this->postCoords($member_one->id);
        $this->assertSame(200, $test_response->getStatusCode());
        $this->assertNotNull((new Coordinates($this->zdb, $this->login))->get($member_one->id));
    }

    /**
     * Coordinates out of bounds, not numeric or missing are refused
     */
    public function testInvalidCoords(): void
    {
        $member_one = $this->getMemberOne();
        $this->logMember($this->dataAdherentOne());

        $invalid = [
            ['latitude' => '91', 'longitude' => '3'],
            ['latitude' => '50', 'longitude' => '-180.5'],
            ['latitude' => 'north', 'longitude' => '3'],
            ['latitude' => '50'],
            [],
        ];
        foreach ($invalid as $data) {
            $test_response = $this->postCoords(null, $data);
            $this->assertSame(400, $test_response->getStatusCode(), print_r($data, true));
            $this->assertSame(
                ['res' => false, 'message' => 'Invalid coordinates.'],
                json_decode((string)$test_response->getBody(), true)
            );
        }
        $this->assertNull((new Coordinates($this->zdb, $this->login))->get($member_one->id));

        //bounds are included
        $test_response = $this->postCoords(null, ['latitude' => '-90', 'longitude' => '180']);
        $this->assertSame(200, $test_response->getStatusCode());
    }

    /**
     * Removing coordinates of a member that has none is not an error
     */
    public function testRemoveMissingCoords(): void
    {
        $this->getMemberOne();
        $this->logMember($this->dataAdherentOne());
        $test_response = $this->postCoords(null, ['remove' => '1']);
        $this->assertSame(200, $test_response->getStatusCode());
        $this->assertSame(
            ['res' => true, 'message' => 'Coordinates has been removed!'],
            json_decode((string)$test_response->getBody(), true)
        );
    }

    /**
     * Towns matching member town are proposed when member has no coordinates
     */
    public function testTownsProposed(): void
    {
        $member_one = $this->getMemberOne();
        $update = $this->zdb->update(Adherent::TABLE);
        $update->set(['ville_adh' => 'Valenciennes'])->where([Adherent::PK => $member_one->id]);
        $this->zdb->execute($update);
        $places = [
            ['lat' => '50.3620', 'lon' => '3.4729', 'display_name' => 'Valenciennes', 'address' => ['city' => 'Valenciennes']],
            ['lat' => '45.1', 'lon' => '1.2', 'display_name' => 'Somewhere', 'address' => ['village' => '<b>Petit</b> Valenciennes']],
        ];
        $client = new \GuzzleHttp\Client([
            'handler' => \GuzzleHttp\HandlerStack::create(
                new \GuzzleHttp\Handler\MockHandler([new \GuzzleHttp\Psr7\Response(200, [], (string)json_encode($places))])
            )
        ]);
        $this->container->set(
            \GaletteMaps\NominatimTowns::class,
            new \GaletteMaps\NominatimTowns($this->preferences, $client)
        );

        $this->logMember($this->dataAdherentOne());
        $test_response = $this->app->handle($this->createRequest('maps_mymap'));
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        $this->assertStringContainsString('id="possible_towns"', $body);
        $this->assertStringContainsString('<span class="lat">50.3620</span>/<span class="lon">3.4729</span>', $body);
        $this->assertStringContainsString('&lt;b&gt;Petit&lt;/b&gt; Valenciennes', $body);
        $this->assertStringNotContainsString('<b>Petit</b>', $body);
    }

    /**
     * Unreachable towns search does not prevent to display the map
     */
    public function testTownsSearchUnavailable(): void
    {
        $member_one = $this->getMemberOne();
        $this->assertNotEmpty($member_one->town);
        $this->logMember($this->dataAdherentOne());

        //no proxy listens there: the request fails at once
        putenv('HTTPS_PROXY=http://127.0.0.1:1');
        try {
            $test_response = $this->app->handle($this->createRequest('maps_mymap'));
        } finally {
            putenv('HTTPS_PROXY');
        }
        $this->assertSame(200, $test_response->getStatusCode());
        $this->assertStringContainsString(
            'Town search is not available for now, you can still search or click on the map.',
            (string)$test_response->getBody()
        );
        $this->expectLogEntry(Analog::WARNING, 'Unable to search towns for member #' . $member_one->id);
        $this->expectNoLogEntry();
    }

    /**
     * A member cannot display coordinates of another member
     */
    public function testMemberCannotShowOtherMemberCoords(): void
    {
        //member two speaks Catalan, member one gets messages in English
        $this->getMemberOne();
        $member_two = $this->getMemberTwo();
        (new Coordinates($this->zdb, $this->login))->set($member_two->id, 48.85, 2.35);

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
        (new Coordinates($this->zdb, $this->login))->set($member_one->id, 48.85, 2.35);
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
        (new Coordinates($this->zdb, $this->login))->set($member_one->id, 48.85, 2.35);

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
     * Map is displayed to visitors only when public pages allow it
     */
    public function testPublicMap(): void
    {
        $member_one = $this->getMemberOne();
        (new Coordinates($this->zdb, $this->login))->set($member_one->id, 48.85, 2.35);
        $request = $this->createRequest('maps_map');

        $this->preferences->pref_bool_publicpages = false;
        $test_response = $this->app->handle($request);
        $this->assertSame(
            ['Location' => [$this->routeparser->urlFor('slash')]],
            $test_response->getHeaders()
        );
        $this->assertSame(302, $test_response->getStatusCode());
        $this->expectFlashData(['error_detected' => ['Unauthorized']]);

        $this->preferences->pref_bool_publicpages = true;
        $this->preferences->pref_publicpages_visibility_generic = \Galette\Core\Preferences::PUBLIC_PAGES_VISIBILITY_PUBLIC;
        $test_response = $this->app->handle($request);
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        $this->assertStringContainsString('_mapsBinded', $body);
        //member one does not display its information
        $this->assertStringNotContainsString('48.850000', $body);
    }

    /**
     * Own localization page, with and without coordinates
     */
    public function testOwnPage(): void
    {
        $member_one = $this->getMemberOne();
        $this->logMember($this->dataAdherentOne());
        $request = $this->createRequest('maps_mymap');

        //no town search without a town
        $update = $this->zdb->update(Adherent::TABLE);
        $update->set(['ville_adh' => ''])->where([Adherent::PK => $member_one->id]);
        $this->zdb->execute($update);
        $test_response = $this->app->handle($request);
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        $this->assertStringNotContainsString('id="possible_towns"', $body);
        $this->assertStringNotContainsString('id="removecoords"', $body);
        $this->assertStringContainsString('onMapClick', $body);

        (new Coordinates($this->zdb, $this->login))->set($member_one->id, 48.85, 2.35);
        $test_response = $this->app->handle($request);
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        $this->assertStringContainsString('var _lat = 48.850000;', $body);
        $this->assertStringContainsString('id="removecoords"', $body);
        $this->assertStringContainsString('I\\u0020live\\u0020here\\u0021', $body);
    }

    /**
     * Only administrators reach preferences
     */
    public function testPreferencesAccess(): void
    {
        $this->getMemberOne();
        $this->logMember($this->dataAdherentOne());
        $this->expectAuthMiddlewareRefused($this->app->handle($this->createRequest('maps_preferences')));
        $this->login->logout();

        $this->logSuperAdmin();
        $test_response = $this->app->handle($this->createRequest('maps_preferences'));
        $this->assertSame(200, $test_response->getStatusCode());
        $body = (string)$test_response->getBody();
        $this->assertStringContainsString('id="pref_maps_tiles_provider"', $body);
        $this->assertStringContainsString('OpenFreeMap, light grey', $body);
    }

    /**
     * Store preferences
     */
    public function testStorePreferences(): void
    {
        $this->logSuperAdmin();
        $store = function (array $data): \Psr\Http\Message\ResponseInterface {
            $request = $this->createRequest('maps_store_preferences', [], 'POST')->withParsedBody($data);
            $test_response = $this->app->handle($request);
            $this->assertSame(
                ['Location' => [$this->routeparser->urlFor('maps_preferences')]],
                $test_response->getHeaders()
            );
            $this->assertSame(302, $test_response->getStatusCode());
            return $test_response;
        };

        try {
            $store([TileProviders::PREF_PROVIDER => 'osm']);
            $this->expectFlashData(['success_detected' => ['Maps settings have been saved.']]);
            $this->preferences->load();
            $this->assertSame('osm', TileProviders::resolve($this->preferences)['id']);

            //own values need an address
            $store([TileProviders::PREF_PROVIDER => TileProviders::CUSTOM, TileProviders::PREF_URL => '  ']);
            $this->expectFlashData(['error_detected' => ['An address is required to use your own background map.']]);
            $this->preferences->load();
            $this->assertSame('osm', TileProviders::resolve($this->preferences)['id']);

            //unticked vector box is not posted
            $store([
                TileProviders::PREF_PROVIDER => TileProviders::CUSTOM,
                TileProviders::PREF_URL => 'https://tiles.example.org/{z}/{x}/{y}.png',
                TileProviders::PREF_ATTRIBUTION => 'Example',
                TileProviders::PREF_MAXZOOM => '17',
                TileProviders::PREF_SUBDOMAINS => '',
            ]);
            $this->expectFlashData(['success_detected' => ['Maps settings have been saved.']]);
            $this->preferences->load();
            $tiles = TileProviders::resolve($this->preferences);
            $this->assertSame(TileProviders::CUSTOM, $tiles['id']);
            $this->assertFalse($tiles['vector']);
            $this->assertSame('https://tiles.example.org/{z}/{x}/{y}.png', $tiles['url']);
            $this->assertSame(17, $tiles['maxzoom']);

            //out of range zoom is refused
            $store([
                TileProviders::PREF_PROVIDER => TileProviders::CUSTOM,
                TileProviders::PREF_URL => 'https://tiles.example.org/{z}/{x}/{y}.png',
                TileProviders::PREF_MAXZOOM => '30',
            ]);
            //core has no range error message
            $this->expectFlashData(['error_detected' => ['- Value for \'pref_maps_tiles_maxzoom\' must be a positive number!']]);
            $this->preferences->load();
            $this->assertSame(17, TileProviders::resolve($this->preferences)['maxzoom']);
        } finally {
            foreach (TileProviders::getSchema() as $name => $schema) {
                $this->preferences->setValue($name, $schema['default'], $this->login);
            }
        }
    }

    /**
     * Superadmin has no own localization page
     */
    public function testSuperAdminOwnPage(): void
    {
        $this->logSuperAdmin();
        $test_response = $this->app->handle($this->createRequest('maps_mymap'));
        $this->assertSame(
            ['Location' => [$this->routeparser->urlFor('slash')]],
            $test_response->getHeaders()
        );
        $this->assertSame(301, $test_response->getStatusCode());
        $this->expectFlashData(['error_detected' => ['Superadmin cannot be localized.']]);
    }

    /**
     * Localization of a member that does not exist
     */
    public function testMissingMember(): void
    {
        $this->logSuperAdmin();
        $test_response = $this->app->handle($this->createRequest('maps_localize_member', ['id' => '999999']));
        $this->assertSame(
            ['Location' => [$this->routeparser->urlFor('slash')]],
            $test_response->getHeaders()
        );
        $this->assertSame(301, $test_response->getStatusCode());
        $this->expectFlashData(['error_detected' => ['No member #999999.']]);
        //logged by Adherent on load
        $this->expectLogEntry(Analog::ERROR, 'No member #999999');

        $test_response = $this->postCoords(999999);
        $this->assertSame(404, $test_response->getStatusCode());
        $this->assertSame(
            ['res' => false, 'message' => 'No member #999999.'],
            json_decode((string)$test_response->getBody(), true)
        );
        $this->expectLogEntry(Analog::ERROR, 'No member #999999');
        $this->expectNoLogEntry();
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
        $this->assertNotNull((new Coordinates($this->zdb, $this->login))->get($member_one->id));

        $test_response = $this->postCoords(null);
        $this->assertSame(400, $test_response->getStatusCode());
        $this->assertSame(
            ['res' => false, 'message' => 'Superadmin cannot be localized.'],
            json_decode((string)$test_response->getBody(), true)
        );
        $this->expectLogEntry(Analog::INFO, 'SuperAdmin does not live anywhere!');
    }
}
