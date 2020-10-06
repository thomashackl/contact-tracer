<?php
/**
 * Class ContactTracerCronjob
 *
 * Cronjob for auto-deleting contact entries older than the configured number of days.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Tracer
 */

class ContactTracerCronjob extends CronJob {

    public static function getName() {
        return dgettext('tracer', 'Löscht alte Kontakteinträge nach einer gegebenen Anzahl von Tagen');
    }

    public static function getDescription() {
        return dgettext('tracer', 'Kontakteinträge, die älter als die eingestellte Anzahl ' .
            'von Tagen sind, werden aus Datenschutzgründen automatisch gelöscht.');
    }

    public static function getParameters() {
        return [];
    }

    /**
     * Delete old entries.
     */
    public function execute($last_result, $parameters = array()) {
        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        $days = Config::get()->CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION;

        // Setting the config entry to 0 disables cleanup.
        if ($days) {

            $time = new DateTime('now', new DateTimeZone('Europe/Berlin'));
            $time->modify(sprintf('-%u days', $days));

            $ret = ContactTracerEntry::deleteBySQL("`end` <= ?", [$time->format('Y-m-d H:i:s')]);

            echo sprintf("Deleted %u entries older than %s.\n", $ret, $time->format('d.m.Y H:i:s'));
        }

    }
}

