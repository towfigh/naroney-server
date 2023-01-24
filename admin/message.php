<?php

include_once("../functions.php");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


if ($_POST['action'] == 'SEND') {

    if (
        !empty($_POST['name'])
        && !empty($_POST['mobile'])
        && !empty($_POST['subject'])
        && !empty($_POST['text'])
    ) {

        $name = injection_replace($_POST['name']);
        $mobile = injection_replace($_POST['mobile']);
        $subject = injection_replace($_POST['subject']);
        $text = injection_replace($_POST['text']);

        $date = $_POST['date'];

        if (sendMessage($name, $mobile, $subject, $text, $date)) {
            response('ok', 'پیغام شما به درستی به پشتیبانی ارسال شد', null);
        } else {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }

    } else {
        response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
    }


} elseif ($_POST['action'] == 'SEEN') {

    $id = $_POST['id'];
    $date = $_POST['date'];

    if (seenMessage($id, $date)) {
        response('ok', '', null);
    } else {
        response('err', 'خطا در برقراری ارتباط با سرور', null);
    }

} elseif ($_POST['action'] == 'DELETE') {

    $id = $_POST['id'];
    $date = $_POST['date'];


    if (deleteMessage($id, $date)) {
        response('ok', 'پیام مورد نظر به درستی حذف شد', getAllMessages());
    } else {
        response('err', 'خطا در برقراری ارتباط با سرور', null);
    }

} elseif ($_POST['action'] == 'ALL') {

    response('ok', '', getAllMessages());

}

?>