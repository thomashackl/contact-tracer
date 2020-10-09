<?php

/**
 * Config entries for lecturer access to date participant list and self-deregistering.
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

class TracerConfigureParticipantList extends Migration {

    public function description()
    {
        return 'Config entries for lecturer access to date participant list and self-deregistering.';
    }

    /**
     * Migration UP: Create config entries
     */
    public function up()
    {
        // Access to participant list for course lecturers
        Config::get()->create('CONTACT_TRACER_LECTURER_PARTICIPANT_LIST_ACCESS', [
            'value' => 0,
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'contact_tracer',
            'description' => 'Dürfen Lehrende einsehen und ändern, wer zu einem Termin registriert ist?'
        ]);
        // Self-deregistration
        Config::get()->create('CONTACT_TRACER_ENABLE_SELF_DEREGISTRATION', [
            'value' => 0,
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'contact_tracer',
            'description' => 'Dürfen sich bereits registrierte Personen zu einem Termin wieder austragen?'
        ]);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        Config::get()->delete('CONTACT_TRACER_LECTURER_PARTICIPANT_LIST_ACCESS');
        Config::get()->delete('CONTACT_TRACER_ENABLE_SELF_DEREGISTRATION');
    }

}
