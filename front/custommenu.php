<?php

use GlpiPlugin\CustomMenu\CustomMenu;
use Html;
use Search;
use Session;

include ('../../../inc/includes.php');

Session::checkLoginUser();
Session::checkRight(CustomMenu::$rightname, READ);

// Handle bulk delete
if (isset($_POST['massiveaction']) && isset($_POST['item'])) {
    Session::checkRight(CustomMenu::$rightname, DELETE);
    
    switch ($_POST['massiveaction']) {
        case 'delete':
            $custommenu = new CustomMenu();
            foreach ($_POST['item'] as $id) {
                $custommenu->delete(['id' => $id]);
            }
            
            Session::addMessageAfterRedirect(
                __('Selected custom menu entries have been deleted.', 'custommenu'),
                true,
                INFO
            );
            break;
    }
    Html::redirect($_SERVER['PHP_SELF']);
}

Html::header(
    CustomMenu::getTypeName(Session::getPluralNumber()),
    $_SERVER['PHP_SELF'],
    "plugins",
    CustomMenu::class,
    "custommenu"
);

Search::show(CustomMenu::class);

Html::footer();
