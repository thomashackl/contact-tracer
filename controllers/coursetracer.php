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
        PageLayout::addScript($this->dispatcher->current_plugin->getPluginURL() .
            '/assets/javascripts/tracer.min.js');
        PageLayout::addStylesheet($this->dispatcher->current_plugin->getPluginURL() .
            '/assets/stylesheets/tracer.css');

        Navigation::activateItem('/course/tracer/qr');

        // Try to find current course date (only with a booked room).
        $this->date = ContactTracerQRCode::findCurrentCourseDate($this->course->id);

        // Date found, generate QR code for registration.
        if ($this->date) {

            if (method_exists('PageLayout', 'allowFullscreenMode')) {
                PageLayout::allowFullscreenMode();
            }

            if ($this->is_lecturer) {
                $registered = count(ContactTracerEntry::findRegisteredPersons($date->id));

                $sidebar = Sidebar::get();
                $widget = new SidebarWidget();
                $widget->setTitle(date('d.m.Y H:i', $this->date->date) . ' ' . $this->date->getRoomName());
                $element = new WidgetElement(sprintf(
                    dngettext('tracer', 'Eine Person registriert', '%s Personen registriert', $registered),
                    $registered));
                $widget->addElement($element);
                $widget->id = 'registered-counter';
                $sidebar->addWidget($widget);

                $print = new ExportWidget();
                $print->addLink(
                    dgettext('tracer', 'QR-Code drucken'),
                    'javascript:window.print()',
                    Icon::create('print')
                );
                $sidebar->addWidget($print);
            }

            $this->date_id = $this->date->id;

            $this->url = URLHelper::getURL($GLOBALS['ABSOLUTE_URI_STUDIP'] .
                'plugins.php/contacttracer/coursetracer/register/' . $this->date->id,
                [
                    'again' => 'yes',
                    'cid' => $this->course->id
                ]);

            $this->qr = ContactTracerQRCode::get($this->date->id);

            $this->enabled = true;

        // No current date available, show message and next date.
        } else {

            $this->date = ContactTracerQRCode::findNextCourseDate($this->course->id);

             if ($this->date) {
                PageLayout::postInfo(
                    sprintf(
                        dgettext('tracer', 'Ein QR-Code zum Registrieren der Anwesenheit wird ' .
                            'automatisch %u Minuten vor Beginn des Termins erzeugt und hier angezeigt.'),
                        Config::get()->CONTACT_TRACER_TIME_OFFSET_BEFORE
                    ),
                    sprintf(
                        dgettext('tracer', 'Nächster Präsenztermin: %s'),
                        $this->date->getFullname() . ($this->date->getRoom() ? ' ' . $nextDate->getRoomName() : '')
                    ));
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
            "`range_id` = :course
                    AND `date` <= :start
            ORDER BY `date`",
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
        $me = User::findCurrent()->id;

        $tz = new DateTimeZone('Europe/Berlin');

        if (!ContactTracerEntry::findByUserAndDate($me, $date_id)) {

            $entry = new ContactTracerEntry();
            $entry->user_id = $me;
            $entry->course_id = $date->course->id;
            $entry->date_id = $date->id;
            $entry->start = new DateTime(date('Y-m-d H:i:s', $date->date), $tz);
            $entry->end = new DateTime(date('Y-m-d H:i:s', $date->end_time), $tz);
            $entry->resource_id = $date->getRoom()->id;
            $entry->mkdate = new DateTime('now', $tz);
            $entry->chdate = new DateTime('now', $tz);

            if ($entry->store() !== false) {
                PageLayout::postSuccess(sprintf(
                    dgettext('tracer', 'Ihre Anwesenheit beim Termin %s wurde registriert.'),
                    $date->getFullname() . ($date->getRoom() ? ' ' . $date->getRoomName() : '')
                ));
            } else {
                PageLayout::postError(
                    dgettext('tracer', 'Ihre Anwesenheit bei diesem Termin konnte nicht registriert ' .
                        'werden, bitte erfassen Sie den Eintrag manuell.')
                );
            }
        } else {
            PageLayout::postWarning(sprintf(
                dgettext('tracer', 'Ihre Anwesenheit beim Termin %s ist bereits registriert.'),
                $date->getFullname() . ($date->getRoom() ? ' ' . $date->getRoomName() : '')
            ));
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

    /**
     * Gets the number of registered persons at the given date.
     *
     * @param string $date_id date to check
     */
    public function get_registered_count_action($date_id)
    {
        $registered = count(ContactTracerEntry::findRegisteredPersons($date_id));
        $this->render_json([
            'number' => (int) $registered,
            'text' => sprintf(
                dngettext('tracer', 'Eine Person registriert', '%u Personen registriert', $registered),
                $registered
            )
        ]);
    }

}
