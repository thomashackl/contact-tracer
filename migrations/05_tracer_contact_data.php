<?php

/**
 * Provides a data field for contact, this could contain a phone number, e-mail or post address.
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

class TracerContactData extends Migration {

    public function description()
    {
        return 'Provides a data field for contact, this could contain a phone number, e-mail or post address.';
    }

    /**
     * Migration UP: Add the column to database table
     */
    public function up()
    {
        // Add a column for storing contact data.
        DBManager::get()->execute("ALTER TABLE `contact_tracing` ADD `contact` TEXT NULL DEFAULT '' AFTER `resource_id`");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove column with contact data.
        DBManager::get()->execute("ALTER TABLE `contact_tracing` DROP `contact`");
    }

}
