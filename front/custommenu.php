<?php

use GlpiPlugin\CustomMenu\CustomMenu;

include ('../../../inc/includes.php');

Session::checkLoginUser();
Session::checkRight(CustomMenu::$rightname, READ);

Html::header(
    CustomMenu::getTypeName(Session::getPluralNumber()),
    $_SERVER['PHP_SELF'],
    "config",
    CustomMenu::class,
    "custommenu"
);

Search::show(CustomMenu::class);

Html::footer();
