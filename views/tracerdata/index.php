<form class="default" action="<?php echo $controller->link_for('tracerdata/store') ?>" method="post">
    <section>
        <label for="contact">
            <?php echo dgettext('tracer', 'Bitte gehen Sie eine Telefonnummer an, ' .
                'unter der Sie sicher erreichbar sind. Optional können Sie zusätzlich Ihre bevorzugte ' .
                'E-Mail-Adresse angeben.') ?>
        </label>
        <textarea name="contact" cols="75" rows="2"><?php echo htmlReady($data->contact) ?></textarea>
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
        <?php echo CSRFProtection::tokenTag() ?>
        <?php echo Studip\Button::createAccept(dgettext('tracer', 'Speichern'), 'store') ?>
    </footer>
</form>
