<form class="default" action="<?php echo $controller->link_for('tracersearch/do') ?>" method="post">
    <section>
        <label>
            <span class="required">
                <?php echo dgettext('tracer', 'Person') ?>
            </span>
        </label>
        <?php echo $qs->render() ?>
    </section>
    <section>
        <label for="start">
            <?php echo dgettext('tracer', 'Zeitraum des Kontakts') ?>
        </label>
        <input name="start" id="start" type="text" size="30" data-datetime-picker>
        <label for="end">
            <?php echo dgettext('tracer', 'bis') ?>
        </label>
        <input name="end" id="end" type="text" size="30" data-datetime-picker='{">":"#start"}'>
    </section>
    <footer data-dialog-button>
        <?php echo Studip\Button::createAccept(dgettext('tracer', 'Kontakte finden'), 'store') ?>
    </footer>
</form>
