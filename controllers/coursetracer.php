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

    /**
     * Show QR code for current date or hint to next date
     */
    public function index_action()
    {
        PageLayout::addScript($this->dispatcher->current_plugin->getPluginURL() .
            '/assets/javascripts/tracer.min.js');
        PageLayout::addStylesheet($this->dispatcher->current_plugin->getPluginURL() .
            '/assets/stylesheets/tracer.css');

        Navigation::activateItem('/course/tracer/qr');

        // Try to find current course date.
        $this->date = ContactTracerQRCode::findCurrentCourseDate($this->course->id);

        // Date found, generate QR code for registration.
        if ($this->date) {

            if (method_exists('PageLayout', 'allowFullscreenMode')) {
                PageLayout::allowFullscreenMode();
            }

            if ($this->is_lecturer) {
                $registered = count(ContactTracerEntry::findRegisteredPersons($this->date->id));

                $sidebar = Sidebar::get();
                $widget = new SidebarWidget();
                $widget->setTitle(date('d.m.Y H:i', $this->date->date) . ' ' . $this->date->getRoomName());
                $element = new WidgetElement(sprintf(
                    dngettext('tracer', 'Eine Person registriert', '%s Personen registriert', $registered),
                    $registered));
                $widget->addElement($element);
                $widget->id = 'registered-counter';
                $sidebar->addWidget($widget);

                if (Config::get()->CONTACT_TRACER_LECTURER_PARTICIPANT_LIST_ACCESS && $this->is_lecturer) {
                    $views = new ViewsWidget();
                    $views->addLink(
                        dgettext('tracer', 'QR-Code anzeigen'),
                        $this->link_for('coursetracer')
                    )->setActive(true);
                    $views->addLink(
                        dgettext('tracer', 'Liste der registrierten Personen'),
                        $this->link_for('coursetracer/list', $this->date->id)
                    )->setActive(false);
                    $sidebar->addWidget($views);
                }

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

            $_SESSION['coursetracer_landing_point'] = $this->link_for('coursetracer');

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
                    [sprintf(
                        dgettext('tracer', 'Nächster Termin: %s'),
                        trim($this->date->getFullname() . ' ' . $this->date->getRoomName())
                    )]
                );
            } else {
                PageLayout::postInfo(dgettext('tracer',
                    'Diese Veranstaltung hat keine zukünftigen Termine, daher ist ' .
                    'keine Kontaktverfolgung erforderlich.'));
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

        $_SESSION['coursetracer_landing_point'] = $this->link_for('coursetracer/manual');
    }

    public function register_action($date_id, $user_id = '')
    {
        PageLayout::addStylesheet($this->dispatcher->current_plugin->getPluginURL() .
            '/assets/stylesheets/tracer.css');

        $navigation = Navigation::getItem('/course/tracer');
        $navigation->addSubNavigation('register', new Navigation(dgettext('tracer', 'Registrieren'),
            $this->link_for('coursetracer/register', $date_id)));

        Navigation::activateItem('/course/tracer/register');

        $this->date = CourseDate::find($date_id);

        $this->user = ($user_id != '' ? $user_id : User::findCurrent()->id);

        $this->lastContact = ContactTracerEntry::findLastContactText($this->user);

        if (ContactTracerEntry::findByUserAndDate($this->user, $date_id)) {
            PageLayout::postWarning(sprintf(
                dgettext('tracer', 'Die Anwesenheit beim Termin %s ist bereits registriert.'),
                $this->date->getFullname() . ($this->date->getRoom() ? ' ' . $this->date->getRoomName() : '')
            ));
        }
    }

    public function do_register_action($date_id, $user_id = '')
    {
        $date = CourseDate::find($date_id);

        $user = $user_id != '' ? $user_id : User::findCurrent()->id;

        $tz = new DateTimeZone('Europe/Berlin');

        if (!ContactTracerEntry::findByUserAndDate($user, $date_id)) {

            $entry = new ContactTracerEntry();
            $entry->user_id = $user;
            $entry->course_id = $date->course->id;
            $entry->date_id = $date->id;
            $entry->start = new DateTime(date('Y-m-d H:i:s', $date->date), $tz);
            $entry->end = new DateTime(date('Y-m-d H:i:s', $date->end_time), $tz);
            $entry->resource_id = $date->getRoom()->id;
            $entry->contact = trim(Request::get('contact'));
            $entry->mkdate = new DateTime('now', $tz);
            $entry->chdate = new DateTime('now', $tz);

            if ($entry->store() !== false) {
                PageLayout::postSuccess(sprintf(
                    dgettext('tracer', 'Die Anwesenheit beim Termin %s wurde registriert.'),
                    $date->getFullname() . ($date->getRoom() ? ' ' . $date->getRoomName() : '')
                ));
            } else {
                PageLayout::postError(
                    dgettext('tracer', 'Die Anwesenheit bei diesem Termin konnte nicht registriert ' .
                        'werden, bitte erfassen Sie den Eintrag manuell.')
                );
            }
        } else {
            PageLayout::postWarning(sprintf(
                dgettext('tracer', 'Die Anwesenheit beim Termin %s ist bereits registriert.'),
                $date->getFullname() . ($date->getRoom() ? ' ' . $date->getRoomName() : '')
            ));
        }

        $this->relocate($_SESSION['coursetracer_landing_point'] ?: $this->link_for('coursetracer'));
    }

    public function unregister_action($date_id, $user_id = '')
    {
        $user = $user_id != '' ? $user_id : User::findCurrent()->id;

        $entry = ContactTracerEntry::findByUserAndDate($user, $date_id);

        if ($entry->delete()) {
            PageLayout::postSuccess(
                dgettext('tracer', 'Die Anwesenheit bei diesem Termin wurde entfernt.')
            );
        } else {
            PageLayout::postError(
                dgettext('tracer', 'Die Anwesenheit bei diesem Termin konnte nicht entfernt werden.')
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

    public function list_action($date_id)
    {
        if (!Config::get()->CONTACT_TRACER_LECTURER_PARTICIPANT_LIST_ACCESS || !$this->is_lecturer) {
            throw new AccessDeniedException();
        }

        Navigation::activateItem('/course/tracer/qr');

        $this->entries = DBManager::get()->fetchAll(
            "SELECT DISTINCT a.`user_id`, a.`Nachname`, a.`Vorname`, a.`username`, IFNULL(t.`entry_id`, 0) AS registered
            FROM `auth_user_md5` a
                JOIN `seminar_user` s ON (s.`user_id` = a.`user_id`)
                LEFT JOIN `contact_tracing` t ON (t.`user_id` = a.`user_id` AND t.`date_id` = :date)
            WHERE s.`Seminar_id` = :course
            ORDER BY a.`Nachname`, a.`Vorname`, a.`username`",
            ['date' => $date_id, 'course' => $this->course->id]);
        $this->date = CourseDate::find($date_id);

        $_SESSION['coursetracer_landing_point'] = $this->link_for('coursetracer/list', $date_id);

        $sidebar = Sidebar::get();
        $views = new ViewsWidget();
        $views->addLink(
            dgettext('tracer', 'QR-Code anzeigen'),
            $this->link_for('coursetracer')
        )->setActive(false);
        $views->addLink(
            dgettext('tracer', 'Liste der registrierten Personen'),
            $this->link_for('coursetracer/list', $this->date->id)
        )->setActive(true);
        $sidebar->addWidget($views);
    }

}
