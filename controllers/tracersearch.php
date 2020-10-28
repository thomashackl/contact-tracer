<?php

/**
 * Class TracersearchController
 * Controller for contact tracing search:
 *   - lecturers generate QR codes for registering to a course date
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

class TracersearchController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();

        Navigation::activateItem('/search/tracer/search');
    }

    public function index_action()
    {
        $this->qs = QuickSearch::get('user', new StandardSearch('user_id'))->withButton();

        $days = Config::get()->CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION;

        if ($days) {
            PageLayout::postWarning(dgettext('tracer',
                sprintf('Einträge werden nach %u Tagen automatisch gelöscht.', $days)));
        }
    }

    public function do_action()
    {
        $tz = new DateTimeZone('Europe/Berlin');

        $start = new DateTime(Request::get('start'), $tz);
        $end = new DateTime(Request::get('end'), $tz);

        $result = ContactTracerEntry::findContacts(Request::option('user'), $start, $end);

        $user = User::find(Request::option('user'));
        $this->user = $user->id;
        $this->name = $user->getFullname();
        $this->start = $start->format('d.m.Y H:i');
        $this->end = $end->format('d.m.Y H:i');

        $this->contacts = [];
        foreach ($result as $one) {

            if ($one->user) {
                $uindex = $one->user->getFullname('full_rev') . ' (' . $one->user->username . ')';
                $cindex = $one->course->getFullname();

                if (!is_array($this->contacts[$uindex])) {
                    $this->contacts[$uindex] = [
                        'contact' => $one->contact_data ? $one->contact_data->contact : $one->user->email,
                        'courses' => []
                    ];
                }

                if (!is_array($this->contacts[$uindex]['courses'][$cindex])) {
                    $this->contacts[$uindex]['courses'][$cindex] = [];
                }

                $this->contacts[$uindex]['courses'][$cindex][] = $one;

                ksort($this->contacts[$uindex]['courses']);
            }
        }

        ksort($this->contacts);

        $sidebar = Sidebar::get();
        $print = new ExportWidget();
        $print->addLink(
            dgettext('tracer', 'Als CSV exportieren'),
            $this->link_for('tracersearch/csv', $user->id, $start->getTimestamp(), $end->getTimestamp()),
            Icon::create('file-text+export')
        );
        $sidebar->addWidget($print);
    }

    /**
     * Exports a contact list as CSV.
     *
     * @param string $user_id user ID
     * @param int $start
     * @param int $end
     */
    public function csv_action($user_id, $start, $end)
    {
        $tz = new DateTimeZone('Europe/Berlin');

        $startTime = new DateTime('now', $tz);
        $startTime->setTimestamp($start);
        $endTime = new DateTime('now', $tz);
        $endTime->setTimestamp($end);

        $result = ContactTracerEntry::findContacts($user_id, $startTime, $endTime);

        $user = User::find($user_id);

        $csv = [
            [
                dgettext('tracer', 'Nachname'),
                dgettext('tracer', 'Vorname'),
                dgettext('tracer', 'Kontakt')
            ]
        ];
        foreach ($result as $one) {
            $courses = [];
            $row = [
                $one->user->nachname,
                $one->user->vorname,
                $one->user->username,
                $one->contact_data ? $one->contact_data->contact : $one->user->email
            ];

            if (!$courses[$one->course->getFullname()]) {
                $courses[$one->course->getFullname()] = $one->course->getFullname() . ':';
            }

            $courses[$one->course->getFullname()] .= "\n" . $one->start->format('d.m.Y H:i') . ' - ' .
                $one->end->format('H:i');

            $row[] = implode("\n", $courses);

            $csv[] = array_values($row);
        }

        $filename = strtolower('kontaktliste-' . $user->nachname . '-' . $user->vorname .
            '-' . $startTime->format('Y-m-d-H-i') . '-' . $endTime->format('Y-m-d-H-i'));

        $this->response->add_header('Content-Disposition', 'attachment;filename=' . $filename . '.csv');
        $this->render_text(array_to_csv($csv));
    }

}
