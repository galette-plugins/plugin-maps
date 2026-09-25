<?php

/**
 * This file is part of Galette Maps plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2012-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteMaps;

use Analog\Analog;
use ArrayObject;
use Galette\Core\Db;
use Galette\Entity\Adherent;
use Laminas\Db\Sql\Expression;

/**
 * Members GPS coordinates
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */

class Coordinates
{
    public const string TABLE = 'coordinates';
    public const string PK = 'id_adh';

    /**
     * Retrieve member coordinates
     *
     * @param int $id Member id
     *
     * @return array<string>|ArrayObject<string, int|string>
     */
    public function getCoords(int $id): array|ArrayObject
    {
        /** @var Db $zdb */
        global $zdb;

        try {
            $select = $zdb->select($this->getTableName());
            $select->where([self::PK => $id]);
            $results = $zdb->execute($select);

            if ($results->count() > 0) {
                return $results->current();
            } else {
                return [];
            }
        } catch (\Exception $e) {
            if ($e->getCode() == '42S02') {
                Analog::log(
                    'Maps coordinates table does not exists',
                    Analog::WARNING
                );
            } else {
                Analog::log(
                    'Unable to retrieve members coordinates for "'
                    . $id . '". | ' . $e->getMessage(),
                    Analog::WARNING
                );
            }
            throw $e;
        }
    }

    /**
     * Returns list of all know coordinates, filtered on publicly
     * visible profile for non admins and non staff
     *
     * @return array<int, array<string,mixed>>
     */
    public function listCoords(): array
    {
        global $zdb, $login;

        try {
            $select = $zdb->select($this->getTableName(), 'c');
            $select->join(
                [
                    'a' => PREFIX_DB . Adherent::TABLE
                ],
                'a.' . self::PK . '=' . 'c.' . self::PK,
                //only what the map displays
                ['nom_adh', 'prenom_adh', 'pseudo_adh', 'societe_adh']
            );
            $where = $select->where;
            $where->equalTo('a.activite_adh', new Expression('true'));

            if (
                !$login->isAdmin()
                && !$login->isStaff()
                && !$login->isSuperAdmin()
            ) {
                //limit query to public up-to-date profiles, and to logged-in member own one
                $visible = $where->nest();
                $public = $visible->nest();
                $public->nest()
                    ->greaterThanOrEqualTo('a.date_echeance', date('Y-m-d'))
                    ->or->equalTo('a.bool_exempt_adh', new Expression('true'))
                    ->unnest();
                $public->and->equalTo('a.bool_display_info', new Expression('true'));
                $public->unnest();
                if ($login->isLogged()) {
                    $visible->or->equalTo('a.' . Adherent::PK, $login->id);
                }
                $visible->unnest();
            }

            $results = $zdb->execute($select);

            $res = [];
            foreach ($results as $r) {
                $m = [
                    'id_adh'    => (int)$r->{self::PK},
                    'lat'       => $r->latitude,
                    'lng'       => $r->longitude,
                    'name'      => Adherent::getNameWithCase($r->nom_adh, $r->prenom_adh),
                    'nickname'  => $r->pseudo_adh
                ];
                if (trim($r->societe_adh ?? '') !== '') {
                    $m['company'] = $r->societe_adh;
                }
                $res[] = $m;
            }

            return $res;
        } catch (\Exception $e) {
            if ($e->getCode() == '42S02') {
                Analog::log(
                    'Maps coordinates table does not exists',
                    Analog::WARNING
                );
            } else {
                Analog::log(
                    'Unable to retrieve members coordinates list "'
                    . '". | ' . $e->getMessage(),
                    Analog::WARNING
                );
            }
            throw $e;
        }
    }

    /**
     * Set member coordinates
     *
     * @param int   $id        Member id
     * @param float $latitude  Latitude
     * @param float $longitude Longitude
     */
    public function setCoords(int $id, float $latitude, float $longitude): bool
    {
        global $zdb;

        try {
            $coords = $this->getCoords($id);
            if (count($coords) === 0) {
                //coordinates does not exist yet
                $insert = $zdb->insert($this->getTableName());
                $insert->values(
                    [
                        self::PK    => $id,
                        'latitude'  => $latitude,
                        'longitude' => $longitude
                    ]
                );
                $results = $zdb->execute($insert);
            } else {
                //coordinates already exists, just update
                $update = $zdb->update($this->getTableName());
                $update->set(
                    [
                        'latitude'  => $latitude,
                        'longitude' => $longitude
                    ]
                )->where(
                    [self::PK => $id]
                );
                //no row is affected when the position does not change
                $zdb->execute($update);
                return true;
            }
            return ($results->count() > 0);
        } catch (\Exception $e) {
            Analog::log(
                'Unable to set coordinates | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Remove member coordinates
     *
     * @param int $id Member id
     */
    public function removeCoords(int $id): bool
    {
        global $zdb;

        try {
            $delete = $zdb->delete($this->getTableName());
            $delete->where([self::PK => $id]);
            $del = $zdb->execute($delete);
            return ($del->count() > 0);
        } catch (\Exception $e) {
            Analog::log(
                'Unable to remove coordinates for member '
                . $id . ' | ' . $e->getMessage(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Get table's name
     */
    protected function getTableName(): string
    {
        return MAPS_PREFIX . self::TABLE;
    }
}
