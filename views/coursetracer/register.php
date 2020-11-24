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
            <span class="required">
                <?php echo dgettext('tracer', 'Bitte geben Sie eine Telefonnummer an, ' .
                    'unter der Sie sicher erreichbar sind. Optional können Sie zusätzlich Ihre bevorzugte ' .
                    'E-Mail-Adresse angeben.') ?>
            </span>
        </label>
        <textarea name="contact" cols="75" rows="2" required><?php echo htmlReady($contactData->contact) ?></textarea>
        <div class="disclaimer">
            <?php echo formatReady($disclaimer) ?>
            <?php if (Config::get()->CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION) : ?>
                <?php echo sprintf(dgettext('tracer', 'Nach %u Tagen werden die Daten automatisch gelöscht.'),
                    Config::get()->CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION) ?>
            <?php endif ?>
        </div>
    </section>
    <?php if ($must_accept_disclaimer != '') : ?>
        <section>
            <span class="required">
                <input type="checkbox" name="accepted" value="1" id="accpted" required>
                <label class="undecorated" for="accpted">
                    <?php echo dgettext('tracer',
                        'Ich bestätige, die oben genannten Angaben gelesen und akzeptiert zu haben.') ?>
                </label>
            </span>
        </section>
    <?php endif ?>
    <footer data-dialog-button>
        <?php echo Studip\Button::createAccept(dgettext('tracer', 'Anwesenheit registrieren'), 'store') ?>
        <?php echo Studip\LinkButton::createCancel(dgettext('tracer', 'Abbrechen'),
            $controller->link_for('coursetracer')) ?>
    </footer>
</form>
