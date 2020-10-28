<?php

/**
 * Makes this plugin a homepage plugin and allows entering the desired
 * contact method via the own profile.
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

require_once(__DIR__ . '/../models/ContactTracerContactData.php');

class TracerContactdataProfile extends Migration {

    public function description()
    {
        return 'Makes this plugin a homepage plugin and allows entering the desired ' .
            'contact method via the own profile.';
    }

    /**
     * Migration UP: Adjust plugin type and prepare database structure.
     */
    public function up()
    {
        // Create a database table for storing phonenumbers/email addresses/other contact data.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `contact_tracing_contact_data`
        (
            `user_id` CHAR(32) NOT NULL,
            `contact` TEXT NOT NULL,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`user_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        /*
         * Migrate already entered contact data to new table.
         * We join auth_user_md5 here so that only real users are migrated.
         */
        $users = DBManager::get()->fetchFirst("SELECT DISTINCT t.`user_id` FROM `contact_tracing` t
            JOIN `auth_user_md5` a USING (`user_id`)");

        $tz = new DateTimeZone('Europe/Berlin');

        foreach ($users as $id) {
            // Get last entered contact data and use it.
            $contact = DBManager::get()->fetchColumn(
                "SELECT `contact` FROM `contact_tracing` WHERE `user_id` = :user ORDER BY `chdate` DESC LIMIT 1",
                ['user' => $id]
            );

            if ($contact != '') {
                $d = new ContactTracerContactData();
                $d->user_id = $id;
                $d->contact = $contact;
                $d->mkdate = new DateTime('now', $tz);
                $d->chdate = new DateTime('now', $tz);
                $d->store();
            }
        }

        // Remove old column from contact tracing table.
        DBManager::get()->execute("ALTER TABLE `contact_tracing` DROP `contact`");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Add column for registration-specific contact data.
        DBManager::get()->execute(
            "ALTER TABLE `contact_tracing` ADD `contact` TEXT NULL DEFAULT '' AFTER `resource_id`");

        // Drop extra contact data table.
        DBManager::get()->execute("DROP TABLE `contact_tracing_contact_data`");
    }

}
