<?php

include_once("../functions.php");

if ($_POST['action'] == 'EDIT') {

    if (
        !empty($_POST['username'])
        && !empty($_POST['password'])
        && !empty($_POST['email'])
        && !empty($_POST['tel']
        && !empty($_POST['userId']))
    ) {
        $username = injection_replace($_POST['username']);
        $password = injection_replace($_POST['password']);
        $email = injection_replace($_POST['email']);
        $tel = injection_replace($_POST['tel']);
        $userId = injection_replace($_POST['userId']);
        $date = $_POST['date'];

        $result = editUser($username, $password, $tel, $email, $userId, $date);

        if ($result['stt'] == 'ok') {
            response('ok', 'اطلاعات شما به درستی به روزرسانی شد', $result['user']);
        } elseif ($result['stt'] == 'err') {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }
    } else {
        response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
    }



} elseif ($_POST['action'] == 'BYID') {
    $id = $_POST['id'];
    response('ok', '', getUserById($id));
}


?>