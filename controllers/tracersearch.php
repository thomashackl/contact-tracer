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
    }

    public function index_action()
    {
        Navigation::activateItem('/search/tracer/search');

        $this->qs = Quicksearch::get('user', new StandardSearch('user_id'))->withButton();
    }

}
