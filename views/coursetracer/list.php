<?php if (count($entries) > 0) : ?>
<table class="default">
    <caption>
        <?php echo htmlReady(sprintf('%s', $date->getFullname() .
            ($date->getRoom() ? '' : ' ' . $date->getRoomName()))) ?>
    </caption>
    <colgroup>
        <col width="5">
        <col>
        <col width="20">
        <col width="20">
    </colgroup>
    <thead>
        <tr>
            <th>#</th>
            <th><?php echo dgettext('tracer', 'Name') ?></th>
            <th><?php echo dgettext('tracer', 'Registriert?') ?></th>
            <th><?php echo dgettext('tracer', 'Aktion') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $counter = 1; foreach ($entries as $entry) : ?>
            <tr>
                <td><?php echo $counter ?>.</td>
                <td>
                    <?php echo htmlReady($entry['Nachname'] . ', ' . $entry['Vorname'] .
                        ' (' . $entry['username'] . ')') ?>
                </td>
                <td>
                    <?php if ($entry['registered']) : ?>
                        <?php echo Icon::create('accept', 'info')->asImg(20) ?>
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($entry['registered']) : ?>
                        <a href="<?php echo $controller->link_for('coursetracer/unregister', $date->id, $entry['user_id']) ?>">
                            <?php echo Icon::create('decline', 'clickable',
                                ['title' => dgettext('tracer',
                                    'Anwesenheit entfernen') ])->asImg(20) ?>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo $controller->link_for('coursetracer/register', $date->id, $entry['user_id']) ?>">
                            <?php echo Icon::create('add', 'clickable',
                                ['title' => dgettext('tracer',
                                    'Anwesenheit entfernen') ])->asImg(20) ?>
                        </a>
                    <?php endif ?>
                </td>
            </tr>
        <?php $counter++; endforeach ?>
    </tbody>
</table>
<?php else : ?>

<?php endif;
