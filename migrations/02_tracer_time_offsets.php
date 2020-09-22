<?php

/**
 * Provides config entries for setting offsets before and after a course date
 * where the QR code will be available.
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

class TracerTimeOffsets extends Migration {

    public function description()
    {
        return 'Provides config entries for setting offsets before and after a course date ' .
            'where the QR code will be available.';
    }

    /**
     * Migration UP: We have just installed the plugin
     * and need to prepare all necessary data.
     */
    public function up()
    {
        // QR code availability before start of course date
        Config::get()->create('CONTACT_TRACER_TIME_OFFSET_BEFORE', [
            'value' => 30,
            'type' => 'integer',
            'range' => 'global',
            'section' => 'contact_tracer',
            'description' => 'Wie viele Minuten vor Beginn eines Termins soll der zugehörige QR-Code verfügbar sein?'
        ]);
        // QR code availability after end of course date
        Config::get()->create('CONTACT_TRACER_TIME_OFFSET_AFTER', [
            'value' => 0,
            'type' => 'integer',
            'range' => 'global',
            'section' => 'contact_tracer',
            'description' => 'Wie viele Minuten nach Ende eines Termins soll der zugehörige QR-Code verfügbar sein?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        Config::get()->delete('CONTACT_TRACER_TIME_OFFSET_BEFORE');
        Config::get()->delete('CONTACT_TRACER_TIME_OFFSET_AFTER');
    }

}
