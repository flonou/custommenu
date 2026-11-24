<?php

use GlpiPlugin\CustomMenu\CustomMenu;
use Html;
use Search;

include ('../../../inc/includes.php');

Html::header(
    CustomMenu::getTypeName(Session::getPluralNumber()),
    $_SERVER['PHP_SELF'],
    "plugins",
    CustomMenu::class,
    "custommenu"
);

Search::show(CustomMenu::class);

Html::footer();
