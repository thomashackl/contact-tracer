<?php if (count($dates) > 0) : ?>
    <table class="default">
        <colgroup>
            <col width="250">
            <col width="50">
        </colgroup>
        <thead>
            <tr>
                <th><?php echo dgettext('tracer', 'Termin') ?></th>
                <th><?php echo dgettext('tracer', 'Registrierung') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dates as $date) : ?>
                <tr>
                    <td>
                        <?php echo htmlReady($date->getFullname() .
                            (($room = $date->getRoomName()) ? ' ' . $room : '')) ?>
                    </td>
                    <td>
                        <?php if (ContactTracerEntry::findByUserAndDate(User::findCurrent()->id, $date->id)) : ?>
                            <?php if (Config::get()->CONTACT_TRACER_ENABLE_SELF_DEREGISTRATION) : ?>
                                <a href="<?php echo $controller->link_for('coursetracer/unregister', $date->id) ?>">
                                    <?php echo Icon::create('minus', 'clickable',
                                        ['title' => dgettext('tracer',
                                            'Anwesenheit entfernen') ])->asImg(20) ?>
                                </a>
                            <?php else : ?>
                                <?php echo Icon::create('accept', 'clickable',
                                    ['title' => dgettext('tracer', 'Sie sind bereits registriert') ])->asImg(20) ?>
                            <?php endif ?>
                        <?php else : ?>
                            <a href="<?php echo $controller->link_for('coursetracer/register', $date->id) ?>">
                                <?php echo Icon::create('add', 'clickable',
                                    ['title' => dgettext('tracer', 'Anwesenheit erfassen') ])->asImg(20) ?>
                            </a>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else : ?>
    <?php echo MessageBox::info(
            dgettext('tracer', 'Es wurden keine vergangenen oder aktuellen Termine gefunden.')) ?>
<?php endif;
