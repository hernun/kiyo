<?php ob_start()?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title><?= $this->getTitle() ?></title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="author" content="<?= APP_AUTHOR ?>" />
        <?php include_template('favicon')?>
        <meta name="theme-color" content="#222">

        <!-- JS -->
        <script src="<?= getAsset('js/jquery-3.7.1.min.js') ?>" ></script>
        <script src="<?= getAsset('jquery-ui-1.13.3/jquery-ui.min.js') ?>" ></script>
        <script src="<?= getAsset('bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js') ?>" ></script>

        <?php if(isFront()):?>
            <link rel="canonical" href="<?= $this->getUrl() ?>" />
            <link rel="alternate" hreflang="<?= strtolower($_SESSION['CURRENT_LANGUAGE']) ?>" href="<?= $this->getUrl() ?>" />
            <?php foreach($this->getAlternates() as $pa):?>
                <link rel="alternate" hreflang="<?= strtolower($pa['lang']) ?>" href="<?= URL . '/' . strtolower($pa['lang']) . '/' . $pa['slug'] ?>" />
            <?php endforeach?>
            <link rel="alternate" hreflang="x-default" href="<?= URL ?>" />
            <meta name="description" content="<?= $this->getDescription() ?>" />

            <meta property="og:title" content="<?= $this->getTitle() ?>" />
            <meta property="og:description" content="<?= $this->getDescription() ?>" />
            <meta property="og:image" content="<?= URL . getAsset('images/og-image.jpg') ?>" />
            <meta property="og:image:width" content="1200" />
            <meta property="og:image:height" content="630" />
            <meta property="og:url" content="<?= $this->getUrl() ?>" />
            <meta property="og:type" content="website" />
            <meta property="og:locale" content="es_AR" />

            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="<?= $this->getTitle() ?>">
            <meta name="twitter:description" content="<?= $this->getDescription() ?>">
            <meta name="twitter:image" content="<?= URL . getAsset('images/x-image.jpg') ?>">
            <meta name="twitter:image:alt" content="<?= APP_TITLE ?>">

            <?php if(ENV === 'prod'):?>
                <meta name="robots" content="index, follow">
            <?php else:?>
                <meta name="robots" content="noindex, nofollow">
            <?php endif?>
        <?php else:?>
            <meta name="robots" content="noindex, nofollow">
            <!-- DataTables -->
            <link href="https://cdn.datatables.net/v/bs5/dt-2.1.6/datatables.min.css" rel="stylesheet">
            <script src="https://cdn.datatables.net/v/bs5/dt-2.1.6/datatables.min.js"></script>
        <?php endif?>

        <!-- CSS -->
        <link href="<?= getAsset('jquery-ui-1.13.3/jquery-ui.min.css') ?>" rel="stylesheet" />
        <link href="<?= getAsset('fontawesome/css/all.min.css') ?>" rel="stylesheet" />
        <link href="<?= getAsset('bootstrap-5.3.3-dist/css/bootstrap.min.css') ?>" rel="stylesheet" />
        <?php if(isAdmin()):?>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <?php endif?>

        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
            integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD"
            crossorigin="anonymous"
        >
        
        <!-- CSS -->
        <?php
            echo getStylesheetLinkTag('css/globals.css');
            echo getStylesheetLinkTag('css/main.css');
            echo getStylesheetLinkTag('css/' . nqv::getSection() . '.css');
            echo getStylesheetLinkTag('css/user.css');
        ?>

        <script src="<?= getAsset('js/nqv-swap.js') ?>"></script>
        <script src="<?= getAsset('js/nqv.js') ?>"></script>
        <script src="<?= getAsset('js/nqv-image-preview.js') ?>"></script>
        <script type="application/ld+json">
            {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "<?= APP_TITLE ?>",
            "url": "<?= URL ?>",
            "logo": "<?= URL . getAsset('images/logo.png') ?>",
            "description": "<?= $this->getDescription() ?>",
            "sameAs": []
            }
        </script>

        <style><?php foreach(getEnabledLangs() as $lang){
            if ($lang === $_SESSION['CURRENT_LANGUAGE']) continue;
                echo '.lang' . $lang . ' {display:none;}';
            }
        ?></style>
    </head>

    <body class="<?= nqv::getSection() ?>">
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
