<?php

/**
 * Class Course_TracerController
 * Controller for contact tracing in courses:
 *   - lecturers provide QR codes for registering to a course date
 *   - participants scan QR codes and register as "present"
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

class CoursetracerController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->course = Course::findCurrent();

        if (!$GLOBALS['perm']->have_studip_perm('user', $this->course->id)) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();

        $this->is_lecturer = $GLOBALS['perm']->have_studip_perm('dozent', $this->course->id);
    }

    public function index_action()
    {
        Navigation::activateItem('/course/tracer/qr');

        // Try to find current course date (only with a booked room).
        $date = CourseDate::findOneBySQL(
            "JOIN `resource_bookings` b ON (b.`range_id` = `termine`.`termin_id`)
            WHERE `termine`.`range_id` = :course
                AND :time BETWEEN `termine`.`date` - :before AND `termine`.`end_time` + :after",
            [
                'course' => $this->course->id,
                'time' => time(),
                'before' => Config::get()->CONTACT_TRACER_TIME_OFFSET_BEFORE * 60,
                'after' => Config::get()->CONTACT_TRACER_TIME_OFFSET_AFTER * 60
            ]
        );

        // Date found, generate QR code for registration.
        if ($date) {
            PageLayout::allowFullscreenMode();

            $this->url = URLHelper::getURL($GLOBALS['ABSOLUTE_URI_STUDIP'] .
                'plugins.php/contacttracer/coursetracer/register/' . $date->id,
                [
                    'again' => 'yes',
                    'cid' => $this->course->id
                ]);

            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel' => QRCode::ECC_M,
                'svgViewBoxSize' => 67
            ]);
            $this->qr = new QRCode($options);

            $this->enabled = true;

        // No current date available, show message and next date.
        } else {

            $nextDate = CourseDate::findOneBySQL(
                "JOIN `resource_bookings` b ON (b.`range_id` = `termine`.`termin_id`)
                WHERE `termine`.`range_id` = :course
                    AND `termine`.`date` > :now",
                ['course' => $this->course->id, 'now' => time()]
            );

            if ($nextDate) {
                PageLayout::postInfo(
                    sprintf(
                        dgettext('tracer', 'Ein QR-Code zum Registrieren der Anwesenheit wird ' .
                            'automatisch %u Minuten vor Beginn des Termins erzeugt und hier angezeigt.'),
                        Config::get()->CONTACT_TRACER_TIME_OFFSET_BEFORE
                    ),
                    $nextDate ?
                        [sprintf(
                            dgettext('tracer', 'Nächster Präsenztermin: %s'),
                            $nextDate->getFullname() . ($nextDate->getRoom() ? ' ' . $nextDate->getRoomName() : '')
                        )] :
                        []
                );
            } else {
                PageLayout::postInfo(dgettext('tracer',
                    'Diese Veranstaltung hat keine Präsenztermine, daher ist keine Kontaktverfolgung erforderlich.'));
            }

            $this->enabled = false;

        }
    }

    public function manual_action()
    {
        Navigation::activateItem('/course/tracer/manual');

        $this->dates = CourseDate::findBySQL(
            "JOIN `resource_bookings` b ON (b.`range_id` = `termine`.`termin_id`)
                WHERE `termine`.`range_id` = :course
                    AND `termine`.`date` <= :start",
            ['course' => $this->course->id, 'start' => time() + Config::get()->CONTACT_TRACER_TIME_OFFSET_BEFORE * 60]
        );
    }

    public function register_action($date_id, $redirect = false)
    {
        $navigation = Navigation::getItem('/course/tracer');
        $navigation->addSubNavigation('register', new Navigation(dgettext('tracer', 'Registrieren'),
            PluginEngine::getURL($this, [], 'coursetracer/register')));

        Navigation::activateItem('/course/tracer/register');

        $date = CourseDate::find($date_id);

        $tz = new DateTimeZone('Europe/Berlin');

        $entry = new ContactTracerEntry();
        $entry->user_id = User::findCurrent()->id;
        $entry->course_id = $date->course->id;
        $entry->date_id = $date->id;
        $entry->start = new DateTime(date('Y-m-d H:i:s', $date->date), $tz);
        $entry->end = new DateTime(date('Y-m-d H:i:s', $date->end_time), $tz);
        $entry->resource_id = $date->getRoom()->id;
        $entry->mkdate = new DateTime('now', $tz);
        $entry->chdate = new DateTime('now', $tz);

        if ($entry->store() !== false) {
            PageLayout::postSuccess(
                dgettext('tracer', 'Ihre Anwesenheit bei diesem Termin wurde registriert.')
            );
        } else {
            PageLayout::postError(
                dgettext('tracer', 'Ihre Anwesenheit bei diesem Termin konnte nicht registriert ' .
                    'werden, bitte erfassen Sie den Eintrag manuell.')
            );
        }

        if ($redirect) {
            $this->relocate('coursetracer/manual');
        }
    }

    public function unregister_action($date_id)
    {
        $entry = ContactTracerEntry::findByUserAndDate(User::findCurrent()->id, $date_id);

        if ($entry->delete()) {
            PageLayout::postSuccess(
                dgettext('tracer', 'Ihre Anwesenheit bei diesem Termin wurde entfernt.')
            );
        } else {
            PageLayout::postError(
                dgettext('tracer', 'Ihre Anwesenheit bei diesem Termin konnte nicht entfernt werden.')
            );
        }

        $this->relocate('coursetracer/manual');
    }

}
