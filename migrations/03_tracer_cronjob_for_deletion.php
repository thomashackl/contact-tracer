<?php

/**
 * Provides a config entries for specifying the number of days before
 * the cronjob deletes old data.
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

class TracerCronjobForDeletion extends Migration {

    public function description()
    {
        return 'Provides a config entries for specifying the number of days before
            the cronjob deletes old data.';
    }

    /**
     * Migration UP: Create config entry
     */
    public function up()
    {
        // QR code availability before start of course date
        Config::get()->create('CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION', [
            'value' => 28,
            'type' => 'integer',
            'range' => 'global',
            'section' => 'contact_tracer',
            'description' => 'Nach wie vielen Tagen sollen Kontakteinträge automatisch gelöscht werden ' .
                '(0 schaltet die Bereinigung ab)?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        Config::get()->delete('CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION');
    }

}
