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
        $this->qs = Quicksearch::get('user', new StandardSearch('user_id'))->withButton();
    }

    public function do_action()
    {
        $tz = new DateTimeZone('Europe/Berlin');

        $start = new DateTime(Request::get('start'), $tz);
        $end = new DateTime(Request::get('end'), $tz);

        $result = ContactTracerEntry::findContacts(Request::option('user'), $start, $end);

        $this->name = User::find(Request::option('user'))->getFullname();
        $this->start = $start->format('d.m.Y H:i');
        $this->end = $end->format('d.m.Y H:i');

        $this->contacts = [];
        foreach ($result as $one) {
            $uindex = $one->user->getFullname('full_rev') . ' (' . $one->user->username . ')';
            $cindex = $one->course->getFullname();

            if (!is_array($this->contacts[$uindex])) {
                $this->contacts[$uindex] = [];
            }

            if (!is_array($this->contacts[$uindex][$cindex])) {
                $this->contacts[$uindex][$cindex] = [];
            }

            $this->contacts[$uindex][$cindex][] = $one;

            ksort($this->contacts[$uindex]);
        }

        ksort($this->contacts);
    }

}
