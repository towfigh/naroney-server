<?php

include_once("../functions.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


if ($_POST['action'] == 'ADD') {

    if (
        !empty($_POST['name'])
        && !empty($_POST['category'])
        && !empty($_POST['size'])
        && !empty($_POST['color'])
        && !empty($_POST['desc'])
        && !empty($_POST['image'])
    ) {

        $name = injection_replace($_POST['name']);
        $category = $_POST['category'];
        $size = $_POST['size'];
        $color = $_POST['color'];
        $sell = $_POST['sell'];
        $rent = $_POST['rent'];
        $desc = injection_replace($_POST['desc']);
        $images = json_decode($_POST['image']);
        $date = $_POST['date'];



        if (addProduct($name, $category, $size, $color, $sell, $rent, $desc, $images, $date)) {
            response('ok', 'محصول مورد نظر به درستی افزوده شد', getAllProducts());
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
        $code = $_POST['code'];
        $date = $_POST['date'];

        if (deleteProduct($id, $code, $date) == 1) {
            response('ok', 'محصول مورد نظر حذف شد', getAllProducts());
        } elseif (deleteProduct($id, $code, $date) == 0) {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }
    } else {
        response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
    }


} elseif ($_POST['action'] == 'EDIT') {


    if (
        !empty($_POST['name'])
        && !empty($_POST['image'])
        && !empty($_POST['desc'])
    ) {

        $id = $_POST['id'];
        $code = $_POST['code'];

        $name = injection_replace($_POST['name']);
        $category = $_POST['category'];
        $size = $_POST['size'];
        $color = $_POST['color'];
        $sell = $_POST['sell'];
        $rent = $_POST['rent'];
        $desc = injection_replace($_POST['desc']);
        $isOldImg = $_POST['isOld'];
        $images = json_decode($_POST['image']);
        $date = $_POST['date'];

        if (editProduct($name, $category, $size, $color, $sell, $rent, $desc, $id, $code, $images, $date, $isOldImg)) {
            response('ok', 'محصول مورد نظر به درستی ویرایش شد', getAllProducts());
        } else {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }

    } else {
        response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
    }


} elseif ($_POST['action'] == 'IMAGES') {
    $code = $_POST['code'];
    response('ok', '', getImagesByCode($code));

} elseif ($_POST['action'] == 'BYCODE') {
    $code = $_POST['code'];
    response('ok', '', getProductByCode($code));

} elseif ($_POST['action'] == 'BYCAT') {
    $cat = $_POST['cat'];
    response('ok', '', getProductsByCategory($cat));

} elseif ($_POST['action'] == 'ALL') {
    response('ok', '', getAllProducts());
}


?>