<?php if ($enabled) : ?>
    <div id="date-qr-code" data-date-id="<?php echo $date_id ?>">
        <?php echo $qr->render($url) ?>
    </div>
<?php endif;
