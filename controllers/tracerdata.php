<?php

/**
 * Class TracerData.php
 * Contact data for user
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

class TracerdataController extends AuthenticatedController
{

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->current_user = User::findOneByUsername(Request::username('username') ?: User::findCurrent()->username);

        if ($this->current_user->id != User::findCurrent()->id &&
                !$GLOBALS['perm']->have_profile_perm('admin', $this->current_user->id)) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        $this->flash = Trails_Flash::instance();

        Navigation::activateItem('/profile/tracer');
    }

    public function index_action()
    {
        $this->data = ContactTracerContactData::find($this->current_user->id);
    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $tz = new DateTimeZone('Europe/Berlin');

        $data = ContactTracerContactData::find($this->current_user->id);
        if (!$data) {
            $data = new ContactTracerContactData();
            $data->user_id = $this->current_user->id;
            $data->mkdate = new DateTime('now', $tz);
        }

        $data->contact = Request::get('contact');
        $data->chdate = new DateTime('now', $tz);

        if ($data->store() !== false) {
            PageLayout::postSuccess(dgettext('tracer', 'Ihre Kontaktdaten wurden gespeichert.'));
        } else {
            PageLayout::postError(dgettext('tracer', 'Ihre Kontaktdaten konnten nicht gespeichert werden.'));
        }

        $this->relocate('tracerdata');
    }

}
