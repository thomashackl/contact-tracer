<?php

/**
 * Config entry for specifying the free datafield that contains the matriculation number.
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

class TracerMatriculation extends Migration {

    public function description()
    {
        return 'Config entry for specifying the free datafield that contains the matriculation number.';
    }

    /**
     * Migration UP: Create config entries
     */
    public function up()
    {
        // Try to find correct ID by searching for a free datafield with name "Matrikelnummer"
        $id = '';
        $datafield = Datafield::findOneByName('Matrikelnummer');
        if ($datafield) {
            $id = $datafield->id;
        }

        $c = Config::get();
        if ($c->CONTACT_TRACER_MATRICULATION_DATAFIELD_ID === null) {
            $c->create('CONTACT_TRACER_MATRICULATION_DATAFIELD_ID', [
                'value' => $id,
                'type' => 'string',
                'range' => 'global',
                'section' => 'contact_tracer',
                'description' => 'ID des freien Datenfelds für die Matrikelnummer'
            ]);
        }
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        Config::get()->delete('CONTACT_TRACER_MATRICULATION_DATAFIELD_ID');
    }

}
