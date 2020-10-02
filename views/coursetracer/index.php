<?php if ($enabled) : ?>
    <div id="date">

    </div>
    <div id="date-qr-code" data-date-id="<?php echo $date_id ?>">
        <?php echo $qr->render($url) ?>
    </div>
    <div id="date-qr-code-url"><?php echo $url ?></div>
<?php endif;
