<?php

/**
 * ContactTracerContactData.php
 * model class for contact data like phone numbers or mail addresses.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Tracer
 *
 * @property string user_id database column
 * @property string contact database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class ContactTracerContactData extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'contact_tracing_contact_data';
        $config['has_one']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];

        // Auto-convert database datetime from and to PHP DateTime objects for easier handling.
        $config['registered_callbacks']['before_store'][]     = 'cbDateTimeObject';
        $config['registered_callbacks']['after_store'][]      = 'cbDateTimeObject';
        $config['registered_callbacks']['after_initialize'][] = 'cbDateTimeObject';

        parent::configure($config);
    }

    /**
     * Visibilities are stored as strings to database (YYYY-MM-DD HH:ii:ss).
     * Internally, the model class uses DateTime objects for better handling.
     *
     * @param string $type the event
     */
    public function cbDateTimeObject($type)
    {
        foreach (words('mkdate chdate') as $one) {
            if ($type === 'before_store' && $this->$one != null) {
                $this->$one = $this->$one->format('Y-m-d H:i:s');
            }
            if (in_array($type, ['after_initialize', 'after_store']) && $this->$one != null) {
                $this->$one = new DateTime($this->$one);
            }
        }
    }

}
