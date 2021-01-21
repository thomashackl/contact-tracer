<?php
/**
 * ContactTracer.php
 *
 * Plugin for contact tracing across courses.
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

require_once('ContactTracerCronjob.php');

class ContactTracer extends StudIPPlugin implements StandardPlugin, SystemPlugin, PrivacyPlugin, HomepagePlugin {

    public function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        // Localization
        bindtextdomain('tracer', realpath(__DIR__.'/locale'));

        if ($GLOBALS['perm']->have_perm('root') ||
                $GLOBALS['user']->getAuthenticatedUser()->hasRole('Kontaktverfolgung')) {
            $navigation = new Navigation($this->getDisplayName(),
                PluginEngine::getURL($this, [], 'tracersearch'));

            $navigation->addSubNavigation('search',
                new Navigation(dgettext('tracer', 'Suche'),
                    PluginEngine::getURL($this, [], 'tracersearch')));

            $navigation->addSubNavigation('contact_data',
                new Navigation(dgettext('tracer', 'Kontaktdatenexport'),
                    PluginEngine::getURL($this, [], 'tracersearch/contact_data')));

            Navigation::addItem('/search/tracer', $navigation);
        }

        $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, [], 'tracerdata'));
        Navigation::addItem('/profile/tracer', $navigation);
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName()
    {
        return dgettext('tracer', 'Kontaktverfolgung');
    }

    public function getVersion()
    {
        $metadata = $this->getMetadata();
        return $metadata['version'];
    }

    /**
     * @see StandardPlugin::getIconNavigation()
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        return null;
    }

    /**
     * @see StandardPlugin::getTabNavigation()
     */
    public function getTabNavigation($course_id)
    {
        if ($GLOBALS['user']->id == 'nobody') {
            return [];
        }

        $tracer = new Navigation($this->getDisplayName());
        $tracer->addSubNavigation('qr', new Navigation(dgettext('tracer', 'QR-Code'),
            PluginEngine::getURL($this, [], 'coursetracer')));
        $tracer->addSubNavigation('manual', new Navigation(dgettext('tracer', 'Anwesenheit manuell erfassen'),
            PluginEngine::getURL($this, [], 'coursetracer/manual')));

        return compact('tracer');
    }

    /**
     * @see StudipModule::getMetadata()
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $metadata['summary'] = dgettext('tracer', 'Kontaktverfolgung via QR-Codes');
        $metadata['description'] = dgettext('tracer', 'Erfassen Sie Kontaktlisten über QR-Codes pro Termin und Veranstaltung');
        $metadata['category'] = _('Lehr- und Lernorganisation');
        $metadata['icon'] = Icon::create('code-qr', 'info');

        return $metadata;
    }

    /**
     * @see StandardPlugin::getInfoTemplate()
     */
    public function getInfoTemplate($course_id)
    {
        return null;
    }

    public function perform($unconsumed_path) {
        $range_id = Request::option('cid', Context::get()->id);

        URLHelper::removeLinkParam('cid');
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, [], null), '/'),
            'register'
        );
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher->current_plugin = $this;
        $dispatcher->range_id       = $range_id;
        $dispatcher->dispatch($unconsumed_path);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public function exportUserData(StoredUserData $storage)
    {
        $entries = ContactTracerEntry::findByUser_id($storage->user_id);

        if ($entries) {
            $field_data = [];
            foreach ($entries as $row) {
                $field_data[] = [
                    'course' => $row->course->getFullname(),
                    'date' => $row->date->getFullname(),
                    'start' => $row->start->format('d.m.Y H:i'),
                    'end' => $row->end->format('d.m.Y H:i')
                ];
            }
            if ($field_data) {
                $storage->addTabularData(
                    dgettext('tracer', 'Kontaktverfolgung'), 'contact_tracer', $field_data);
            }
        }

    }

    /**
     * @see HomepagePlugin::getHomepageTemplate()
     */
    public function getHomepageTemplate($user_id)
    {
        return null;
    }

    public static function onEnable($pluginId) {
        parent::onEnable($pluginId);
        ContactTracerCronjob::register()->schedulePeriodic(11, 0)->activate();
    }

    public static function onDisable($pluginId) {
        ContactTracerCronjob::unregister();
        parent::onDisable($pluginId);
    }

}
