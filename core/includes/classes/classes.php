<?php

$classes = [
    'App.php',
    'Request.php',
    'Response.php',
    'nqv.php',
    'nqvDB.php',
    'nqvSession.php',
    'nqvDbObject.php',
    'nqvDbField.php',
    'nqvDbTable.php',
    'nqvSession_types.php',
    'nqvNotifications.php',
    'nqvUsers.php',
    'nqvAdvertisers.php',
    'nqvList.php',
    'nqvSearch.php',
    'nqvElement.php',
    'nqvImageProcessor.php',
    'nqvMainImages.php',
    'nqvImage.php',
    'nqvHolders.php',
    'nqvActivities.php',
    'nqvInscription.php',
    'nqvCategories.php',
    'nqvStandapplications.php',
    'nqvMail.php',
    'nqvCities.php',
    'nqvStates.php',
    'nqvTags.php',
    'nqvSessionType.php',
    'nqvConfig.php',
    'nqvWidgets.php',
    'nqvMdParser.php',
    'nqvHtmlParser.php',
    'nqvPages.php'
];

foreach ($classes as $class) {
    $userClass = USER_CLASSES_PATH . $class;
    $coreClass = CLASSES_PATH . $class;

    if (is_file($userClass)) {
        require_once $userClass;
    } else {
        require_once $coreClass;
    }
}
