<?php

include_once("../functions.php");

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = getUser($username, $password);

    if ($user['password'] == $password) {
        response('ok', '', [
            'user' => $user,
            'contact' => getContactInfo(),
            'categories' => getAllCategries(),
            'colors' => getAllColors(),
            'sizes' => getAllSizes(),
            'products' => getAllProducts(),
            'messages' => getAllMessages(),
        ]);
    } else {
        response('err', 'رمز عبور یا نام کاربری اشتباه است');
    }
} else {
    response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
}

?>