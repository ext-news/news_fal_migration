<?php
defined('TYPO3_MODE') or die();

$boot = function () {


    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \GeorgRinger\NewsFalMigration\Command\NewsFalMigrationCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \GeorgRinger\NewsFalMigration\Command\NewsCategoryMigrationCommandController::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \GeorgRinger\NewsFalMigration\Command\NewsCeMigrationCommandController::class;
};

$boot();
unset($boot);
