<?php ob_start()?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title><?php echo APP_TITLE ?></title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="<?php echo APP_DESCRIPTION ?>" />
        <meta name="author" content="<?php echo APP_AUTHOR ?>" />
        
        <?php include_template('favicon')?>

        <!-- CSS -->
        <link href="<?= getAsset('jquery-ui-1.13.3/jquery-ui.min.css') ?>" rel="stylesheet" />
        <link href="<?= getAsset('fontawesome/css/all.min.css') ?>" rel="stylesheet" />
        <link href="<?= getAsset('bootstrap-5.3.3-dist/css/bootstrap.min.css') ?>" rel="stylesheet" />

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
            integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD"
            crossorigin="anonymous"
        >

        <link href="<?= getAsset('css/globals.css') ?>" rel="stylesheet" />
        <link href="<?= getAsset('css/main.css') ?>" rel="stylesheet" />
        <link href="<?= getAsset('css/' . nqv::getSection() . '.css') ?>" rel="stylesheet" />
        <link href="<?= getAsset('css/user.css') ?>" rel="stylesheet" />

        <!-- JS -->
        <script src="<?= getAsset('js/jquery-3.7.1.min.js') ?>"></script>
        <script src="<?= getAsset('jquery-ui-1.13.3/jquery-ui.min.js') ?>"></script>
        <script src="<?= getAsset('bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js') ?>"></script>

        <!-- DataTables -->
        <link href="https://cdn.datatables.net/v/bs5/dt-2.1.6/datatables.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/v/bs5/dt-2.1.6/datatables.min.js"></script>

        <script src="<?= getAsset('js/nqv-swap.js') ?>"></script>
        <script src="<?= getAsset('js/nqv.js') ?>"></script>
        <script src="<?= getAsset('js/nqv-image-preview.js') ?>"></script>

        <style><?php foreach(getEnabledLangs() as $lang){
            if ($lang === $_SESSION['CURRENT_LANGUAGE']) continue;
                echo '.lang' . $lang . ' {display:none;}';
            }
        ?></style>
    </head>

    <body class="<?php echo nqv::getSection()?>">
        <?php if (hasHeader()) getHeader(); ?>

        <main class="<?php echo implode(' ', $mainClass) ?>">
            <?php if (is_null(include_template($template))) include_template('error') ?>
        </main>

        <?php if (hasFooter()) getFooter(); ?>
    </body>
</html>

<?php echo ob_get_clean() ?>

<script type="text/javascript">
    $(function () {
        if ($('.notifications-container').length) {
            let height = $('.notifications-container').outerHeight();
            $('.notifications-container').css('margin-top', $('header').outerHeight());
            height += parseInt($('main').css('padding-top'));
            $('main').css({ 'padding-top': height });
        }
    });

    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
