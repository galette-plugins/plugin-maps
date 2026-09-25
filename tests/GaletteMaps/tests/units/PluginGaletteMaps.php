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
 * Plugin class tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class PluginGaletteMaps extends GaletteTestCase
{
    protected int $seed = 20260925101512;

    /**
     * Cleanup after each test method
     */
    public function tearDown(): void
    {
        $this->login->logout();
        parent::tearDown();
    }

    /**
     * Get plugin instance
     */
    private function getPlugin(): \GaletteMaps\PluginGaletteMaps
    {
        return $this->container->get(\GaletteMaps\PluginGaletteMaps::class);
    }

    /**
     * Get routes names of menus entries
     *
     * @param array<string|int, mixed> $menus Menus
     *
     * @return array<string, array<string>>
     */
    private function getMenusRoutes(array $menus): array
    {
        $routes = [];
        foreach ($menus as $section => $menu) {
            $routes[$section] = array_map(
                fn(array $item): string => $item['route']['name'],
                $menu['items']
            );
        }
        return $routes;
    }

    /**
     * Menus depend on logged-in user
     */
    public function testMenus(): void
    {
        $plugin = $this->getPlugin();
        $this->assertSame([], $plugin->getMenus());

        $this->getMemberOne();
        $this->assertTrue($this->login->login($this->dataAdherentOne()['login_adh'], $this->dataAdherentOne()['mdp_adh']));
        $this->assertSame(['myaccount' => ['maps_mymap']], $this->getMenusRoutes($plugin->getMenus()));
        $this->login->logout();

        //superadmin does not live anywhere
        $this->logSuperAdmin();
        $this->assertSame(['configuration' => ['maps_preferences']], $this->getMenusRoutes($plugin->getMenus()));

        $public = $plugin->getPublicMenus();
        $this->assertCount(1, $public);
        $this->assertSame('maps_map', $public[0]['route']['name']);
    }

    /**
     * Dashboards and actions
     */
    public function testDashboardsAndActions(): void
    {
        $plugin = $this->getPlugin();
        $member = $this->getMemberOne();

        $this->logSuperAdmin();
        $this->assertSame([], $plugin->getMyDashboards());
        $this->assertSame([], $plugin->getDashboards());
        $this->assertSame([], $plugin->getBatchActions());
        $this->login->logout();

        $this->assertTrue($this->login->login($this->dataAdherentOne()['login_adh'], $this->dataAdherentOne()['mdp_adh']));
        $dashboards = $plugin->getMyDashboards();
        $this->assertCount(1, $dashboards);
        $this->assertSame(
            ['name' => 'maps_localize_member', 'args' => ['id' => $member->id]],
            $dashboards[0]['route']
        );

        $actions = $plugin->getListActions($member);
        $this->assertCount(1, $actions);
        $this->assertSame(
            ['name' => 'maps_localize_member', 'args' => ['id' => $member->id]],
            $actions[0]['route']
        );
        $this->assertSame($actions, $plugin->getDetailedActions($member));
    }

    /**
     * Plugin is installed in tests database
     */
    public function testIsInstalled(): void
    {
        $this->assertTrue($this->getPlugin()->isInstalled());
    }
}
