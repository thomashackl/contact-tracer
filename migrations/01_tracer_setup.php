<?php

/**
 * Initializes necessary data structures for contact tracing.
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

class TracerSetup extends Migration {

    public function description()
    {
        return 'Initializes necessary data structures for contact tracing.';
    }

    /**
     * Migration UP: We have just installed the plugin
     * and need to prepare all necessary data.
     */
    public function up()
    {
        // Table for storing who was present at which course date.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `contact_tracing`
        (
            `entry_id` INT NOT NULL AUTO_INCREMENT,
            `user_id` VARCHAR(32) COLLATE latin1_bin NOT NULL,
            `course_id` VARCHAR(32) COLLATE latin1_bin NULL,
            `date_id` VARCHAR(32) COLLATE latin1_bin NULL,
            `start` DATETIME NOT NULL,
            `end` DATETIME NOT NULL,
            `resource_id` VARCHAR(32) COLLATE latin1_bin NOT NULL,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`entry_id`),
            INDEX course_id (`course_id`),
            INDEX date_id (`date_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        // Remove database table.
        DBManager::get()->execute("DROP TABLE IF EXISTS `contact_tracing`");
    }

}
