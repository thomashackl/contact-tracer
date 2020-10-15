<form class="default" action="<?php echo $controller->link_for('coursetracer/do_register', $date->id, $user) ?>"
      method="post">
    <header>
        <h1>
            <?php echo sprintf(
                dgettext('tracer', 'Registrierung der Anwesenheit beim Präsenztermin %s'),
                trim($date->getFullname() . $date->getRoomName())) ?>
        </h1>
    </header>
    <section>
        <label for="contact">
            <?php echo dgettext('tracer', 'Wie möchten Sie bei einem Coronafall kontaktiert werden? ' .
                'Geben Sie hier eine E-Mail- oder Postadresse oder Telefonnummer an:') ?>
        </label>
        <textarea name="contact" cols="75" rows="2"><?php echo htmlReady($lastContact) ?></textarea>
        <div class="disclaimer">
            <?php echo dgettext('tracer', 'Die hier erfassten Daten werden ausschließlich zum ' .
                'Zweck der Kontaktverfolgung gespeichert.') ?>
            <?php if (Config::get()->CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION) : ?>
                <?php echo sprintf(dgettext('tracer', 'Nach %u Tagen werden diese Daten automatisch gelöscht.'),
                    Config::get()->CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION) ?>
            <?php endif ?>
        </div>
    </section>
    <footer data-dialog-button>
        <?php echo Studip\Button::createAccept(dgettext('tracer', 'Anwesenheit registrieren'), 'store') ?>
        <?php echo Studip\LinkButton::createCancel(dgettext('tracer', 'Abbrechen'),
            $controller->link_for('coursetracer')) ?>
    </footer>
</form>
