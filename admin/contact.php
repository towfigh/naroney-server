<?php

include_once("../functions.php");

if ($_POST['action'] == 'EDIT') {

    if (
        !empty($_POST['tel'])
        && !empty($_POST['phone1'])
        && !empty($_POST['phone2'])
        && !empty($_POST['address'])
        && !empty($_POST['insta'])
        && !empty($_POST['email'])
    ) {
        $tel = injection_replace($_POST['tel']);
        $phone1 = injection_replace($_POST['phone1']);
        $phone2 = injection_replace($_POST['phone2']);
        $address = injection_replace($_POST['address']);
        $insta = injection_replace($_POST['insta']);
        $email = injection_replace($_POST['email']);
        $subTop = injection_replace($_POST['subTop']);
        $subDown = injection_replace($_POST['subDown']);
        $about1 = injection_replace($_POST['about1']);
        $about2 = injection_replace($_POST['about2']);
        $date = $_POST['date'];

        $result = editContactInfo($tel, $phone1, $phone2, $address, $insta, $email, $subTop, $subDown, $about1, $about2, $date);

        if ($result['stt'] == 1) {
            response('ok', 'اطلاعات تماس به درستی به روزرسانی شد', $result['contact']);
        } else {
            response('err', 'مشکلی پیش آمده است لطفا مجددا تلاش کنید', null);
        }
    } else {
        response('err', 'لطفا اطلاعات را به صورت کامل وارد کنید');
    }


} elseif ($_POST['action'] == 'ALL') {
    response('ok', '', getContactInfo());
}

?>