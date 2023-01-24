<?php

include_once("../functions.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


if ($_POST['action'] == 'ADD') {

    if (
        !empty($_POST['name'])
        && !empty($_POST['image'])
    ) {

        $name = injection_replace($_POST['name']);
        $date = $_POST['date'];

        $uniqueId = uniqid();

        if (addCategory($name, $uniqueId, $_POST['image'], $date)) {
            response('ok', 'دسته بندی مورد نظر به درستی افزوده شد', getAllCategries());
        } else {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }

        ;

    } else {
        response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
    }



} elseif ($_POST['action'] == 'DELETE') {


    if (
        !empty($_POST['id'])
        && !empty($_POST['date'])
    ) {
        $id = $_POST['id'];
        $date = $_POST['date'];

        if (deleteCategory($id, $date) == 1) {
            response('ok', 'دسته بندی مورد نظر حذف شد', getAllCategries());
        } elseif (deleteCategory($id, $date) == 0) {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }
    } else {
        response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
    }



} elseif ($_POST['action'] == 'EDIT') {


    if (
        !empty($_POST['name'])
        && !empty($_POST['image'])
    ) {

        $name = injection_replace($_POST['name']);
        $uniqueId = $_POST['id'];
        $isOldImg = $_POST['isOldImg'];
        $date = $_POST['date'];

        if (editCategory($name, $uniqueId, $_POST['image'], $date, $isOldImg)) {
            response('ok', 'دسته بندی مورد نظر به درستی ویرایش شد', getAllCategries());
        } else {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }

    } else {
        response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
    }


} elseif ($_POST['action'] == 'ALL') {
    response('ok', '', getAllCategries());
}


?>