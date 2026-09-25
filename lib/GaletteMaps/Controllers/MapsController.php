<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteMaps\Controllers;

use DI\Attribute\Inject;
use Galette\Controllers\AbstractPluginController;
use Galette\Entity\Adherent;
use GaletteMaps\NominatimTowns;
use GaletteMaps\Coordinates;
use GaletteMaps\TileProviders;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Analog\Analog;

/**
 * Galette maps controller
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */

class MapsController extends AbstractPluginController
{
    /**
     * @var array<string, mixed>
     */
    #[Inject("Plugin Galette Maps")]
    protected array $module_info;

    /**
     * Member dependencies to load; groups are loaded on demand by access checks
     *
     * @return array<string, bool>
     */
    private function getMemberDeps(): array
    {
        return [
            'picture'   => false,
            'groups'    => false,
            'dues'      => false
        ];
    }

    /**
     * Message for a member that does not exist
     *
     * @param int $id Requested member ID
     */
    private function getNoMemberMessage(int $id): string
    {
        return sprintf(
            //TRANS: parameter is the member identifier
            _T('No member #%1$s.'),
            $id
        );
    }

    /**
     * Main route
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     */
    public function map(Request $request, Response $response): Response
    {
        $params = [
            'page_title'        => _T('Maps', 'maps'),
            'module_id'         => $this->getModuleId(),
            'tiles'             => TileProviders::resolve($this->preferences),
            'list'              => []
        ];

        try {
            $params['list'] = (new Coordinates())->listCoords();
        } catch (\Throwable $e) {
            //already logged
            $this->flash->addMessageNow(
                'error_detected',
                _T('Coordinates has not been loaded. Maybe plugin tables does not exists in the database?', 'maps')
            );
        }

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('maps'),
            $params
        );
        return $response;
    }

    /**
     * Member localization
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param ?int     $id       Member ID
     */
    public function localizeMember(Request $request, Response $response, ?int $id = null): Response
    {
        if ($id === null && $this->login->isSuperAdmin()) {
            return $this->redirectWithErrors(
                response: $response,
                errors: [_T('Superadmin cannot be localized.', 'maps')],
                redirect_url: $this->routeparser->urlFor('slash')
            );
        }
        $id ??= (int)$this->login->id;
        $member = new Adherent($this->zdb, $id, $this->getMemberDeps());
        if ($member->id === null) {
            return $this->redirectWithErrors(
                response: $response,
                errors: [$this->getNoMemberMessage($id)],
                redirect_url: $this->routeparser->urlFor('slash')
            );
        }

        if (!$member->canShow($this->login)) {
            Analog::log(
                'Logged in member ' . $this->login->login
                . ' has tried to display coordinates of member #' . $id
                . ' without the right to show them.',
                Analog::WARNING
            );
            return $this->redirectWithErrors(
                response: $response,
                errors: [_T("You do not have permission for requested URL.")],
                redirect_url: $this->routeparser->urlFor('me')
            );
        }
        $can_edit = $member->canEdit($this->login);

        $coords = new Coordinates();
        $mcoords = $coords->getCoords($member->id);

        $towns = false;
        //towns are only proposed to choose a location
        if ($can_edit && count($mcoords) === 0 && trim($member->town ?? '') !== '') {
            try {
                $towns = (new NominatimTowns($this->preferences))->search(
                    $member->town,
                    $member->country
                );
            } catch (\RuntimeException $e) {
                Analog::log(
                    'Unable to search towns for member #' . $member->id . ' | ' . $e->getMessage(),
                    Analog::WARNING
                );
                $this->flash->addMessageNow(
                    'warning_detected',
                    _T('Town search is not available for now, you can still search or click on the map.', 'maps')
                );
            }
        }

        $params = [
            'page_title'        => _T('Maps', 'maps') . ' - ' . str_replace(
                '%member',
                $member->sfullname,
                _T('%member geographic position', 'maps')
            ),
            'member'            => $member,
            'can_edit'          => $can_edit,
            'adh_map'           => true,
            'module_id'         => $this->getModuleId(),
            'tiles'             => TileProviders::resolve($this->preferences)
        ];

        if ($towns !== false) {
            $params['towns'] = $towns;
        } elseif (count($mcoords) > 0) {
            $params['town'] = $mcoords;
        }

        if ($member->login == $this->login->login) {
            $params['mymap'] = true;
        }

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('mymap'),
            $params
        );
        return $response;
    }

    /**
     * Tile provider settings
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     */
    public function preferences(Request $request, Response $response): Response
    {
        $params = [
            'page_title'    => _T('Maps settings', 'maps'),
            'module_id'     => $this->getModuleId(),
            'providers'     => TileProviders::getSelectValues(),
            'custom'        => TileProviders::CUSTOM,
            'tiles'         => TileProviders::resolve($this->preferences),
            'provider'      => $this->preferences->getPluginValue(TileProviders::PREF_PROVIDER),
            'vector'        => $this->preferences->getPluginValue(TileProviders::PREF_VECTOR),
            'url'           => $this->preferences->getPluginValue(TileProviders::PREF_URL),
            'attribution'   => $this->preferences->getPluginValue(TileProviders::PREF_ATTRIBUTION),
            'maxzoom'       => $this->preferences->getPluginValue(TileProviders::PREF_MAXZOOM),
            'subdomains'    => $this->preferences->getPluginValue(TileProviders::PREF_SUBDOMAINS),
        ];

        $this->view->render(
            $response,
            $this->getTemplate('maps_preferences'),
            $params
        );
        return $response;
    }

    /**
     * Store tile provider settings
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     */
    public function storePreferences(Request $request, Response $response): Response
    {
        $post = $request->getParsedBody();
        $provider = $post[TileProviders::PREF_PROVIDER] ?? TileProviders::DEFAULT;

        $values = [TileProviders::PREF_PROVIDER => $provider];
        if ($provider === TileProviders::CUSTOM) {
            //own values are only meaningful along with the custom provider
            $values += [
                TileProviders::PREF_VECTOR => (int)isset($post[TileProviders::PREF_VECTOR]),
                TileProviders::PREF_URL => trim((string)($post[TileProviders::PREF_URL] ?? '')),
                TileProviders::PREF_ATTRIBUTION => trim((string)($post[TileProviders::PREF_ATTRIBUTION] ?? '')),
                TileProviders::PREF_MAXZOOM => (int)($post[TileProviders::PREF_MAXZOOM] ?? 19),
                TileProviders::PREF_SUBDOMAINS => trim((string)($post[TileProviders::PREF_SUBDOMAINS] ?? '')),
            ];

            if ($values[TileProviders::PREF_URL] === '') {
                $this->flash->addMessage(
                    'error_detected',
                    _T('An address is required to use your own background map.', 'maps')
                );
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', $this->routeparser->urlFor('maps_preferences'));
            }
        }

        $stored = true;
        foreach ($values as $name => $value) {
            $stored = $this->preferences->setValue($name, $value, $this->login) && $stored;
        }

        if ($stored) {
            $this->flash->addMessage(
                'success_detected',
                _T('Maps settings have been saved.', 'maps')
            );
        } else {
            foreach ($this->preferences->getErrors() as $error) {
                $this->flash->addMessage('error_detected', $error);
            }
        }

        return $response
            ->withStatus(302)
            ->withHeader('Location', $this->routeparser->urlFor('maps_preferences'));
    }

    /**
     * Change member localization
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param ?int     $id       Member ID
     */
    public function ILiveHere(Request $request, Response $response, ?int $id = null): Response
    {
        $error = null;
        $message = null;
        $status = 200;

        if ($id === null && $this->login->isSuperAdmin()) {
            Analog::log(
                'SuperAdmin does not live anywhere!',
                Analog::INFO
            );
            $error = _T('Superadmin cannot be localized.', 'maps');
            $status = 400;
        } else {
            $id ??= (int)$this->login->id;
            $member = new Adherent($this->zdb, $id, $this->getMemberDeps());
            if ($member->id === null) {
                $error = $this->getNoMemberMessage($id);
                $status = 404;
            } elseif (!$member->canEdit($this->login)) {
                Analog::log(
                    'Logged in member ' . $this->login->login
                    . ' has tried to change coordinates of member #' . $id
                    . ' without the right to edit them.',
                    Analog::WARNING
                );
                $error = _T('You do not have permission for requested URL.');
                $status = 403;
            }
        }

        if ($error === null) {
            $post = $request->getParsedBody();
            $coords = new Coordinates();
            if (isset($post['remove'])) {
                if ($coords->removeCoords($id)) {
                    $message = _T('Coordinates has been removed!', 'maps');
                } else {
                    $error = _T('Coordinates has not been removed :(', 'maps');
                    $status = 500;
                }
            } else {
                $latitude = filter_var($post['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
                $longitude = filter_var($post['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
                if (
                    $latitude === false
                    || $longitude === false
                    || abs($latitude) > 90
                    || abs($longitude) > 180
                ) {
                    $error = _T('Invalid coordinates.', 'maps');
                    $status = 400;
                } elseif ($coords->setCoords($id, $latitude, $longitude)) {
                    $message = _T('New coordinates has been stored!', 'maps');
                } else {
                    $error = _T('Coordinates has not been stored :(', 'maps');
                    $status = 500;
                }
            }
        }

        $response = $response
            ->withStatus($status)
            ->withHeader('Content-type', 'application/json');

        $res = [
            'res'       => $error === null,
            'message'   => ($error ?? $message)
        ];

        $body = $response->getBody();
        $body->write(json_encode($res));

        return $response;
    }
}
