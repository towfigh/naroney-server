<?php

include_once("../functions.php");

if ($_POST['action'] == 'ADD') {

    if (
        !empty($_POST['size'])
    ) {
        $size = injection_replace($_POST['size']);
        $date = $_POST['date'];

        $sql = "INSERT INTO sizes (size, created_at, deleted_at) 
    VALUES ('$size', '$date',null)";

        if ($conn->query($sql) === TRUE) {
            response('ok', 'سایز مورد نظر به درستی اضافه شد', getAllSizes());
        } else {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', $conn->error);
        }
    } else {
        response('err', 'لطفا اطلاعات را به صورت صحیح وارد کنید');
    }



} elseif ($_POST['action'] == 'DELETE') {

    $id = $_POST['id'];
    $date = $_POST['date'];

    $query = "UPDATE sizes SET deleted_at='$date' WHERE id='$id'";

    if ($conn->query($query) === TRUE) {
        response('ok', 'سایز مورد نظر به درستی حذف شد', getAllSizes());
    } else {
        response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', $conn->error);
    }



} elseif ($_POST['action'] == 'ALL') {
    response('ok', '', getAllSizes());
}



?>