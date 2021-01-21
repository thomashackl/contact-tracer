<?php

/**
 * Creates a global role for people who may use the global contact search.
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

class TracerSearchRole extends Migration {

    public function description()
    {
        return 'Creates a global role for people who may use the global contact search.';
    }

    /**
     * Migration UP: Create a new role if necessary.
     */
    public function up()
    {
        $role = new Role(Role::UNKNOWN_ROLE_ID, 'Kontaktverfolgung');
        RolePersistence::saveRole($role);
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        $id = RolePersistence::getRoleIdByName('Kontaktverfolgung');

        if ($id) {
            $roles = RolePersistence::getAllRoles();
            RolePersistence::deleteRole($roles[$id]);
        }
    }

}
