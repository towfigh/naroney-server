<?php

include_once("../functions.php");

if ($_POST['action'] == 'ADD') {

    if (
        !empty($_POST['name'])
        && !empty($_POST['code'])
    ) {
        $name = injection_replace($_POST['name']);
        $code = $_POST['code'];
        $date = $_POST['date'];

        $sql = "INSERT INTO colors (name, code, is_active, created_at, updated_at, deleted_at) 
    VALUES ('$name','$code',1,'$date','$date',null)";

        if ($conn->query($sql) === TRUE) {
            response('ok', 'رنگ انتخابی به درستی اضافه شد', getAllColors());
        } else {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', $conn->error);
        }
    } else {
        response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
    }



} elseif ($_POST['action'] == 'DELETE') {

    $id = $_POST['id'];
    $date = $_POST['date'];

    $query = "UPDATE colors SET deleted_at='$date', updated_at ='$date'
     WHERE id='$id'";

    if ($conn->query($query) === TRUE) {
        response('ok', 'رنگ مورد نظر به درستی حذف شد', getAllColors());
    } else {
        response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', $conn->error);
    }


} elseif ($_POST['action'] == 'EDIT') {

    $id = $_POST['id'];
    $name = injection_replace($_POST['name']);
    $code = $_POST['code'];
    $date = $_POST['date'];

    $query = "UPDATE colors SET name='$name', code='$code', updated_at ='$date'
     WHERE id='$id'";

    if ($conn->query($query) === TRUE) {
        response('ok', 'رنگ مورد نظر به درستی ویرایش شد', getAllColors());
    } else {
        response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', $conn->error);
    }


} elseif ($_POST['action'] == 'ALL') {
    response('ok', '', getAllColors());
}


?>