<?php

/**
 * Changes collation of database columns that reference Stud.IP IDs.
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

class TracerCollations extends Migration {

    public function description()
    {
        return 'Changes collation of database columns that reference Stud.IP IDs.';
    }

    /**
     * Migration UP: Add the column to database table
     */
    public function up()
    {
        // Add a column for storing contact data.
        DBManager::get()->execute("ALTER TABLE `contact_tracing_contact_data`
            CHANGE `user_id` `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove column with contact data.
        DBManager::get()->execute("ALTER TABLE `contact_tracing_contact_data`
            CHANGE `user_id` `user_id` CHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

}
