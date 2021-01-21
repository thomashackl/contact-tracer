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
        if (!$GLOBALS['perm']->have_perm('root') &&
                !$GLOBALS['user']->getAuthenticatedUser()->hasRole('Kontaktverfolgung')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();
    }

    /**
     * Show search mask for specifying which contacts to find.
     */
    public function index_action()
    {
        Navigation::activateItem('/search/tracer/search');

        $this->qs = QuickSearch::get('user', new StandardSearch('user_id'))->withButton();

        $days = Config::get()->CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION;

        if ($days) {
            PageLayout::postWarning(dgettext('tracer',
                sprintf('Einträge werden nach %u Tagen automatisch gelöscht.', $days)));
        }
    }

    /**
     * Find contacts of given person in given time frame.
     *
     * @throws Exception
     */
    public function do_action()
    {
        Navigation::activateItem('/search/tracer/search');

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
                        'contact' => ($one->contact_data ? $one->contact_data->contact : '') . "\n" . $one->user->email,
                        'courses' => []
                    ];
                }

                $lecturers = $one->course->getMembersWithStatus('dozent', true)
                    ->orderBy('position, nachname, vorname, username');

                $lecturersText = [];

                foreach ($lecturers as $l) {
                    $lecturersText[] = $l->getUserFullname('full');
                }

                if (!is_array($this->contacts[$uindex]['courses'][$cindex])) {
                    $this->contacts[$uindex]['courses'][$cindex] = [
                        'lecturers' => $lecturersText,
                        'dates' => []
                    ];
                }

                $this->contacts[$uindex]['courses'][$cindex]['dates'][] = $one;

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
                dgettext('tracer', 'Nutzername'),
                dgettext('tracer', 'Kontakt'),
                dgettext('tracer', 'Termin(e)')
            ]
        ];
        foreach ($result as $one) {
            $courses = [];
            $row = [
                $one->user->nachname,
                $one->user->vorname,
                $one->user->username,
                ($one->contact_data ? $one->contact_data->contact : '') . "\n" . $one->user->email
            ];

            $lecturers = $one->course->getMembersWithStatus('dozent', true)
                ->orderBy('position, nachname, vorname, username');

            $lecturersText = [];

            foreach ($lecturers as $l) {
                $lecturersText[] = $l->getUserFullname('full');
            }

            if (!$courses[$one->course->getFullname()]) {
                $courses[$one->course->getFullname()] = $one->course->getFullname() . ' (' .
                    implode(', ', $lecturersText) . '):';
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

    /**
     * Provide a search mask for a list of usernames.
     */
    public function contact_data_action()
    {
        Navigation::activateItem('/search/tracer/contact_data');
    }

    public function get_contact_data_action()
    {
        if (($matriculation = Config::get()->CONTACT_TRACER_MATRICULATION_DATAFIELD_ID) != null) {
            $entries = DBManager::get()->fetchAll(
                "SELECT a.`Nachname`, a.`Vorname`, a.`username`, IFNULL(c.`contact`, a.`Email`)
                FROM `auth_user_md5` a
                    LEFT JOIN `contact_tracing_contact_data` c ON (c.`user_id` = a.`user_id`)
                    LEFT JOIN `datafields_entries` d ON (d.`range_id` = c.`user_id` AND d.`datafield_id` = :matriculation)
                WHERE a.`username` IN (:users) OR d.`content` IN (:users)
                ORDER BY a.`Nachname`, a.`Vorname`, a.`username`",
                [
                    'users' => array_map('trim', preg_split('/\n|,/', Request::get('users'))),
                    'matriculation' => $matriculation
                ]
            );
        } else {
            $entries = DBManager::get()->fetchAll(
                "SELECT a.`Nachname`, a.`Vorname`, a.`username`, IFNULL(c.`contact`, a.`Email`)
                FROM `auth_user_md5` a
                    LEFT JOIN `contact_tracing_contact_data` c ON (c.`user_id` = a.`user_id`)
                WHERE a.`username` IN (:usernames)
                ORDER BY a.`Nachname`, a.`Vorname`, a.`username`",
                ['users' => array_map('trim', preg_split('/\n|,/', Request::get('users')))]
            );
        }

        $this->render_csv($entries);
    }

}
