<?php if ($enabled) : ?>
    <div id="date">
        <?php echo sprintf(dgettext('tracer', 'QR-Code für %s'),
            trim($date->getFullname() . ' ' . $date->getRoomName())) ?>
    </div>
    <div id="date-qr-code" data-date-id="<?php echo $date_id ?>">
        <?php echo $qr->render($url) ?>
    </div>
    <div id="date-qr-code-url"><?php echo $url ?></div>
<?php endif;
