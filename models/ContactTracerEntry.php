<?php

/**
 * ContactTracerEntry.php
 * model class for contact tracing entries.
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
 * @property int entry_id database column
 * @property string user_id database column
 * @property string course_id database column
 * @property string date_id database column
 * @property int start database column
 * @property int end database column
 * @property string resource_id database column
 * @property string contact database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class ContactTracerEntry extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'contact_tracing';
        $config['has_one']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name' => 'Course',
            'foreign_key' => 'course_id',
            'assoc_foreign_key' => 'seminar_id'
        ];
        $config['belongs_to']['date'] = [
            'class_name' => 'CourseDate',
            'foreign_key' => 'date_id',
            'assoc_foreign_key' => 'termin_id'
        ];

        // Auto-convert database datetime from and to PHP DateTime objects for easier handling.
        $config['registered_callbacks']['before_store'][]     = 'cbDateTimeObject';
        $config['registered_callbacks']['after_store'][]      = 'cbDateTimeObject';
        $config['registered_callbacks']['after_initialize'][] = 'cbDateTimeObject';

        parent::configure($config);
    }

    /**
     * Gets the entry for the given user and date.
     *
     * @param string $user_id the user ID
     * @param string $date_id the date ID
     */
    public static function findByUserAndDate($user_id, $date_id)
    {
        return self::findOneBySQL("`user_id` = :user AND `date_id` = :date", ['user' => $user_id, 'date' => $date_id]);
    }

    /**
     * Fetches all registered persons at the given date.
     *
     * @param string $date_id
     * @return array user IDs of all persons registered.
     */
    public static function findRegisteredPersons($date_id)
    {
        return array_map(function ($one) {
                return $one->user_id;
            },
            self::findBySQL("`date_id` = :date", ['date' => $date_id]));
    }

    /**
     * Fetches all contacts the given person had in the given time period.
     *
     * @param string $user_id
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return array all entries
     */
    public static function findContacts($user_id, $start, $end)
    {
        // Search at which dates the user was present.
        $present = array_map(function($one) {
                return $one->date_id;
            },
            self::findBySQL(
                "`user_id` = :user AND (`start` BETWEEN :start AND :end OR `end` BETWEEN :start AND :end)",
                [
                    'user' => $user_id,
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s')
                ]
            )
        );

        return self::findBySQL(
            "`date_id` IN (:dates) AND `user_id` != :user ORDER BY `start`",
            ['dates' => $present, 'user' => $user_id]
        );
    }

    /**
     * Gets the last entered contact text (like email, post address or phone number)
     *
     * @param string $user_id
     * @return mixed
     */
    public static function findLastContactText($user_id)
    {
        $user = User::find($user_id);
        $lastContact = $user->email;

        $last = self::findOneBySQL("`user_id` = :user ORDER BY `mkdate` DESC",
            ['user' => $user->id]);

        if ($last && $last->contact) {
            $lastContact = $last->contact;
        }

        return $lastContact;
    }

    /**
     * Visibilities are stored as strings to database (YYYY-MM-DD HH:ii:ss).
     * Internally, the model class uses DateTime objects for better handling.
     *
     * @param string $type the event
     */
    public function cbDateTimeObject($type)
    {
        foreach (words('start end mkdate chdate') as $one) {
            if ($type === 'before_store' && $this->$one != null) {
                $this->$one = $this->$one->format('Y-m-d H:i:s');
            }
            if (in_array($type, ['after_initialize', 'after_store']) && $this->$one != null) {
                $this->$one = new DateTime($this->$one);
            }
        }
    }

}
