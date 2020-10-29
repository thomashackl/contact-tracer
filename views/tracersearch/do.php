<?php if (count($contacts) > 0) : ?>
    <table class="default">
        <caption>
            <?php echo sprintf(
                dngettext(
                    'tracer',
                    'Eine Kontaktperson von %2$s im Zeitraum %3$s - %4$s',
                    '%u Kontaktpersonen von %2$s im Zeitraum %3$s - %4$s',
                    count($contacts)),
                    count($contacts), $name, $start, $end) ?>
        </caption>
        <colgroup>
            <col>
            <col>
            <col>
        </colgroup>
        <thead>
            <tr>
                <th><?php echo dgettext('tracer', 'Name') ?></th>
                <th><?php echo dgettext('tracer', 'Erreichbar unter') ?></th>
                <th><?php echo dgettext('tracer', 'Gemeinsame Präsenz') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $user => $data) : ?>
                <tr>
                    <td><?php echo htmlReady($user) ?></td>
                    <td><?= nl2br(htmlReady($data['contact'])) ?></td>
                    <td>
                        <ul>
                            <?php foreach ($data['courses'] as $course => $data) : ?>
                                <li>
                                    <?php echo htmlReady($course) ?>
                                    (<?php echo htmlReady(implode(', ', $data['lecturers'])) ?>)
                                    <ul>
                                        <?php foreach ($data['dates'] as $date) : ?>
                                            <li>
                                                <?php echo $date->date->getFullname() . ' ' .
                                                    $date->date->getRoomName() ?>
                                            </li>
                                        <?php endforeach ?>
                                    </ul>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else : ?>
    <?php echo MessageBox::info(sprintf(dgettext('tracer',
        'Es wurden keine Kontakte von %s im Zeitraum %s - %s gefunden.'), $name, $start, $end)) ?>
<?php endif ?>
<?php echo Studip\LinkButton::create(dgettext('tracer', 'Zurück zur Suche'),
    $controller->link_for('tracersearch'));

