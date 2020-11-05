<?php

/**
 * Config entries for setting a disclaimer text that must be accepted on registration.
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

class TracerDisclaimer extends Migration {

    public function description()
    {
        return 'Config entry for setting a disclaimer text that must be accepted on registration.';
    }

    /**
     * Migration UP: Create config entries
     *
     * TODO: Config entries are not i18n, so there's only one language
     * or all languages concatenated in one string.
     */
    public function up()
    {
        Config::get()->create('CONTACT_TRACER_DISCLAIMER', [
            'value' => 'Die hier erfassten Daten werden ausschließlich zum ' .
                'Zweck der Kontaktverfolgung gespeichert. - The data entered ' .
                'here will only be stored for contact tracing.',
            'type' => 'string',
            'range' => 'global',
            'section' => 'contact_tracer',
            'description' => 'Text, der bei Registrierung angezeigt wird.'
        ]);
        Config::get()->create('CONTACT_TRACER_MUST_ACCEPT_DISCLAIMER', [
            'value' => 0,
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'contact_tracer',
            'description' => 'Muss der bei der Registrierung angezeigte Text bestätigt/akzeptiert werden?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        Config::get()->delete('CONTACT_TRACER_DISCLAIMER');
    }

}
