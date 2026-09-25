<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteMaps;

use DI\Attribute\Inject;
use Galette\Core\Db;
use Galette\Core\Login;
use Galette\Core\Plugins\InstallableInterface;
use Galette\Core\Plugins\MenuProviderInterface;
use Galette\Core\Plugins\DashboardProviderInterface;
use Galette\Core\Plugins\MemberActionProviderInterface;
use Galette\Core\Plugins\PreferencesProviderInterface;
use Galette\Entity\Adherent;
use Galette\Core\GalettePlugin;

/**
 * Members GPS coordinates
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */

class PluginGaletteMaps extends GalettePlugin implements InstallableInterface, MenuProviderInterface, DashboardProviderInterface, MemberActionProviderInterface, PreferencesProviderInterface
{
    #[Inject]
    private readonly Db $zdb; //@phpstan-ignore property.uninitializedReadonly, property.onlyRead (injected from DI)
    #[Inject]
    private readonly Login $login; //@phpstan-ignore property.uninitializedReadonly, property.onlyRead (injected from DI)

    /**
     * Get the preferences the plugin declares
     *
     * @return array<string, array<string, mixed>>
     */
    public function getPreferences(): array
    {
        return TileProviders::getSchema();
    }

    /**
     * Extra menus entries
     *
     * @return array<string|int, string|array<string,mixed>>
     */
    public function getMenus(): array
    {
        $menus = [];

        if ($this->login->isAdmin()) {
            $menus['configuration'] = [
                'items' => [
                    [
                        'label' => _T('Maps settings', 'maps'),
                        'route' => [
                            'name' => 'maps_preferences',
                        ]
                    ],
                ]
            ];
        }

        if ($this->login->isLogged() && !$this->login->isSuperAdmin()) {
            $menus['myaccount'] = [
                'items' => [
                    [
                        'label' => _T('My localization', 'maps'),
                        'route' => [
                            'name' => 'maps_mymap', //or maps_localize_member
                        ]
                    ],
                ]
            ];
        }

        return $menus;
    }

    /**
     * Extra public menus entries
     *
     * @return array<int, string|array<string,mixed>>
     */
    public function getPublicMenus(): array
    {
        return [
            [
                'label' => _T("Maps", "maps"),
                'route' => [
                    'name' => 'maps_map'
                ],
                'icon' => 'map'
            ]
        ];
    }

    /**
     * Get current logged-in user dashboards contents
     *
     * @return array<int, string|array<string,mixed>>
     */
    public function getMyDashboards(): array
    {
        if ($this->login->isSuperAdmin()) {
            return [];
        }

        return [
            [
                'label' => _T("My localization", "maps"),
                'route' => [
                    'name' => 'maps_localize_member',
                    'args' => ["id" => $this->login->id]
                ],
                'icon' => 'map'
            ]
        ];
    }

    /**
     * Get dashboards contents
     *
     * @return array<int, string|array<string,mixed>>
     */
    public function getDashboards(): array
    {
        return [];
    }

    /**
     * Get actions contents
     *
     * @param Adherent $member Member instance
     *
     * @return array<int, string|array<string,mixed>>
     */
    public function getListActions(Adherent $member): array
    {
        return [
            [
                'label' => _T("Geolocalize", "maps"),
                'title' => sprintf(
                    //TRANS: parameter is the member name
                    _T('Geolocalize %1$s', 'maps'),
                    $member->sname
                ),
                'route' => [
                    'name' => 'maps_localize_member',
                    'args' => ['id' => $member->id]
                ],
                'icon' => 'map marker alternate grey'
            ],
        ];
    }

    /**
     * Get detailed actions contents
     *
     * @param Adherent $member Member instance
     *
     * @return array<int, string|array<string,mixed>>
     */
    public function getDetailedActions(Adherent $member): array
    {
        return $this->getListActions($member);
    }

    /**
     * Get batch actions contents
     *
     * @return array<int, string|array<string,mixed>>
     */
    public function getBatchActions(): array
    {
        return [];
    }

    /**
     * Is the plugin fully installed (including database, extra configuration, etc.)?
     */
    public function isInstalled(): bool
    {
        return $this->zdb->tableExists(MAPS_PREFIX . Coordinates::TABLE);
    }
}
