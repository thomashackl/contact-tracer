<form class="default" action="<?php echo $controller->link_for('tracersearch/get_contact_data') ?>" method="post">
    <section>
        <label for="users">
            <?php if (Config::get()->CONTACT_TRACER_MATRICULATION_DATAFIELD_ID != null) : ?>
                <?php echo dgettext('tracer', 'Bitte hier eine durch Komma oder Zeilenumbruch ' .
                    'getrennte Liste von Kennungen oder Matrikelnummern angeben:') ?>
            <?php else : ?>
                <?php echo dgettext('tracer',
                    'Bitte hier eine durch Komma oder Zeilenumbruch getrennte Liste von Kennungen angeben:') ?>
            <?php endif ?>
        </label>
        <textarea name="users" id="users" cols="75" rows="4"></textarea>
    </section>
    <footer data-dialog-button>
        <?php echo Studip\Button::createAccept(
                dgettext('tracer', 'Kontaktinformationen exportieren'), 'export') ?>
    </footer>
</form>
