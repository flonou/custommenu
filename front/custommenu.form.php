<?php

use GlpiPlugin\CustomMenu\CustomMenu;
use Html;

include ('../../../inc/includes.php');

Session::checkLoginUser();
Session::checkRight(CustomMenu::$rightname, CREATE);

$custommenu = new CustomMenu();

if (isset($_POST["add"])) {
    $newID = $custommenu->add($_POST);

    if ($newID) {
        Html::redirect(CustomMenu::getFormURL() . "?id=" . $newID);
    }
    Html::back();
} else if (isset($_POST["delete"])) {
    $custommenu->delete($_POST);
    $custommenu->redirectToList();
} else if (isset($_POST["restore"])) {
    $custommenu->restore($_POST);
    $custommenu->redirectToList();
} else if (isset($_POST["purge"])) {
    $custommenu->delete($_POST, 1);
    $custommenu->redirectToList();
} else if (isset($_POST["update"])) {
    $custommenu->update($_POST);
    Html::back();
} else {
    // Fill id, if missing.
    $ID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    Html::header(
        CustomMenu::getTypeName(),
        $_SERVER['PHP_SELF'],
        "config",
        CustomMenu::class,
        "custommenu"
    );

    $custommenu->display(['id' => $ID]);

    Html::footer();
}
