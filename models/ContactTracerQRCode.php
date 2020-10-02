<?php

/**
 * ContactTracerQRCode.php
 * helper class for generating QR codes.
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

require_once(__DIR__ . '/../vendor/autoload.php');

use chillerlan\QRCode\QRCode, chillerlan\QRCode\QROptions;

class ContactTracerQRCode extends SimpleORMap
{

    /**
     * Gets a QR code for the given date.
     *
     * @param string $date_id the date ID
     */
    public static function get($date_id)
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M,
            'svgViewBoxSize' => 100
        ]);
        return new QRCode($options);

    }

    /**
     * Finds the current course date, given by course ID and time frame before and after start.
     *
     * @param string $course_id
     * @return CourseDate|null
     */
    public static function findCurrentCourseDate($course_id)
    {
        return CourseDate::findOneBySQL(
            "`range_id` = :course
                AND :time BETWEEN `date` - :before AND `end_time` + :after",
            [
                'course' => $course_id,
                'time' => time(),
                'before' => Config::get()->CONTACT_TRACER_TIME_OFFSET_BEFORE * 60,
                'after' => Config::get()->CONTACT_TRACER_TIME_OFFSET_AFTER * 60
            ]
        );
    }

    public static function findNextCourseDate($course_id)
    {
        return CourseDate::findOneBySQL(
            "`range_id` = :course AND `date` > :now ORDER BY `date`",
            ['course' => $course_id, 'now' => time()]
        );

    }

}
