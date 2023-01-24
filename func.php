<?php
date_default_timezone_set("iran");


if($_SESSION['site']!='main'&&$_SESSION['site']>''){
    include_once("../newcoms/Database.php");
    include_once("../newcoms/jdf.php");

//require_once '../Mandrill.php';
}else {
    include_once("newcoms/Database.php");
    include_once("newcoms/jdf.php");
//require_once 'Mandrill.php';
}

function pgsel($page=1,$num,$perpage=10,$pgsper=10,$perfix="",$pgperfix="&page="){

    $str="";
    if(!$perpage){$perpage=10;}
    $PageCount=ceil($num/$perpage);
    if(($num/$perpage)>$PageCount){$PageCount=$PageCount+1;}
    if($page==0){$page=1;}
    $from=($page-1)*$perpage;
    $to=$perpage;

    if($PageCount>1){

        $allpage=ceil($page/$pgsper);
        $thispage=($allpage-1)*$pgsper;
        $nextpage=$thispage+$pgsper;
        if($nextpage>$num){$nextpage=$num;}
        if($nextpage>$PageCount){$nextpage=$PageCount;}
        $str= "<ul class='pagination'>";
        if($thispage>1){
            $lastpg=$thispage-1;
            $str.= " <li ><a href='$perfix".$pgperfix."1'><<</a>  </li>";
            $str.= " <li><a href='$perfix".$pgperfix."$lastpg'>...</a> </li> ";
        }
        for($i=$thispage+1;$i<=$nextpage;$i++){
            if($page==$i){
                $str.="<li class='disabled'><a href='#'>$i</a></li> ";
            }else{
                $str.=" <li><a href='$perfix".$pgperfix."$i'>$i</a> </li> ";
            }
        }
        $i=$nextpage+1;
        if($nextpage<$PageCount){
            $str.= " <li><a href='$perfix".$pgperfix."$i'>...</a> </li> ";
            $str.= " <li><a href='$perfix".$pgperfix."$PageCount'>>></a> </li> ";
        }
        $str.= "</ul>";
    }
    return array($from,$to,$str,$PageCount);
}



function get_srearch_module_result($type,$module,$str,$table_name,$coms_conect,$site,$la,$tag=0,$cat=0,$news_video=0,$news_gallery=0,$news_voice=0,$start_date_str,$end_date_str){
    if($type==100)$faq_status=1;else $faq_status=2;
//echo 'id:'.$type.'start='.$start_date_str.'<br>';
    $news_video1=(injection_replace($news_video)=="") ? '0-1' : '1';
    $news_gallery1=(injection_replace($news_gallery)=="") ? '0-1' : '1';
    $news_voice1=(injection_replace($news_voice)=="") ? '0-1' : '1';
    $news_video_str='';
    if($news_video==1 or $news_gallery==1 or $news_voice==1)
        $news_video_str=' and (news_type REGEXP "'."^[0-1][$news_video1][$news_gallery1][$news_voice1][0-1]+$".'"  )';


    if($cat>0){

        $module_search_cat_condition=" and b.type=$type and b.page_id=a.id and b.cat_id=$cat ";
    }else{

        $module_search_cat_condition="  ";

    }

    if($type==100||$type==99){
        $sql="select count(a.id)as id from $table_name a  where 
		a.status=$faq_status and la='$la'
		and site='$site' $str";
    }else if($tag>0){
        $sql="select count(id)as cnt from (select count(a.id)as id from $table_name a,new_modules_catogory b,new_mudoal_label c  
		where 
		a.id=b.page_id and a.status=1 and  b.type=$type and la='$la'
		$module_search_cat_condition 	$news_video_str $end_date_str $start_date_str
		and c.type=$type and c.id=a.id and label_id=$tag
		and site='$site' $str group by page_id) q";
    }else {
        $sql="select count(id)as cnt from (select count(a.id)as id from $table_name a,new_modules_catogory b  
		where 
		a.id=b.page_id and a.status=1 and  b.type=$type and la='$la'
		$module_search_cat_condition 	$news_video_str $end_date_str $start_date_str
		and site='$site' $str group by page_id) q";
    }
    //echo $sql.'<br><br>';;
    ///  if($type==8){
    // 	echo $sql;
    //  exit;//  }
    $count=get_result($sql,$coms_conect);
    return $count;
}


function get_image_module($id,$type,$coms_conect){
    $image_name=array(1=>'news_image',11=>'content_image',9=>'galery_pic');

    if($type==5)
        return get_result("select slide_img1 from new_slideshow where page_id=$id and type=5",$coms_conect);
    else if($type==8)
        return get_modual_address($id,$coms_conect);
    else
        return get_result("select adress from new_file_path where id=$id and type=$type and name='{$image_name[$type]}'",$coms_conect);
}

function get_module_table_details($module,$coms_conect){
    $RS1['table_name']=get_result("select table_name from new_modules where link='$module'",$coms_conect);
    return " , {$RS1['table_name']} a";
}
function get_module_table($module,$q,$coms_conect,$search_type){
    if($search_type==1)
        $search_type=" = '$q'";
    if($search_type==2)
        $search_type="like '$q%'";
    if($search_type==3)
        $search_type="like '%$q'";
    if($search_type==4)
        $search_type="like '%$q%'";
    switch ($module){
        case 'download':
            $str ="and (a.en_description $search_type  or a.download_info $search_type or a.notes $search_type or a.name $search_type or a.title $search_type or a.abstract $search_type or a.description $search_type  or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;

        case 'blog':
            $str ="and (a.name $search_type or a.title $search_type or a.abstract $search_type or a.continue_blog $search_type  or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;
        case 'article':
            $str ="and (a.author $search_type or a.title $search_type or a.translator $search_type   or a.publisher $search_type or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;
        case 'gallery':
            $str ="and (a.cameraman $search_type or a.title $search_type or a.pic_source $search_type or a.deatils $search_type or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;
        case 'video':
            $str ="and (a.video_source $search_type or a.title $search_type  or a.deatils $search_type or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;
        case 'content':
            $str ="and (a.name $search_type or a.title $search_type or a.text $search_type or a.abstract $search_type or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;
        case 'page':
            $str ="and (a.name $search_type or a.title $search_type or a.text $search_type or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;
        case 'news':
            $str ="and (a.name $search_type or a.title $search_type or a.text $search_type or a.abstract $search_type or  a.meta_keyword $search_type  or  a.meta_desciption $search_type)";
            break;
    }
    return $str;
}

function new_pgsel($page=1,$num,$perpage=10,$pgsper=10,$perfix="",$pgperfix="/"){

    $str="";
    if(!$perpage){$perpage=10;}
    $PageCount=ceil($num/$perpage);
    if(($num/$perpage)>$PageCount){$PageCount=$PageCount+1;}
    if($page==0){$page=1;}
    $from=($page-1)*$perpage;
    $to=$perpage;

    if($PageCount>1){

        $allpage=ceil($page/$pgsper);
        $thispage=($allpage-1)*$pgsper;
        $nextpage=$thispage+$pgsper;
        if($nextpage>$num){$nextpage=$num;}
        if($nextpage>$PageCount){$nextpage=$PageCount;}
        $str= "<ul class='pagination'>";
        if($thispage>1){
            $lastpg=$thispage-1;
            $str.= " <li ><a href='$perfix".$pgperfix."1'><<</a>  </li>";
            $str.= " <li><a href='$perfix".$pgperfix."$lastpg'>...</a> </li> ";
        }
        for($i=$thispage+1;$i<=$nextpage;$i++){
            if($page==$i){
                $str.="<li class='disabled'><a href='#'>$i</a></li> ";
            }else{
                $str.=" <li><a href='$perfix".$pgperfix."$i'>$i</a> </li> ";
            }
        }
        $i=$nextpage+1;
        if($nextpage<$PageCount){
            $str.= " <li><a href='$perfix".$pgperfix."$i'>...</a> </li> ";
            $str.= " <li><a href='$perfix".$pgperfix."$PageCount'>>></a> </li> ";
        }
        $str.= "</ul>";
    }
    return array($from,$to,$str,$PageCount);
}




function menu_has_child($menu_id,$coms_conect){
     return	get_result("SELECT count(id) FROM new_modules_cat WHERE parent_id='$menu_id'",$coms_conect);
}


function get_header_time_create($type,$id,$coms_conect){
    $table_name=get_result("select table_name from new_modules where link = '$type'",$coms_conect);
    if($table_name>""){
        $sql44 = "SELECT date , edit_date FROM $table_name WHERE id='$id'";
        //echo $sql44;
        $result44 = $coms_conect->query($sql44);
        $row44 = $result44->fetch_assoc();
        $row44['date']=date("Y-m-d H:m:d",$row44["date"]);
        if($row44['edit_date']<10000)
            $row44['edit_date'] =$row44['date'];
        else
            $row44['edit_date']=date("Y-m-d H:m:d",$row44["edit_date"]);
        return $row44;
    }
}

function get_header_category($type,$id,$coms_conect){
    $type_id=get_result("select id from new_modules where link = '$type'",$coms_conect);
    if($type_id>""){
        $sql44 = "SELECT name FROM new_modules_cat a ,new_modules_catogory b WHERE page_id='$id' and a.type=$type_id and a.id=b.cat_id order by a.id desc";
        $result44 = $coms_conect->query($sql44);
        $row44 = $result44->fetch_assoc();
        return $row44['name'];
    }
}

function get_module_tag_function($type,$id,$coms_conect){
    $table_id=get_result("select id from new_modules where link = '$type'",$coms_conect);
    if($table_id>""){
        $sql44 = "SELECT name FROM new_mudoal_label a,new_keyword b  WHERE b.id=a.label_id and a.type='$table_id' and a.id='$id'";
        // echo $sql44;
        $result44 = $coms_conect->query($sql44);
        while($row44 = $result44->fetch_assoc()){
            $result[]=$row44["name"];
        }
    }
    return $result;
}






function get_module_image_function($type,$id,$coms_conect){
    $table_id=get_result("select id from new_modules where link = '$type'",$coms_conect);
    switch ($table_id) {
        case 1:
            $pic_name='news_image';
            break;
        case 6:
            $pic_name='download_pic';
            break;
        case 7:
            $pic_name='article_image';
            break;

        case 8:
            $pic_name='video_pic';
            break;
        case 9:
            $pic_name='galery_pic';
            break;
        case 10:
            $pic_name='blog_album';
            break;
        case 11:
            $pic_name='content_image';
            break;
        case 18:
            $pic_name='download_pic';
            break;
    }
    if($table_id>""){
        $sql44 = "SELECT adress FROM new_file_path  WHERE  type='$table_id' and id='$id' and name='$pic_name'";
        //  echo $sql44;
        $result44 = $coms_conect->query($sql44);
        $row44 = $result44->fetch_assoc();
        $temp=getimagesize($row44["adress"]);
        $pic[2]=  $row44["adress"];
        $pic[0]=  $temp[0];
        $pic[1]=  $temp[1];
        return $pic;
    }



}



function check_has_child($menu_id,$site,$la,$coms_conect){
    $sql44 = "SELECT id FROM new_menu WHERE parent_id=$menu_id and site_id='$site' and la='$la' and float_menu=0 and status=1";
     //echo $sql44;
    $result44 = $coms_conect->query($sql44);
    $row44 = $result44->fetch_assoc();
    if($row44['id']>0)
        return 1;
    else
        return 0;

}
function check_has_child_float($menu_id,$site,$la,$coms_conect){
    $sql44 = "SELECT id FROM new_menu WHERE parent_id=$menu_id and site_id='$site' and la='$la' and float_menu=1 and status=1";
    // echo $sql44;
    $result44 = $coms_conect->query($sql44);
    $row44 = $result44->fetch_assoc();
    if($row44['id']>0)
        return 1;
    else
        return 0;

}

function get_user_online($user,$session,$coms_conect){
    $count=get_result("SELECT count(id) FROM new_user_online WHERE session='$session'",$coms_conect);
    if($user>"")$type=1;else $type=0;

    $time_check=time()-300;
    if($count==0){
        $arr_slide=array("type"=>$type,"time"=>time(),"session"=>$session,'ip'=>$_SERVER['REMOTE_ADDR']);
        insert_to_data_base($arr_slide,'new_user_online',$coms_conect);
    }else{
        $condition="session='$session'";
        $arr_slide=array("type"=>$type,"time"=>time(),'ip'=>$_SERVER['REMOTE_ADDR']);
        update_data_base($arr_slide,'new_user_online',$condition,$coms_conect);
    }

    $condition1="time<$time_check";
    delete_from_data_base('new_user_online',$condition1,$coms_conect);

}


function check_insert_lang_permission_manager($manager_la,$user_id,$coms_conect){
    $sql = "SELECT * FROM new_manage_lang where manager_id=$user_id and type='l'";
    $result = $coms_conect->query($sql);
    $temp=array();
    while($row = $result->fetch_assoc()) {
        $temp[]=$row['lang_id'];
    }
    //print_r($manager_la);
    foreach($manager_la as $value){
        //echo '<br>'.$value;
        if(in_array($value,$temp))
            return 1;
        else{
            return 0;
            exit();
        }
    }
}

function check_edit_lang_permission_manager($manager_la,$user_id,$dbname,$RSconn){
    $sql = "SELECT * FROM new_manage_lang where manager_id=$user_id and type='l'";
    $result=mysql_db_query($dbname,$sql,$RSconn);
    $temp=array();
    while($row = mysql_fetch_array($result)) {
        $temp[]=$row['lang_id'];
    }
    foreach($manager_la as $value){
        if(in_array($value,$temp))
            return 1;
        else{
            return 0;
            exit();
        }
    }
}



function encript_data($str,$key){
    return $str;
    return  mcrypt_ecb (MCRYPT_3DES, $key, $str, MCRYPT_ENCRYPT);
}

function decrypt_data($str,$key){
    return $str;
    return mcrypt_ecb (MCRYPT_3DES, $key, $str, MCRYPT_DECRYPT);
}

function send_bank_email($key,$html,$subject,$Reply,$from_name,$numbers){
    try {
        $mandrill = new Mandrill($key);
        $message = array(
            'html' => "$html",
            'text' => '',
            'subject' => "$subject",
            'from_email' => "$Reply",
            'from_name' => "$from_name",
            'to' => $numbers

        ,


            'headers' => array('Reply-To' => "$Reply"),
            'important' => false,
            'track_opens' => null,
            'track_clicks' => null,
            'auto_text' => null,
            'auto_html' => null,
            'inline_css' => true,
            'url_strip_qs' => null,
            'preserve_recipients' => null,
            'view_content_link' => null,
            'bcc_address' => null,
            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
            'merge' => false

        );
        $async = false;
        $ip_pool = 'Main Pool';
        $send_at = '';
        //print_r( $message);echo '<br>';exit;
        $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
        return($result);
    } catch(Mandrill_Error $e) {
        echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
        throw $e;
    }
}


function send_email($key,$html,$subject,$Reply,$from_name,$email){
    try {
        $mandrill = new Mandrill($key);
        $message = array(
            'html' => "$html",
            'text' => '',
            'subject' => "$subject",
            'from_email' => "$Reply",
            'from_name' => "$from_name",
            'to' => array(
                array(
                    'email' => $email,
                    'name' => '',
                    'type' => 'to'
                )
            ),


            'headers' => array('Reply-To' => "$Reply"),
            'important' => false,
            'track_opens' => null,
            'track_clicks' => null,
            'auto_text' => null,
            'auto_html' => null,
            'inline_css' => true,
            'url_strip_qs' => null,
            'preserve_recipients' => null,
            'view_content_link' => null,
            'bcc_address' => null,
            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
            'merge' => false

        );
        $async = false;
        $ip_pool = 'Main Pool';
        $send_at = '';
        //print_r( $message);echo '<br>';exit;
        $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
        return($result);
    } catch(Mandrill_Error $e) {
        echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
        throw $e;
    }
}




function send_message($sms_url,$sms_user,$sms_pass,$smsfrom,$mobile,$smstext){
    $client = new SoapClient($sms_url);
    $params = array(
        'username' 	=> $sms_user,
        'password' 	=> $sms_pass,
        'senderNumbers' => $smsfrom,
        'recipientNumbers'=> $mobile,
        'messageBodies' => $smstext,

    );
    $results = $client->SendSMS( $params );
    //print_r( $params);
    $q=object2Array($results);
    return	$q['SendSMSResult']['long'];
}


function get_sms_charg($user,$pass){
    $client = new SoapClient("http://login.smspanel.org/API/Send.asmx?WSDL",array(
        'typemap' => array(
            array(
                'type_ns' => 'http://www.w3.org/2001/XMLSchema',
                'type_name' => 'long',
                'to_xml' => 'to_long_xml',
                'from_xml' => 'from_long_xml',
            ),
        ),
    ));
    $SendResult="";
    $params->username=$user;
    $params->password=$pass;
    $result= $client->Credit($params);
    $SendResult=$result->CreditResult;
    $charsh=explode(".",$SendResult);
    return $charsh[0];
}

function get_month($mah){
    switch ($mah) {
        case 1:
            return 'فروردين';
            break;

        case 2:
            return 'ارديبهشت';
            break;

        case 3:
            return 'خرداد';
            break;

        case 4:
            return 'تير';
            break;

        case 5:
            return 'مرداد';
            break;

        case 6:
            return 'شهريور';
            break;

        case 7:
            return 'مهر';
            break;

        case 8:
            return 'آبان';
            break;

        case 9:
            return 'آذر';
            break;

        case 10:
            return 'دي';
            break;

        case 11:
            return 'بهمن';
            break;

        case 12:
            return 'اسفند';
            break;

    }
}
function resolve_sms_status($str){

    switch ($str) {
        case -1:
            return 'شناسه ارسال شده اشتباه است';
            break;
        case 0:
            return 'پيامک در صف ارسال مي باشد';
            break;
        case 27:
            return 'شماره گيرنده جز ليست سياه مي باشد';
            break;
        case 1:
            return 'رسيده به گوشي';
            break;
        case 2:
            return 'نرسيده به گوشي';
            break;
        case 8:
            return 'رسيده به مخابرات';
            break;
        case 16:
            return 'نرسيده به مخابرات';
            break;
        case "":
            return 'نامشخص';
            break;


    }
}

function resolve_sms_error($str){

    switch ($str) {
        case 1:
            return 'کلمه عبور يا نام کاربري اشتباه است';
            break;
        case 2:
            return 'آرايه ها خالي مي باشد';
            break;
        case 3:
            return 'طول آرايه بيشتر از 100 مي باشد';
            break;
        case 4:
            return 'طول آرايه ها با هم تطابق ندارد';
            break;
        case 5:
            return 'امکان گرفتن پيام جديد وجود ندارد';
            break;
        case 6:
            return 'حساب کاربري غير فعال مي باشد';
            break;
        case 7:
            return 'امکان دسترسي به به خط مورد نظر وجود ندارد';
            break;
        case 8:
            return 'شماره گيرنده نا معتبر است';
            break;
        case 9:
            return 'حساب اعتبار ريالي مورد نظر را دارا نمي باشد';
            break;
        case 10:
            return 'خطايي در سيستم رخ داده است دوباره سعي کنيد';
            break;
    }
}

function to_long_xml($longVal) {
    return '<long>' . $longVal . '</long>';
}
function from_long_xml($xmlFragmentString) {
    return (string)strip_tags($xmlFragmentString);
}
function get_delivey($rec){
    $client = new SoapClient("http://login.smspanel.org/API/Send.asmx?WSDL",array(
        'typemap' => array(
            array(
                'type_ns' => 'http://www.w3.org/2001/XMLSchema',
                'type_name' => 'long',
                'to_xml' => 'to_long_xml',
                'from_xml' => 'from_long_xml',
            ),
        ),
    ));
    $SendResult="";
    $params->recId=$rec;

    $result= $client->Delivery($params);
    $SendResult=$result->DeliveryResult;
    return $SendResult;
}

function test_sms_panel($str){

    switch ($str) {
        case 0:
            return 'نام کاربري يا کلمه عبور اشتباه است';
            break;
        case 1:
            return 'اعتبار کافي نيست';
            break;
        case 2:
            return 'اکانت شما داراي محدوديت ارسال مي باشد';
            break;
        case 3:
            return 'پارامتر نام کاربري تعيين نشده است';
            break;
        case 4:
            return 'پارامتر رمز عبور تعيين نشده است';
            break;
        case 5:
            return 'پارامتر فرستنده تعيين نشده است';
            break;
        case 6:
            return 'پارامتر گيرنده تعيين نشده است';
            break;
        case 7:
            return 'پارامتر متن پيامک تعيين نشده است';
            break;
        case 8:
            return 'پارامتر فلش تعيين نشده است';
            break;
        case 9:
            return 'پارامتر شماره فرستنده تعيين نشده است';
            break;
        case 10:
            return 'پيامک شما  ارسال نشد سيستم موقتا قطع مي باشد';
            break;
        case 11:
            return ' تعداد مخاطب بيش از 80 نفر است';
            break;

    }



}

function get_date_tofolder($date){
    $time=explode(" ",$date);
    $q=explode("-",$time[0]);
    $da= gregorian_to_jalali($q[0],$q[1],$q[2]);
    $path="$da[0]/$da[1]/$da[2]";
    return $path;

}

function upload_files($file_name,$file_type,$tmp_name,$path){
    $allowedExts = array("zip");
    $temp = explode(".", $file_name);
    $extension = end($temp);
    if (($file_type == "application/octet-stream")&& in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . "<br>";
        }
        else {
            /*	if (file_exists("$path" . $_FILES["file"]["name"])) {
                    //echo $_FILES["file"]["name"] . " already exists. ";
                }
                else {*/
            move_uploaded_file($tmp_name,
                "$path");
            //echo "Stored in: " . "$path/$user_id/" . $_FILES["file"]["name"];
            //}
        }
    }
    else {
    }
}


function upload_php_files($file_name,$file_type,$tmp_name,$path){
    $allowedExts = array("php");
    $temp = explode(".", $file_name);
    $extension = end($temp);
    if (($file_type == "application/octet-stream")&& in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . "<br>";
        }
        else {
            /*	if (file_exists("$path" . $_FILES["file"]["name"])) {
                    //echo $_FILES["file"]["name"] . " already exists. ";
                }
                else {*/
            move_uploaded_file($tmp_name,
                "$path");
            //echo "Stored in: " . "$path/$user_id/" . $_FILES["file"]["name"];
            //}
        }
    }
    else {
    }
}


function upload_file($file_name,$file_type,$tmp_name,$path){
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = explode(".", $file_name);
    $extension = end($temp);
    if ((($file_type == "image/gif")
            || ($file_type == "image/jpeg")
            || ($file_type == "image/jpg")
            || ($file_type == "image/pjpeg")
            || ($file_type == "image/x-png")
            || ($file_type == "image/png"))
        && in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . "<br>";
        } else {
            /*if (file_exists("$path" . $_FILES["file"]["name"])) {
             // echo $_FILES["file"]["name"] . " already exists. ";
            } else {*/
            move_uploaded_file($tmp_name,
                "$path");
            //echo "Stored in: " . "$path/$user_id/" . $_FILES["file"]["name"];
            //}
        }
    } else {
    }
}
function upload_video($file_name,$file_type,$tmp_name,$path){
    $allowedExts = array("mp4");
    $temp = explode(".", $file_name);
    $extension = end($temp);
    if (($file_type == "video/mp4")&& in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . "<br>";
        }
        else {
            /*if (file_exists("$path" . $_FILES["file"]["name"])) {
                //echo $_FILES["file"]["name"] . " already exists. ";
            }
            else {*/
            move_uploaded_file($tmp_name,
                "$path");
            //echo "Stored in: " . "$path/$user_id/" . $_FILES["file"]["name"];
            //}
        }
    }
    else {
    }
}

function upload_csv($file_name,$file_type,$tmp_name,$path){
    $allowedExts = array("csv");
    $temp = explode(".", $file_name);
    $extension = end($temp);
    if (($file_type == "application/vnd.ms-excel")&& in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . "<br>";
        }
        else {
            /*if (file_exists("$path" . $_FILES["file"]["name"])) {
                //echo $_FILES["file"]["name"] . " already exists. ";
            }
            else {*/
            move_uploaded_file($tmp_name,
                "$path");
            //echo "Stored in: " . "$path/$user_id/" . $_FILES["file"]["name"];
            //}
        }
    }
    else {
    }
}

function upload_pdf($file_name,$file_type,$tmp_name,$path){
    $allowedExts = array("pdf");
    $temp = explode(".", $file_name);
    $extension = end($temp);
    if (($file_type == "application/pdf")&& in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . "<br>";
        }
        else {
            /*if (file_exists("$path" . $_FILES["file"]["name"])) {
                //echo $_FILES["file"]["name"] . " already exists. ";
            }
            else {*/
            move_uploaded_file($tmp_name,
                "$path");
            //echo "Stored in: " . "$path/$user_id/" . $_FILES["file"]["name"];
            //}
        }
    }
    else {
    }
}
function upload_voice($file_name,$file_type,$tmp_name,$path){
    $allowedExts = array("mp3");
    $temp = explode(".", $file_name);
    $extension = end($temp);
    if (($file_type == "audio/mp3")&& in_array($extension, $allowedExts)) {
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . "<br>";
        }
        else {
            /*if (file_exists("$path" . $_FILES["file"]["name"])) {
                //echo $_FILES["file"]["name"] . " already exists. ";
            }
            else {*/
            move_uploaded_file($tmp_name,"$path");
            //echo "Stored in: " . "$path/$user_id/" . $_FILES["file"]["name"];
            //	}
        }
    }
    else {
    }
}
function get_folder($path){
    $t="server/php/files";
    $date=miladitojalali(date('Y-m-d'));
    $da=explode("-",$date);
    $year=$da[0];
    if (!is_dir ( "$t/$year" )) {
        mkdir ( "$t/$year",0755 );
    }

    $oldmask = umask(0);
    chmod("$t/$year", 0777);
    umask($oldmask);

    $month=$da[1];
    if (!is_dir ( "$t/$year/$month" )) {
        mkdir ( "$t/$year/$month",0755 );
    }

    $oldmask = umask(0);
    chmod("$t/$year/$month", 0777);
    umask($oldmask);

    $day=$da[2];
    if (!is_dir ( "$t/$year/$month/$day" )) {
        mkdir ( "$t/$year/$month/$day",0755 );
    }

    $oldmask = umask(0);
    chmod("$t/$year/$month/$day", 0777);
    umask($oldmask);

    $adress="$t/$year/$month/$day";
    return $adress;
}

function ads_get_folder(){
    $t="server/php/files";
    $date=miladitojalali(date('Y-m-d'));
    $da=explode("-",$date);
    $year=$da[0];
    if (!is_dir ( "$t/$year" )) {
        mkdir ( "$t/$year",0777 );
    }

    $month=$da[1];
    if (!is_dir ( "$t/$year/$month" )) {
        mkdir ( "$t/$year/$month",0777 );
    }

    $day=$da[2];
    if (!is_dir ( "$t/$year/$month/$day" )) {
        mkdir ( "$t/$year/$month/$day",0777 );
    }
    $adress="$year/$month/$day";
    return $adress;
}



function get_uploder_folder($addrss){
    $t="server/php/files";
    if (!is_dir ( "$t/$addrss" )) {
        mkdir ( "$t/$addrss",0777 );
    }
    $t="server/php/files/$addrss";
    $date=miladitojalali(date('Y-m-d'));
    $da=explode("-",$date);
    $year=$da[0];
    if (!is_dir ( "$t/$year" )) {
        mkdir ( "$t/$year",0777 );

        $oldmask = umask(0);
        chmod("$t/$year", 0777);
        umask($oldmask);
    }

    $month=$da[1];
    if (!is_dir ( "$t/$year/$month" )) {
        mkdir ( "$t/$year/$month",0777 );
        $oldmask = umask(0);
        chmod("$t/$year/$month", 0777);
        umask($oldmask);
    }

    $day=$da[2];
    if (!is_dir ( "$t/$year/$month/$day" )) {
        mkdir ( "$t/$year/$month/$day",0777 );
        $oldmask = umask(0);
        chmod("$t/$year/$month/$day", 0777);
        umask($oldmask);
    }

    $adress="$year/$month/$day";
    return $adress;
}

####################################################
function miladitojalali($date)
{

    $q=explode("-",$date);
    $qq= gregorian_to_jalali($q[0],$q[1],$q[2]);
    $date= $qq[0]."-".$qq[1]."-".$qq[2];
    return $date;
}
function miladitojalaliuser($date,$la='fa',$type=0)
{
    if($la!='fa')
        return $date;
    if($type==1)
        return	jdate('d  F  Y',$date);
    else{
        $q=explode("-",$date);
        $qq= gregorian_to_jalali($q[0],$q[1],$q[2]);
        $date= $qq[0]."/".$qq[1]."/".$qq[2];
        return $date;
    }
}

function ta_latin_num($string) {
    //arrays of persian and latin numbers
    $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
    $latin_num = range(0, 9);
    $string = str_replace($persian_num, $latin_num, $string);
    return $string;
}
function ta_persian_num($string) {
    //arrays of persian and latin numbers
    $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
    $latin_num = range(0, 9);

    $string = str_replace($latin_num, $persian_num, $string);

    return $string;
}


function miladitojalalidefult($date,$la='fa'){
    if($la!='fa')
        return $date;
    $temp=explode(" ",$date);
    $q=explode("-",$temp[0]);
    $qq= gregorian_to_jalali($q[0],$q[1],$q[2]);
    $date= $temp[1].' '.$qq[0]."/".$qq[1]."/".$qq[2].' ';
    return $date;
}

function create_main_menu_index_virdual($parentID,$site_id,$defult_lang,$coms_conect){
    $sql = "SELECT * FROM new_menu WHERE parent_id='$parentID' and site_id='$site_id' and la='$defult_lang' and float_menu=1 and status=1 ORDER BY rang";
    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo "<ul>";
        while($row = $result->fetch_assoc()) {
            $target=get_target ($row['target']);
            $href=$row['link'];
            echo "<li >";
            echo "<a   $target  href='$href'>{$row['name']}</a>";
            create_main_menu_index_virdual($row['id'],$site_id,$defult_lang,$coms_conect);
            echo "</li>";
        }
        echo "</ul>";
    }
}

function create_new_cat_virdual($parentID,$site_id,$defult_lang,$coms_conect,$type=1,$module='news'){
    $sql = "SELECT name,la,id FROM new_modules_cat WHERE parent_id=$parentID and type=$type and la='$defult_lang' ORDER BY rang";
    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo "<ul>";
        while($row = $result->fetch_assoc()) {
            $href="/$module/{$row['la']}/category/{$row['id']}";
            echo "<li >";
            echo "<a    href='$href'>{$row['name']}</a>";
            create_new_cat_virdual($row['id'],$site_id,$defult_lang,$coms_conect,$type=1,$module='news');
            echo "</li>";
        }
        echo "</ul>";
    }
}

function miladitojalalitime($date,$la='fa')
{
    if($la!='fa')
        return $date;
    $datee=explode(' ',$date);
    $q=explode("-",$datee[0]);
    $qq= gregorian_to_jalali($q[0],$q[1],$q[2]);
    $date= $datee[1].' - '.$qq[0]."/".$qq[1]."/".$qq[2] ;
    return $date;
}

function jalalitomiladi($date)
{
    $q=explode("/",$date);
    $qq= jalali_to_gregorian($q[0],$q[1],$q[2]);

    $date= $qq[0]."-".$qq[1]."-".$qq[2];
    return $date;
}
function jalalitomiladileft($date)
{
    $q=explode("/",$date);
    $qq= jalali_to_gregorian($q[0],$q[1],$q[2]);
    echo $qq[2].'<br>';
    $date= $qq[2]."-".$qq[1]."-".$qq[0];
    return $date;
}
// Function to remove folders and files
function rrmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file)
            if ($file != "." && $file != "..") rrmdir("$dir/$file");
        rmdir($dir);
    }
    else if (file_exists($dir)) unlink($dir);
}

// Function to Copy folders and files
function rcopy($src, $dst) {

    if (is_dir ( $src )) {
        $files = scandir ( $src );
        foreach ( $files as $file ){
            if ($file != "." && $file != ".."){
                rcopy ( "$src/$file", "$dst/$file" );
                //  unlink("$src/$file");
            }}
    } else if (file_exists ( $src ))
        copy ( $src, $dst );

}


function delete_allfile($path)
{
    //$path = "uploads/small/$folder/";
    $result = glob ("$path/*.*");
    if($result[0]!=''){
        foreach(glob($path.'/' ."*.*") as $file) {
            unlink($file);
        }
    }
}

function  sendsms($too,$text){

    $host="login.smspanel.org";
    $path="./api/sendsms.ashx";
    $site=$_SERVER['HTTP_HOST'];
    $sitefu=$_SERVER['REQUEST_URI'];
    $data_to_send="username=timapoo24&password=147258&to=$too&text=urlencode($text)";

    $fp = fsockopen($host,80);
    $outp="POST $path HTTP/1.1\n";
    $outp.="Host: $host\n";
    $outp.="Content-type: application/x-www-form-urlencoded\n";
    $outp.="Content-length: ".strlen($data_to_send)."\n";
    $outp.="Connection: close\n\n";
    $outp.=$data_to_send;
    fputs($fp,$outp);
    fclose($fp);
}


function get_menu_name($url){
    $check=explode("/",$url);
    if(count($check>=6))
        $url="$check[0]/$check[1]/$check[2]/$check[3]/$check[4]";
    return $url;
}





function get_kama($str){
    $str=strrev($str);
    $arr=str_split($str);
    $num="";
    for($i=0;$i<=count($arr);$i++){
        $k=$i;
        $k++;
        if($k%3==0&&$i!=count($arr)){
            $num .=$arr[$i].",";
        }
        else
            $num .=$arr[$i];
    }
    $num=strrev($num);
    return $num;
}

function object2Array($d) {
    if (is_object($d)) {
        $d = get_object_vars($d);
    }
    if (is_array($d)) {
        return array_map(__FUNCTION__, $d);
    }
    else {
        return $d;
    }
}

function show_msg_warninig($str){?>
    <div class="alert alert-error yepalert yepalert-danger  fade " role="alert" style="position: fixed;">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong></strong> <?=$str?>
    </div>

    <script>
        $(".alert").delay(200).addClass("in").fadeOut(400).fadeIn(400);
    </script>
<?}
function show_msg($str){
    ?>
    <div class="yepalert  yepalert-success fade alert" style="position: fixed;">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong></strong> <?=$str?>
    </div>

    <script>
        $(".alert").delay(200).addClass("in").fadeOut(400).fadeIn(400);
    </script>
<?}


function yepmail($email,$title,$msg,$headers){
    $coms_conect=$_SESSION["coms_conect"];
    $type=0;
    $query="select * from new_tem_setting where name='stmp_email_setting' and tem_name='default' and la='fa' and site='main'";
    $result = $coms_conect->query($query);
    $RS = $result->fetch_assoc();
    //$stmp_email_setting= get_tem_result('main','fa',"stmp_email_setting","default",$coms_conect);
    $user=$RS['link'];
    $password=$RS['pic'];
    $sender=$RS['title'];
    $type=$RS['text'];
    $host=$RS['pic_adress'];
    if($type==1){
        require("PHPMailer/class.phpmailer.php");

        $mail = new PHPMailer();
        $mail->IsSMTP();                                      // set mailer to use SMTP
        $mail->Host = "$host";  // specify main and backup server
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Username = $user;  // SMTP username
        $mail->Password = $password; // SMTP password
        $mail->CharSet = 'UTF-8';
        $mail->From = $sender;
        $mail->FromName = $name;
        $mail->AddAddress($email);
        $mail->WordWrap = 50;                                 // set word wrap to 50 characters
        //$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
        //$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
        $mail->IsHTML(true);                                  // set email format to HTML
        $mail->Subject = $title;
        $mail->Body    = $msg;
        if(!$mail->Send())
        {
            return 0;

        }
        return 1;
    }else{
        return mail($email,$title,$msg,$headers);
    }

}



###########################new_functions##################################
function check_parent($id, $connection){
    $row=get_result("SELECT id FROM new_menu WHERE parent_id='$id'");
    if($row>0)
        return 'folder';
    else
        return 'item';
}
function check_parent_count($id, $connection){
    $row=get_result("SELECT id FROM new_menu WHERE parent_id='$id'");
    if($row>0)
        return 1;
    else
        return 0;
}
function get_target($id){
    if($id==1){
        return 'target="_blabk"';

    }
    else
        return '';


}
function menu_showNested($float_menu,$parentID,$site_id,$la,$coms_conect) {
    $sql = "SELECT target,status,unic_id,id,name,link FROM new_menu WHERE parent_id='$parentID' and float_menu=$float_menu and site_id='$site_id' and la='$la' ORDER BY rang";
    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo "\n";
        echo "<ol class='dd-list'>\n";
        while($row = $result->fetch_assoc()) {
            $str="";$status=$row['status'];	if($status==1)$str='checked';

            echo "\n";
            $id=$row['unic_id'];
            $ide=$row['id'];
            echo "<li class='dd-item' data-id='{$row['id']}'>\n";
            echo "<div class='dd-handle'> <a target='_blank' href='{$row['link']}'>{$row['name']}</a>";
            echo '	<div class="pull-right action-buttons">
									<a class="blue" href="javascript:void(0)">
									<input '.$str.' id='.$id.' name="switch-field-1" class="ace ace-switch ace-switch-6" type="checkbox"/>
									<span class="lbl"></span>
									</a>';
            if($_SESSION["can_edit"]==1){
                echo '<a id='.$id.' class="blue edit_menu" href="#">
									<i class="ace-icon fa fa-pencil bigger-130"></i>
									</a>';
            }if($_SESSION["can_delete"]==1){
                echo '<a id='.$id.' value="'.$ide.'" class="del_menu red" data-title="Delete" data-toggle="modal" data-target="#delete" data-placement="top" rel="tooltip">
										<i class="ace-icon fa fa-trash-o bigger-130"></i>
										</a>';
            }
            echo '</div></div>';
            menu_showNested($float_menu,$row['id'],$site_id,$la,$coms_conect);

            echo "</li>\n";
        }
        echo "</ol>\n";
    }
}


function show_madules_cat($parentID,$site_id,$la,$type,$coms_conect,$modul_name='news') {
    $sql = "SELECT * FROM new_modules_cat WHERE parent_id='$parentID' and type='$type'  and la='$la' ORDER BY rang";
    //echo $sql;
    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo "\n";
        echo "<ol class='dd-list'>\n";
        while($row = $result->fetch_assoc()) {
            echo "\n";
            $id=$row['id'];
            $str="";
            $status=$row['status'];
            $name=insert_dash($row['name']);
            if($status==1)
                $str='checked';
            echo "<li class='dd-item' data-id='{$row['id']}'>\n";
            if($type==99)
                echo "<div class='dd-handle'><a class='sr-pull-right' target='_blank' href='"."/question/$la/category/$id"."'> {$row['name']}</a>";
            else if($type==100)
                echo "<div class='dd-handle'><a class='sr-pull-right' target='_blank' href='"."/faq/$la/category/$id"."'> {$row['name']}</a>";
            else
                echo "<div class='dd-handle'> <a class='sr-pull-right' target='_blank' href='"."/$modul_name/$la/category/$name/1"."'> {$row['name']}</a>";
            echo '	<div class="pull-right action-buttons">';
            if($_SESSION["can_edit"]==1){
                echo '<a class="blue" href="#">
									<input '.$str.' id='.$id.' name="switch-field-1" class="ace ace-switch ace-switch-6" type="checkbox"/>
									<span class="lbl"></span>
									</a>';

                echo '<a id='.$id.' class="blue edit_menu" href="#">
									<i class="flaticon-note32 bigger-130"></i>
									</a>';
            }

            if($type==100&&menu_has_child($id,$coms_conect)==0&&get_result("select count(cat_id) from new_faq where cat_id=$id",$coms_conect)==0&&$_SESSION["can_delete"]==1){

                echo '<a id='.$id.' class="del_menu red" data-title="Delete" data-toggle="modal" data-target="#delete" data-placement="top" rel="tooltip">
										<span class="flaticon-delete84 bigger-130"></span>
										</a>';
            }
            else if($type!=100&&menu_has_child($id,$coms_conect)==0&&!get_result("select count(cat_id) from new_modules_catogory where cat_id=$id",$coms_conect)&&$_SESSION["can_delete"]==1){
                echo '<a id='.$id.' class="del_menu red" data-title="Delete" data-toggle="modal" data-target="#delete" data-placement="top" rel="tooltip">
										<span class="flaticon-delete84 bigger-130"></span>
										</a>';
            }
            echo '</div></div>';
            show_madules_cat($row['id'],$site_id,$la,$type,$coms_conect,$modul_name='news');

            echo "</li>\n";
        }
        echo "</ol>\n";
    }
}
function array_to_string($str){
    $i=1;$temp='';
    foreach($str as $val){
        $str=',';
        if($i==1)$str='';
        $temp .=$str.$val;
        $i++;
    }
    return $temp;
}

function creat_main_menu($parentID,$site_id,$la,$coms_conect) {
    global $connection;
    $sql = "SELECT unic_id,status,id,name FROM new_main_menu WHERE parent_id='$parentID' and site_id='$site_id' and la='$la' ORDER BY rang";
    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo "\n";
        echo "<ol class='dd-list'>\n";
        while($row = $result->fetch_assoc()) {
            echo "\n";
            $id=$row['id'];
            $unic_id=$row['unic_id'];
            $str="";
            $status=$row['status'];
            $name=$_SESSION["menu_lang"][$row['name']];
            if($status==1)
                $str='checked';

            echo "<li class='dd-item dd2-item' data-id='{$row['id']}'>\n";
            echo '<div class="dd-handle dd2-handle"> 
									 <i class="normal-icon ace-icon fa fa-bars blue bigger-130"></i>
									 <i class="drag-icon ace-icon fa fa-arrows bigger-125"></i>
								  </div>';
            echo '<div class="dd2-content">';
            echo '	<div class="pull-right action-buttons">';
            echo '<a class="blue" href="javascript:void(0)">
									<input '.$str.' id='.$unic_id.' name="switch-field-1" class="ace ace-switch ace-switch-6" type="checkbox"/>
									<span class="lbl"></span>
									</a>
									<a id='.$unic_id.' title="ویرایش" class="blue edit_menu" href="#">
									<i class="ace-icon fa fa-pencil bigger-130"></i>
									</a>
									<a id='.$id.' title="حذف" class="del_menu red" data-title="Delete" data-toggle="modal" data-target="#delete" data-placement="top" rel="tooltip">
									<i class="ace-icon fa fa-trash-o bigger-130"></i>
									</a>
									</div>'.$name.'</div>';
            creat_main_menu($row['id'],$site_id,$la,$coms_conect);

            echo "</li>\n";
        }
        echo "</ol>\n";
    }
}

function create_side_menu($yep,$parentID,$site_id,$la,$manager_group,$coms_conect){
    $sql = "SELECT file_path,icon,id,name,status FROM new_main_menu a ,new_menu_permission b 
	WHERE a.unic_id=b.menu_id and b.permission=1 and b.group_id=$manager_group and a.la='$la' and a.parent_id='$parentID' and a.status=1 group by id  ORDER BY rang";

    $result = $coms_conect->query($sql);

    $str22='';
    if($result->num_rows>0){
        echo "<ul class='submenu'  >\n";
        while($row = $result->fetch_assoc()){
            $id=$row["id"];
            $file_path=$row['file_path'];
            $icon=$row["icon"];
            $sql1 = "SELECT count(id) as cont FROM new_main_menu WHERE parent_id='$id'";
            $result1 = $coms_conect->query($sql1);
            $row1 = $result1->fetch_assoc();
            $str="";
            $str1="";
            $numRows1=$row1["cont"];
            if($numRows1>0){
                $str="class='dropdown-toggle'";
                $str1="<b class='arrow fa fa-angle-down'></b>";
            }
            $numRows1=0;
            $name=$row["name"];
            $name=$_SESSION["menu_lang"][$name];
            $str222='';
            if (in_array($row["id"], $yep))
                $str222= "active";

            echo '	<li class="'.$str222.'" >
				 <a href="newcoms.php?yep='.$file_path.'" '.$str.'>
				 <i class="menu-icon fa '.$icon.'"></i>
				 <span class="menu-text">'.$name.' </span>';
            $commane_count=0;
            if($row["name"]==38||$row["name"]==41)
                $commane_count=get_result("SELECT count(a.id) as cnt from new_madules_comment b ,new_news a where a.id>0
							and user_id in({$_SESSION["manager_user_permisson_string"]})  and b.type=1 and a.id=b.madul_id and b.status=0",$coms_conect);


            if($row["name"]==53||$row["name"]==57)
                $commane_count=get_result("SELECT count(a.id) as cnt from new_madules_comment b ,new_video a where a.id>0
							and user_id in({$_SESSION["manager_user_permisson_string"]})  and b.type=8 and a.id=b.madul_id and b.status=0",$coms_conect);


            if($row["name"]==51||$row["name"]==48)
                $commane_count=get_result("SELECT count(a.id) as cnt from new_madules_comment b ,new_article a where a.id>0
							and user_id in({$_SESSION["manager_user_permisson_string"]})  and b.type=7 and a.id=b.madul_id and b.status=0",$coms_conect);

            if($row["name"]==92||$row["name"]==95)
                $commane_count=get_result("SELECT count(a.id) as cnt from new_madules_comment b ,new_blog a where a.id>0
							and user_id in({$_SESSION["manager_user_permisson_string"]})  and b.type=10 and a.id=b.madul_id and b.status=0",$coms_conect);


            if($row["name"]==59||$row["name"]==62)
                $commane_count=get_result("SELECT count(a.id) as cnt from new_madules_comment b ,new_gallery a where a.id>0
							and user_id in({$_SESSION["manager_user_permisson_string"]})  and b.type=9 and a.id=b.madul_id and b.status=0",$coms_conect);

            if($row["name"]==172||$row["name"]==174) {
                $commane_count=get_result("select count(id) from new_manager_pm where resiver='{$_SESSION["manager_user_name"]}' and status=0",$coms_conect);

            }
            if($row["name"]==407||$row["name"]==175) {
                $commane_count=get_result("select count(id) from new_contactus_pm where user_id={$_SESSION["manager_id"]} and status=0",$coms_conect);

            }

            if($commane_count>0)
                echo "<span class='badge badge-grey'>$commane_count</span>";
            echo $str1.'</a>';
            create_side_menu($yep,$id,$site_id,$la,$manager_group,$coms_conect) ;
            echo ' </li>';
        }
        echo '</ul>';

    }
}

function create_permission_group($id,$site_id,$la){
    $sql = "SELECT file_path,id,name FROM new_main_menu WHERE parent_id='$id'  ORDER BY rang";
    $result = $coms_conect->query($sql);
    if($result->num_rows>0){?>
        <div class="panel-group" id="accordion-cat-<?=$id?>">
            <div class="panel panel-default panel-faq">
                <div class="panel-heading">
                    <a data-toggle="collapse" data-parent="#accordion-cat-<?=$id?>" href="#faq-cat-1-sub-<?=$id?>">
                        <h4 class="panel-title">
                            <?=$name?>
                            <span class="pull-right"><i class="glyphicon glyphicon-plus"></i></span>
                        </h4>
                    </a>
                </div>
                <div id="faq-cat-1-sub-<?=$id?>" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="row-fluid">
                            <div class="row-fluid">
                                <? while($row1 = $result->fetch_assoc()){
                                    $name1=$row1['name'];
                                    $file_path1=$row1['file_path'];
                                    $id1=$row1['id'];?>
                                    <div class="form-group">
                                        <div class="row">
                                            <label class="col-sm-4 control-label"><?=$name1?></label>
                                            <div class="col-sm-8">
                                                <div class="col-sm-2">
											<span class="button-checkbox">
											<button   type="button" class="btn" data-color="info">مشاهده</button>
											<input name="<?=$id1?>" id="<?=$id1?>"  value="1" type="checkbox" class="hidden"  />
											</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?create_permission_group($id1,$site_id,$la);
                                }?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?}
}






function create_lang_title($lang,$coms_conect,$manager_la,$type=0){
    $sql = "SELECT name,title FROM new_language";
    $result = $coms_conect->query($sql);
    if($type==1){
        $temp_str='multiple' ;
        $multiple_str='[]';
    } else{
        $temp_str='';$multiple_str='';
    }

    echo "<select $temp_str name='manage_lang$multiple_str' id='manage_lang' class='form-control' rows='2' >";
    while($row = $result->fetch_assoc()) {
        $str="";
        if($lang==$row['title'])
            $str="selected";
        if(in_array($row['title'],$manager_la))
            echo "<option $str value='{$row['title']}'>{$row['name']}</option> ";
    }
    echo '</select>';
}



function sign_out(){
    header('Location: new_manager_signout.php');
}

function create_sub_site($site_id,$coms_conect,$manager_site){
    $sql = "SELECT id,name FROM new_subsite where status=1";
    $result = $coms_conect->query($sql);
    echo "<select name='manage_site' id='manage_site' class='form-control' rows='2'  >";
    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $id=$row['id'];
        $str="";
        if($site_id==$id)
            $str="selected";
        //echo $id.'<br>';
        if(in_array($id,$manager_site))
            echo "<option $str value='$id'>$name</option> ";
    }
    echo '</select>';
}

function create_sub_site_title($site_id,$coms_conect,$manager_site){
    $sql = "SELECT title,name FROM new_subsite where status=1";
    $result = $coms_conect->query($sql);
    echo "<select name='manage_site' id='manage_site' class='form-control' rows='2'  >";
    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        //$title=$row['title'];
        $str="";
        if($site_id==$name)
            $str="selected";
        if(in_array($name,$manager_site))
            echo "<option $str value='$name'>$name</option> ";
    }
    echo '</select>';
}

function create_sub_site_menu($site_id,$coms_conect,$manager_site,$class_name){
    $sql = "SELECT id,name FROM new_subsite where status=1";
    $result = $coms_conect->query($sql);
    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $id=$row['name'];
        $str="";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$manager_site))
            echo "<li><a class='$class_name' id ='$id' href='#'>$name</a></li> ";
    }

}
function create_sub_site_filter($site_id,$coms_conect,$manager_site){
    $sql = "SELECT name FROM new_subsite where status=1";
    $result = $coms_conect->query($sql);
    echo "<select name='manage_site_filter' id='manage_site_filter' class='form-control' rows='2' >";
    echo "<option value=-1>همه سایتها</option>";
    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $id=$row['name'];
        $str="";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$manager_site))
            echo "<option $str value='$id'>$name</option> ";
    }
    echo '</select>';
}

function create_sub_site_filter_none($site_id,$coms_conect,$manager_site){
    $sql = "SELECT name FROM new_subsite where status=1";
    $result = $coms_conect->query($sql);
    echo "<select name='manage_site_filter' id='manage_site_filter' class='form-control' rows='2' >";

    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $id=$row['name'];
        $str="";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$manager_site))
            echo "<option $str value='$id'>$name</option> ";
    }
    echo '</select>';
}

function create_lang_filter($site_id,$coms_conect,$manager_lang){
    $sql = "SELECT title,name FROM new_language where status=1";
    $result = $coms_conect->query($sql);
    echo "<select name='manage_lang_filter' id='manage_lang_filter' class='form-control' rows='2'  >";
    echo "<option value=-1>همه زبانها</option>";
    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $id=$row['title'];
        $str="";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$manager_lang))
            echo "<option $str value='$id'>$name</option> ";
    }
    echo '</select>';
}



function create_lang_filter_none($site_id,$coms_conect,$manager_lang){
    $sql = "SELECT title,name FROM new_language where status=1";
    $result = $coms_conect->query($sql);
    echo "<select name='manage_lang_filter' id='manage_lang_filter' class='form-control' rows='2'  >";

    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $id=$row['title'];
        $str="";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$manager_lang))
            echo "<option $str value='$id'>$name</option> ";
    }
    echo '</select>';
}

function create_manager_filter($site_id,$coms_conect,$managers){
    $sql = "SELECT id,user_name,name FROM new_managers";
    $result = $coms_conect->query($sql);
    echo "<select name='manager_filter' id='manager_filter' class='form-control' rows='2' >";
    echo "<option value=-1>همه مدیران</option>";
    while($row = $result->fetch_assoc()) {
        $name=$row['user_name'];
        $id=$row['id'];
        $str="";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$managers))
            echo "<option $str value='$id'>$name ({$row['name']})</option> ";
    }
    echo '</select>';
}

function create_manager_filter_none($site_id,$coms_conect,$managers){
    $sql = "SELECT id,user_name,name FROM new_managers";
    $result = $coms_conect->query($sql);
    echo "<select name='manager_filter' id='manager_filter' class='form-control' rows='2' >";
    while($row = $result->fetch_assoc()) {
        $name=$row['user_name'];
        $id=$row['id'];
        $str="";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$managers))
            echo "<option $str value='$id'>$name ({$row['name']})</option> ";
    }
    echo '</select>';
}

function create_member_filter($site_id,$coms_conect,$managers){
    $sql = "SELECT id,user_name FROM new_members";
    $result = $coms_conect->query($sql);
    echo "<select name='manager_filter' id='manager_filter' class='form-control' rows='2'  >";
    while($row = $result->fetch_assoc()) {
        $name=$row['user_name'];
        $id=$row['id'];
        $str="";
        echo "<option value='0'>همه</option> ";
        if($site_id==$id)
            $str="selected";
        if(in_array($id,$managers))
            echo "<option $str value='$id'>$name</option> ";
    }
    echo '</select>';
}
function get_username($manager_filter,$coms_conect){
    $sql = "SELECT user_name FROM new_managers where id=$manager_filter";
    $result = $coms_conect->query($sql);
    $row = $result->fetch_assoc();
    return $row['user_name'];

}
function insert_dash ($str){
    return 	str_replace(" ","-",$str);
}

function delete_dash($str){
    return 	str_replace("-"," ",$str);
}





function resolve_status($val){
    if($val==0)
        return 'غیرفعال';
    if($val==1)
        return 'فعال';
}

function creat_sub_wive_permission($id,$site_id,$la,$edit_id,$manager_group,$coms_conect){
    if($edit_id>0)
        $sql = "SELECT icon,name,id,permission FROM new_main_menu a,new_menu_permission b WHERE   b.view=1  and la='fa'  and a.parent_id='$id' and  b.group_id=$edit_id and a.unic_id=b.menu_id  ORDER BY rang";
    else{
        $sql = "SELECT icon,name,id,permission FROM new_main_menu a,new_menu_permission b WHERE a.parent_id='$id'  and la='fa'  and b.permission=1 and  b.group_id=$manager_group and a.unic_id=b.menu_id    ORDER BY rang";
    }

    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo ",'children': [";
        while($row = $result->fetch_assoc()) {
            $str="";
            $name=$row['name'];
            $name=$_SESSION["menu_lang"][$name];
            $icon=$row['icon'];
            $permission=$row['permission'];
            $id=$row['id'];
            if($permission==1&&$edit_id>"")
                $str =",'state': {'selected': 'true'}";

            echo "{'text': '$name' ,'id':'$id' $str";
            creat_sub_wive_permission($id,$site_id,$la,$edit_id,$manager_group,$coms_conect);
            echo "},";
        }
        echo "],";

    }
}

function creat_madual_cat_permission($id,$edit_id,$arr,$manager_group,$type,$coms_conect,$la){
    $sql = "SELECT id,name FROM new_modules_cat a ,new_cat_permission b WHERE
			parent_id='$id' and type='$type' and permission=1 and a.id=b.menu_id and a.status=1 and la='$la' and b.group_id=$manager_group group by    a.id ORDER BY type ";
    //echo $sql;
    $result = $coms_conect->query($sql);
    echo ",'children': [";
    while($row = $result->fetch_assoc()) {
        $str="";
        if($edit_id>""&&in_array($row['id'],$arr))
            $str =",'state': {'selected': 'true'}";
        echo "{'text': '{$row['name']}' ,'id':'{$row['id']}' $str ,'icon': 'yep {$row['icon']}'";
        creat_madual_cat_permission($row['id'],$edit_id,$arr,$manager_group,$type,$coms_conect,$la);
        echo "},";
    }
    echo "],";
}

function creat_fistr_madual_cat_permission($id,$edit_id,$arr,$manager_group,$type,$coms_conect,$la){
    $j=1;
    $sql = "SELECT id,name FROM new_modules_cat a ,new_cat_permission b WHERE
	parent_id='$id' and type='$type' and a.id=b.menu_id and permission=1 and a.status=1   and a.la='$la' and b.group_id=$manager_group  group by a.id ORDER BY   rang";
    // echo $sql;
    $result = $coms_conect->query($sql);
    while($row = $result->fetch_assoc()) {
        $str="";
        if(($edit_id>""&&in_array($row['id'],$arr))||($j==1&&$edit_id==""))
            $str =",'state': {'selected': 'true'}";
        echo "{'text': '{$row['name']}' ,'id':'{$row['id']}' $str";
        creat_madual_cat_permission($row['id'],$edit_id,$arr,$manager_group,$type,$coms_conect,$la);
        echo "},";
        $j++;}
}


function creat_faq_cat($id,$type,$coms_conect,$la,$name='faq'){
    $sql = "SELECT id,name FROM new_modules_cat a  WHERE
	parent_id='$id' and type='$type'   and a.status=1 and la='$la'  group by    a.id ORDER BY type ";
    //echo $sql;
    $result = $coms_conect->query($sql);
    echo ",'children': [";
    while($row = $result->fetch_assoc()) {

        echo "{'text': '{$row['name']}' ,'id':'{$row['id']}'  ,'icon': 'yep {$row['icon']}'   ,'a_attr':{'href':'/$name/$la/category/{$row['id']}'}";
        creat_faq_cat($row['id'],$type,$coms_conect,$la,$name);
        echo "},";
    }
    echo "],";
}


function creat_first_faq_cat($id,$type,$coms_conect,$la,$name='faq'){
    $j=1;

    $sql = "SELECT id,name FROM new_modules_cat a  WHERE
	parent_id='$id' and type='$type'  and a.status=1 and la='$la' group by a.id ORDER BY rang";
    // echo $sql;
    $result = $coms_conect->query($sql);
    while($row = $result->fetch_assoc()) {
        echo "{'text': '{$row['name']}' ,'id':'{$row['id']}' ,'a_attr':{'href':'/$name/$la/category/{$row['id']}'}";
        creat_faq_cat($row['id'],$type,$coms_conect,$la,$name);
        echo "},";
        $j++;}

}







function creat_fistr_madual_cat_permission0($id,$edit_id,$manager_group,$type,$coms_conect){
    $sql = "SELECT id,name,permission FROM new_modules_cat a ,new_cat_permission b  WHERE	
			 parent_id=0 and a.type='$type' and b.group_id=$edit_id and a.status=1 and a.id=b.menu_id  group by b.menu_id ORDER BY a.type";

    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo ",'children': [";
        while($row = $result->fetch_assoc()) {
            $str="";
            if(($row['permission']==1&&$edit_id>""&&$manager_group!=-1))
                $str =",'state': {'selected': 'true'}";
            echo "{'text': '{$row['name']}' ,'id':'{$row['id']}' $str ";
            creat_madual_cat_permission0($row['id'],$edit_id,$arr,$manager_group,$type,$coms_conect);
            echo "},";$j++;
        }
        echo "],";

    }
}

function creat_madual_cat_permission0($id,$edit_id,$arr,$manager_group,$type,$coms_conect){
    if($edit_id>""&&$la=="")
        $sql = "SELECT * FROM new_modules_cat a ,new_cat_permission b  WHERE	
			  parent_id='$id' and a.type='$type' and b.group_id=$edit_id and a.id=b.menu_id   group by b.menu_id ORDER BY a.type";
    $result = $coms_conect->query($sql);
    //echo $sql;
    if ($result->num_rows > 0) {
        echo ",'children': [";
        while($row = $result->fetch_assoc()) {
            $str="";
            $name=$row['name'];
            $icon=$row['icon'];

            $permission=$row['permission'];
            $id=$row['id'];
            if(($permission==1&&$edit_id>""&&$manager_group!=-1))
                $str =",'state': {'selected': 'true'}";
            if($la>""){
                if(in_array($id,$la))
                    $str =",'state': {'selected': 'true'}";
            }
            echo "{'text': '$name' ,'id':'$id' $str ,'icon': 'yep $icon'";
            creat_madual_cat_permission0($id,$edit_id,$arr,$manager_group,$type,$coms_conect);
            echo "},";
        }
        echo "],";

    }
}


function check_url($la,$url,$manager_group,$coms_conect){
    $sql = "SELECT count(id) as count FROM new_main_menu a,new_menu_permission  b where
	a.unic_id=b.menu_id and a.file_path='$url' and a.la='$la' and b.permission=1 and group_id='$manager_group'";
    //echo $sql.'<br>';exit;
    $result = $coms_conect->query($sql);
    $row = $result->fetch_assoc();
    $count=$row['count'];
    return $count;
}

function check_owner_feild($table,$id,$coms_conect){
    $query="select count(id) as count from $table where user_id=$id";
    $result = $coms_conect->query($query);
    $RS1 = $result->fetch_assoc();
    return $RS1["count"];
}

function create_password($user,$pass){
    return md5(md5($user.'ya_mahdi'.$pass.'adrekni'.'YEPCO'));
}
function create_memebre_password($user,$pass){
    return md5(md5($user.'ya_mahdi'.$pass.'adrekni'.'YEPCO_company'));
}

function create_manager_folder($user_name,$id,$coms_conect,$temp_folder){

    global $temp_folder;
    if($id!=0){
        $query="select user_name,parent_id from new_managers where id='$id'";
        $result12 = $coms_conect->query($query);
        $RS1 = $result12->fetch_assoc();
        $temp_folder .=$RS1["user_name"].'^';
        if($RS1["parent_id"]!='0'&&$RS1["user_name"]!="")
            return	create_manager_folder($RS1["user_name"],$RS1["parent_id"],$coms_conect,$temp_folder);
        else
            return $temp_folder;
        //exit;
    }else
        return 'comsroot';
}

function check_parrent_permission($id,$coms_conect,$qaz,$j){
    global $qaz;
    $query="select id from new_managers where parent_id=$id";
    $result12 = $coms_conect->query($query);
    if ($result12->num_rows > 0) {
        while($RS1 = $result12->fetch_assoc()) {
            $qaz .=$RS1["id"].'^';
            $j++;
            check_parrent_permission($RS1["id"],$coms_conect,$qaz,$j);
        }

    }
    return $qaz;
}

function get_group_parrent($id,$coms_conect,$qazz,$j){
    global $qazz;
    $query="select id from new_groups where parrent_id=$id";
    //echo $query.'<br>';
    $result12 = $coms_conect->query($query);
    if ($result12->num_rows > 0) {
        while($RS1 = $result12->fetch_assoc()) {
            $qazz .=$RS1["id"].'^';
            $j++;
            get_group_parrent($RS1["id"],$coms_conect,$qazz,$j);
        }

    }
    return $qazz;
}

function insert_templdate($site,$pic_adress,$la,$text,$title,$link,$pic,$name,$tem_name,$coms_conect,$type=0){
    $query11="SELECT id from new_tem_setting where name='$name' and la='$la' and site='$site' and tem_name='$tem_name'";
//    if($type==1)
//        echo $query11.'<br>';
    $result11 = $coms_conect->query($query11);
    $row11 = mysqli_num_rows($result11);
    if ($row11> 0) {
        $query="update new_tem_setting set pic_adress='$pic_adress',title='$title',link='$link',pic='$pic',text='".$text."' where name='$name' and tem_name='$tem_name' and la='$la'  and site='$site'";
        //if($name=='google_code_analiz')
        if($type==1)
            echo $query;
        $coms_conect->query($query);
    }else{
        $query="insert into new_tem_setting(site,pic_adress,la,title,link,pic,text,name,tem_name)values('$site','$pic_adress','$la','$title','$link','$pic','$text','$name','$tem_name')";
        if($type==1)
            echo $query;
        $coms_conect->query($query);
    }
}

function insert_ads_setting($site,$name,$la,$value,$type,$coms_conect){
    $query11="SELECT id from new_ads_setting where name='$name' and la='$la' and site='$site' and type=$type";
    //echo $query11.'<br>';
    $result11 = $coms_conect->query($query11);
    $row11 = mysqli_num_rows($result11);
    if ($row11> 0) {
        $query="update new_ads_setting set name='$name',value='$value',type='$type' where name='$name' and type='$type' and la='$la'  and site='$site'";
        $coms_conect->query($query);
    }else{
        $query="insert into new_ads_setting(site,value,la,type,name,date,user_id)values('$site','$value','$la','$type','$name',now(),{$_SESSION["manager_id"]})";
        $coms_conect->query($query);
    }
}


function insert_ads_templdate($site,$la,$value,$name,$coms_conect){
    $query11="SELECT id  from new_modual_tem_setting where name='$name' and la='$la' and site='$site'";
    $result = $coms_conect->query($query11);
    if(($result->num_rows > 0)){
        $query="update new_modual_tem_setting set value='$value' where name='$name' and la='$la'  and site='$site'";
        $coms_conect->query($query);
    }else{
        $query="insert into new_modual_tem_setting(name,value,la,site)values('$name','$value','$la','$site')";
        $coms_conect->query($query);
    }

}


function insert_news_templdate($site,$pic_adress,$la,$text,$title,$link,$pic,$name,$tem_name,$coms_conect){
    $query="insert into new_tem_setting(site,pic_adress,la,title,link,pic,text,name,tem_name)values('$site','$pic_adress','$la','$title','$link','$pic','$text','$name','$tem_name')";
    $coms_conect->query($query);
}

function upload_tem_pic($name,$tem_name){
    $file_name= $_FILES["$name"]["name"];
    $file_type= $_FILES["$name"]["type"];
    $file_tmp= $_FILES["$name"]["tmp_name"];
    $file_path="new_template/$tem_name/images/$name.gif";
    upload_file($file_name,$file_type,$file_tmp,$file_path);
}

function get_tem_result($site,$la,$name,$tem_name,$coms_conect){
    $query="select * from new_tem_setting where name='$name' and tem_name='$tem_name' and la='$la' and site='$site'";
    $result = $coms_conect->query($query);
    ///  echo $query;
    $RS = $result->fetch_assoc();
    $arr = array("text"=>$RS["text"], "title"=>$RS["title"], "pic"=>$RS["pic"], "link"=>$RS["link"], "pic_adress"=>$RS["pic_adress"]);
    return $arr;
}
function get_ads_setting_result($site,$la,$name,$type,$coms_conect){
    $query="select value from new_ads_setting where name='$name' and type='$type' and la='$la' and site='$site'";
    $result = $coms_conect->query($query);
    $RS = $result->fetch_assoc();

    return $RS['value'];
}
function get_modual_setting_result($site,$la,$name,$coms_conect){
    $query="select value from new_modual_tem_setting where name='$name' and la='$la' and site='$site'";
    $result = $coms_conect->query($query);
    $RS = $result->fetch_assoc();
    return $RS["value"];
}
function create_menu_index($parentID,$site_id,$la,$coms_conect,$a_class,$li_class,$ul_class){
    $sql = "SELECT * FROM new_menu WHERE parent_id='$parentID' and site_id='$site_id' and la='$la' and float_menu=0 and status=1 ORDER BY rang";
    $result = $coms_conect->query($sql);
    //echo $sql.'<br>';
    if ($result->num_rows > 0) {
        echo "<ul class='$ul_class'>";
        while($row = $result->fetch_assoc()) {
            $target=get_target ($row['target']);
            echo "<li class='$li_class'>
						<a   $target class='$a_class' href='{$row['link']}'>{$row['name']}</a>";
            create_menu_index($row['id'],$site_id,$la,$coms_conect,'','','');
            echo "</li>";
        }
        echo "</ul>";
    }
}
function create_menu_index_float($parentID,$site_id,$la,$coms_conect,$a_class,$li_class,$ul_class){
    $sql = "SELECT * FROM new_menu WHERE parent_id='$parentID' and site_id='$site_id' and la='$la' and float_menu=1 and status=1 ORDER BY rang";
    $result = $coms_conect->query($sql);
    //echo $sql.'<br>';
    if ($result->num_rows > 0) {
        echo "<ul class='$ul_class'>";
        while($row = $result->fetch_assoc()) {
            $target=get_target ($row['target']);
            echo "<li class='$li_class'>
						<a   $target class='$a_class' href='{$row['link']}'>{$row['name']}</a>";
            create_menu_index_float($row['id'],$site_id,$la,$coms_conect,'','','');
            echo "</li>";
        }
        echo "</ul>";
    }
}



function resolve_langueg($id,$coms_conect){
    $sql = "SELECT title FROM new_language where id=$id";
    $result = $coms_conect->query($sql);
    $row = $result->fetch_assoc();
    return $row['title'];

}


function resolve_id_langueg($id,$coms_conect){
    $sql = "SELECT id FROM new_language where title='$id'";
    //echo $sql;exit;
    $result = $coms_conect->query($sql);
    $row = $result->fetch_assoc();
    return $row['id'];
}

function get_direction_lang($id,$coms_conect){
    $sql = "SELECT align FROM new_language where id='$id'";
    $result = $coms_conect->query($sql);
    $row = $result->fetch_assoc();
    $lang_dir=($row['align']==1) ? 'ltr' : 'rtl';
    return $lang_dir;
}

function get_direction_lang_title($id,$coms_conect){
    $sql = "SELECT align FROM new_language where title='$id'";
    $result = $coms_conect->query($sql);
    $row = $result->fetch_assoc();
    $lang_dir=($row['align']==1) ? 'ltr' : 'rtl';
    return $lang_dir;
}

function resolve_id_site($id,$coms_conect){
    $sql = "SELECT id FROM new_subsite where name='$id'";
    $result = $coms_conect->query($sql);
    $row = $result->fetch_assoc();
    return $row['id'];
}



function get_devivery($coms_conect,$MessageIDs)	{
    $query="select * from new_sms_webservice";
    $result = $coms_conect->query($query);
    $row = $result->fetch_assoc();
    $url=$row['url'];
    $sms_password=$row['sms_password'];
    $sms_user=$row['sms_user'];
    $client = new SoapClient("$url");
    $params = array(
        'username' 	=> $sms_user,
        'password' 	=> $sms_password,
        'MessageIDs' => $MessageIDs,
    );
    $results = $client->GetMessageStatus($params);
    $q=object2Array($results);
    return $q['GetMessageStatusResult']['int'];
}




function sms_alert($val){
    if($val==1)return 'نام کاربری و یا کلمه عبور پنل اس ام اس اشتباه است';
    if($val==2)return 'آرایه ها خالی می باشد';
    if($val==3)return 'طول آرایه بیش از 100 می باشد';
    if($val==4)return 'طول آرایه ها با هم تطابق ندارد';
    if($val==5)return 'امکان گرفتن پیام جدید وجود ندارد';
    if($val==6)return 'حساب کاربری اس ام اس غیر فعال می باشد';
    if($val==7)return 'امکان دسترسی به خط مورد نظر وجود ندارد';
    if($val==8)return 'شماره گیرنده نامعتبر است';
    if($val==9)return 'حساب اعتبار مالی ندارد';
    if($val==10)return 'خطایی در سیستم رخ داده است دوباره سعی کنید';
    if($val==20)return 'شماره موبایل گیرنده فیلتر شده است';


}


function insert_to_data_base($arr,$tbl_name,$coms_conect,$mode=0){
    $sql="insert into $tbl_name(";
    $i=0;$str="";
    foreach($arr as $feild_name=>$value){
        if($i!=0)
            $str=",";
        $sql .="$str$feild_name";$i++;
    }
    $sql .=")values(";$i=0;$str1="";
    foreach($arr as $value){
        if($i!=0)
            $str1=",";
        $sql .="$str1"."'".$value."'";$i++;
    }
    $sql .=")";

    if($mode==1)
        echo $sql.'<br>';

    $coms_conect->query($sql);
    return mysqli_insert_id($coms_conect);

}

function get_domain_pic_setting($domain,$tem_name,$coms_conect){
    $aquery1="select  id,pic_adress from new_tem_setting
	 where    site='{$_SESSION['site']}' and la='{$_SESSION['la']}'   and tem_name='$tem_name'";

    $aresult1 = $coms_conect->query($aquery1);
    while($arowe1 = $aresult1->fetch_assoc()){

        $temp=str_replace('http://localhost/',"http://$domain/",$arowe1["pic_adress"]);
        $id=$arowe1["id"];
        $arr_slide=array("pic_adress"=>$temp);
        $condition="id=$id";
        update_data_base($arr_slide,'new_tem_setting',$condition,$coms_conect);
    }
}


function update_data_base($arr,$tbl_name,$condition,$coms_conect,$type=0){
    $sql="update $tbl_name set ";
    $i=0;$str="";
    foreach($arr as $feild_name=>$value){
        if($i!=0)
            $str=",";
        $sql .="$str$feild_name="."'".$value."'";$i++;
    }
    $sql .=" where $condition";
    if($type==1)
        echo $sql.'<br>';
    $coms_conect->query($sql);
}
function send_sms($url,$password,$user,$senderNumbers,$recipientNumbers,$messageBodies,$sendDate,$coms_conect,$manager_id){
    $client = new SoapClient("$url");
    $params = array(
        'username' 	=> $user,
        'password' 	=> $password,
        'senderNumbers' => $senderNumbers,
        'recipientNumbers'=> $recipientNumbers,
        'messageBodies' =>$messageBodies,
        'sendDate'=> $sendDate,
    );
    $results = $client->SendSMS($params);

    $q=object2Array($results);
    //echo $q['SendSMSResult']['long'];exit;
    //  print_r($params);exit;
    if(($q['SendSMSResult']['long'])<=1000)
        return $q['SendSMSResult']['long'];
    else if($q['SendSMSResult']['long']>10000){
        $arr_attach=array("sms_number"=>$senderNumbers[0],"mobile"=>$recipientNumbers[0],"date"=>time(),"text"=>$messageBodies[0],"unic_id"=>$q['SendSMSResult']['long'],"manager_id"=>$manager_id);
        insert_to_data_base($arr_attach,'new_sms_archive',$coms_conect);
        return 1;
    }
}
function get_result($sql,$coms_conect,$type=0){
    if($type==1)
        echo $sql.'<br>';
    $result=mysqli_query($coms_conect,$sql);
    $row =mysqli_fetch_array($result,MYSQLI_NUM);
    return $row[0];

}




function get_all_result($sql,$coms_conect){
    // echo $sql.'<br>';
    $result = $coms_conect->query($sql);
    while($row = $result->fetch_assoc()){
        $temp[]=$row[0];
    }
    return $temp;
}

function get_result_number($sql,$coms_conect){
    $result=mysqli_query($coms_conect,$sql);
    return mysqli_num_rows($result);
}


function get_modual_table($id){
    switch ($id){
        case  1:
            $table='new_news';
            break;
        case   5:
            $table='new_static_page';
            break;
        case  6:
            $table='new_download';
            break;
        case   7:
            $table='new_article';
            break;
        case   8:
            $table='new_video';
            break;
        case  9:
            $table='new_gallery';
            break;
        case   10:
            $table='new_blog';
            break;
    }
    return $table;
}

function get_module_viwe_count($coms_conect,$start_date,$end_date){
    return get_result("select sum(count_view) from new_module_view where  date>='$start_date' and date<='$end_date'",$coms_conect);
}



function create_viwe_tree($type,$coms_conect,$mount,$year){
    $st='';
    for($i=1;$i<=31;$i++){
        $date=date('Y-m-d',jmktime(5, 1, 1, $mount, $i,$year));
        $sql="select  count_view from new_module_view where type=$type and date='$date'";
        $result1 = $coms_conect->query($sql);
        $RS11 = $result1->fetch_assoc();

        if($i==1)$st='';else $st=',';
        if($RS11['count_view']=="")
            $temp= 0;
        else
            $temp=$RS11['count_view'] ;

        $str .="$st  $temp";
    }
    echo $str;
}

function create_month_day(){
    $st='';
    for($i=1;$i<=31;$i++){
        if($i==1)$st='';else $st=',';
        $temp=tr_num($i,$mod='fa');
        $str .="$st "."'".$temp."'";
    }
    echo $str;
}

function delete_from_data_base($tbl_name,$condition,$coms_conect){
    $sql="delete from  $tbl_name where $condition ";
    $coms_conect->query($sql);
}
function check_catogory ($array_value){
    $array_valu=explode(",",$array_value);
    $tempp=(array_diff($array_valu,$_SESSION["manager_page_cat"]));
    if($tempp[0]!='')
        header('Location: new_manager_signout.php');
    else
        return 1;
}


function check_password($old_pass,$new_pass,$user_name){
    $temp=create_password($user_name,$new_pass);
    if($old_pass==$temp)
        return 1;
    else
        return 0;

}

function create_location($subdomian_add,$defult_dir,$site,$la,$tem_name,$page_id,$type,$coms_conect,$cat_id=0){
    $query23="select block_order,block_id,block_name from new_block_location where type='$type' and page_id=$page_id and tem_name='$tem_name' order by block_order";
    ///echo $query23;exit;
    $result23 = $coms_conect->query($query23);
    if($site!='main')
        include("../languages/$la.php");
    else
        include("languages/$la.php");
    while($RS23 = $result23->fetch_assoc()) {
        $block_id=$RS23['block_id'];
        $block_name=$RS23['block_name'];
        $defult_lang=$la;
        $defult_site=$site;
        if($block_id==0&&$site=='main'&&file_exists("new_template/$tem_name/blocks/$block_name.php4")){
            include_once "new_template/$tem_name/blocks/$block_name.php4";}
        else if($block_id==0&&$site!='main'&&file_exists("new_template/$tem_name/blocks/$block_name.php4"))
            include_once "../new_template/$tem_name/blocks/$block_name.php4";
        else {
            $query="select title,text,type from new_blocks where id=$block_id";
            $result = $coms_conect->query($query);
            $RS = $result->fetch_assoc();
            //echo $RS['title'].$block_id.'dddd';exit;
            if($RS['type']==1)
                echo $RS['text'];
            else if(file_exists("new_blocks/".$RS['title'].".php4")&&$site=='main')
                include "new_blocks/".$RS['title'].".php4";
            else if(file_exists("../new_blocks/".$RS['title'].".php4")&&$site!='main')
                include "../new_blocks/".$RS['title'].".php4";
        }
    }
}

function create_fotter_location($site,$tem_name,$page_id,$type,$coms_conect,$defult_lang){
    if($defult_lang=='')
        $defult_lang='fa';
    $defult_site=$site;
    $query23="select block_order,block_id,block_name from new_block_location where type='$type' and page_id='$page_id' and tem_name='$tem_name' order by block_order";

    $result23 = $coms_conect->query($query23);
    while($RS23 = $result23->fetch_assoc()) {
        $block_id=$RS23['block_id'];
        $block_name=$RS23['block_name'];
        //echo $block_name;
        echo '<div class="col-md-4">';
        if($block_id==0&&$site=='main'){
            include "new_template/$tem_name/blocks/$block_name.php4";
        }if($block_id==0&&$site!='main'){
            include "../new_template/$tem_name/blocks/$block_name.php4";
        }else {
            $query="select text from new_blocks where id=$block_id";
            $result = $coms_conect->query($query);
            $RS = $result->fetch_assoc();
            echo $RS['text'];
        }
        echo '</div>';
    }
}

function new_sign_out(){
    header('Location: new_manager_signout.php');
}

function check_lang_title($title,$manager_title_lang){
    if(in_array($title,$manager_title_lang))
        return 1;
    else
        new_sign_out();	exit();
}


function download_file($file){
    if (file_exists($file)) {
//	echo $file;exit;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}
function get_madules($type){
    if($type==1){
        $madual['title']="اخبار";
        $madual['name']='news';}
    if($type==5){
        $madual['title']="نوشته ها";
        $madual['name']='page';}
    if($type==6){
        $madual['title']="دانلود";
        $madual['name']='download';}
    if($type==7){
        $madual['title']="مقاله";
        $madual['name']='article';}
    if($type==8){
        $madual['title']="ویدئو";
        $madual['name']='video';}
    if($type==9){
        $madual['title']="گالری";
        $madual['name']='gallery';}
    if($type==10){
        $madual['title']="وبلاگ";
        $madual['name']='weblog';}
    return $madual;
}
function get_last_madual_content($la,$site,$tbl_name,$count,$coms_conect){
    $query23="select publish_date,id,title,name from $tbl_name where status=1 and site='$site' and la='$la' and status=1 order by id desc limit 0,$count";
    //echo $query23;//exit;
    $i=0;
    $result23 = $coms_conect->query($query23);
    while($RS23 = $result23->fetch_assoc()) {
        $temp[$i]['id']=$RS23['id'];
        $temp[$i]['title']=$RS23['title'];
        $temp[$i]['name']=$RS23['name'];
        $temp[$i]['name']=$RS23['name'];
        $temp[$i]['publish_date']=$RS23['publish_date'];
        $i++;
    }
    return $temp;
}
function get_pic_address($adders,$site,$domain){
    if($adders>""&&file_exists($adders))
        return $adders;
    else if($site!="main")
        return "$domain/images/nopic.jpg";
    else
        return '/images/nopic.jpg';

}

function get_modual_pic_address($adders,$site,$domain,$type){
    $arr=array('11'=>'news.jpg','7'=>'article.jpg','1'=>'news.jpg','6'=>'download.jpg','18'=>'ads.jpg');
    if($type==18&&$adders>"")
        return "/new_gallery/files/tn/$adders";
    else if($adders>"")
        return $adders;
    else if($site!="main")
        return "$domain/new_gallery/".$arr[$type];
    else
        return '/new_gallery/'.$arr[$type];

}


function take_pic_video($ffmpeg='ffmpeg',$videos){

    $videos_name=basename($videos);


    $temp=explode(".",$videos_name);
    $videos_name=$temp[0].'.jpg';
    //$cmd = "$ffmpeg -i $videos -an -y -f mjpeg  -ss 00:05:00 -vframes 1 source/comsroot/video_pic/$videos_name";

    $cmd = "$ffmpeg -ss 00:0:00 -i $videos -vframes 1 -q:v 2 source/comsroot/video_pic/$videos_name";
    $stat = exec($cmd);


    return 	"source/comsroot/video_pic/$videos_name";
}

function get_durition($ffmpeg='ffmpeg',$str){

    /* $temp=explode("/source",$str);

    $getID3 = new getID3;
$file = $getID3->analyze("source".$temp[1]);
echo $file['playtime_string']; */
    //return $file;
    // return exec("$ffmpeg -i $str 2>&1 | grep  Duration >1.txt");
    return exec("$ffmpeg -i $str 2>&1 | grep  Duration ");
}

function get_gallery_thumbnail($address,$type=2){

    if($type==1){
        $temp=explode("/new_gallery/files/",$address);
        return $temp[0]."/new_gallery/files/tn/".$temp[1];
    }if($type==3){
        $temp=explode("/",$address);
        $file_name=end($temp);
        unset($temp[count($temp)-1]);
        $address=implode('/',$temp);
        return $address."/tn/".$file_name;
    }else
        return $address;
}

function format_size($size) {
    $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    if ($size == 0) { return('n/a'); } else {
        return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
}

function get_modual_address($id,$coms_conect,$type=0,$mode=1){
    //echo "select adress from new_file_path where name='video_pic' and type=8 and id=$id";
    return get_result("select adress from new_file_path where name='video_pic' and type=8 and id=$id",$coms_conect);
}

function get_news_modual_address($id,$coms_conect){
    return get_result("select adress from new_file_path where name='news_video_pic' and type=1 and id=$id",$coms_conect);
}

function get_content_modual_address($id,$coms_conect){
    return get_result("select adress from new_file_path where name='content_video_pic' and type=11 and id=$id",$coms_conect);
}


function get_user_avatar($adders){
    if($adders>"")
        return $adders;
    else
        return '/new_gallery/no_avatar.jpg';
}

function get_email_link($id,$email,$user_id){
    return md5(md5('ya_zahra'.$id.$email.$row['user_id'].date('Y-m-d').'ya_ali'));
}


function get_template($site,$la,$coms_conect){
    $query1="select manager_id   from new_manage_lang  where lang_id='$la' and type='tl'";
    $result1 = $coms_conect->query($query1);
    $RS11 = $result1->fetch_assoc();
    $temp1=$RS11['manager_id'];

    if($temp1>""){
        $query2="select manager_id   from new_manage_lang  where lang_id='$site' and type='ts' and manager_id='$temp1'";
        $result2 = $coms_conect->query($query2);
        $RS12 = $result2->fetch_assoc();
        $temp2=$RS12['manager_id'];

    }else
        echo '<script>window.location="/404.html"</script>';
    //header("location : 404.html");
    if($temp2>""){
        $query3="select tem_name   from new_active_template  where id='$temp1'";
        $result3 = $coms_conect->query($query3);
        $RS3 = $result3->fetch_assoc();
    }else
        echo '<script>window.location="/404.html"</script>';
    if($temp1==$temp2){
        return $RS3['tem_name'];
    }

    else
        return false;


}

function create_reset_password($user_name) {
    return 	md5(md5($user_name.md5('khamenei').date('Y-m-d').md5('rahbar')));

}
function create_member_reset_password($user_name) {
    return 	md5(md5($user_name.md5('KHAMENEI').date('Y-m-d').md5('RAHBAR')));
}
function get_label_count($str,$coms_conect){
    foreach($str as $value){
        $query="update new_keyword set key_count=key_count+1 where id='$value'";
        $coms_conect->query($query);
    }
}

function injection_replace_pic($input){
    return $input;

}

function create_chart_moduale($type,$coms_conect,$mounth,$year,$day,$start,$query){
    $st='';$temp_num=$day+$start;;$j=1;
    for($i=$start;$i<=$temp_num;$i++){
        $date=strtotime(date('Y-m-d',jmktime(5, 1, 1, $mounth, $i,$year)));
        $q=$i+1;
        $date1=strtotime(date('Y-m-d',jmktime(5, 1, 1, $mounth, $q,$year)));
        $sql="select sum(view) as view from new_article a ,new_modules_catogory b where
		a.id>0 and a.id=b.page_id and b.type=$type and date>=$date and  date<$date1 
		 $query";
        $result1 = $coms_conect->query($sql);
        $RS11 = $result1->fetch_assoc();
        if($j==1)$st='';else $st=',';
        if($RS11['view']=="")
            $temp= 0;
        else
            $temp=$RS11['view'] ;

        $str .="$st  $temp";
        $j++;
    }
    echo $str;
}


function get_pic_name($str){
    $temp=explode ("/",$str);
    $i=count($temp);$i--;
    return   ($temp[$i]);
}
function farsi_date_num ($day=31,$start){
    $temp='';$j=1;$temp_num=$day+$start;
    for($i=$start;$i<=$temp_num;$i++){
        $str='';
        if($j==1)$str="";else $str=',';
        $temp .=$str.'"'. tr_num($i,$mod='fa').'"';
        $j++;
    }
    return $temp;
}

function copy_directory($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                copy_directory($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function redirect($url) {
    session_commit();
    header( "Location: {$url}" );
    exit;
}

function check_token( $user_token, $session_token, $returnURL ) {
    if( $user_token !== $session_token || !isset( $session_token ) ) {
        //redirect( $returnURL );
    }
}


function mudoal_comment_count($id,$type,$coms_conect){
    $temp=get_result("select count(id) from new_madules_comment where type=$type and madul_id=$id",$coms_conect);
    if ($temp>0)
        echo "<span class='badge badge-grey'>$temp</span>";

}


function iranian_data($date){
    $start_date=miladitojalaliuser(date('Y-m-d',$date));
    $temp_date=explode("/",$start_date);
    return $temp_date[2].jdate_words(array('mm'=>$temp_date[1]))['mm'].$temp_date[0];
}

function create_session_token() {
    if( isset( $_SESSION[ 'session_token' ] ) ) {
        destroy_session_token();
    }
    $_SESSION[ 'session_token' ] = md5( uniqid() );
}
function destroy_session_token() {
    unset( $_SESSION[ 'session_token' ] );
}

function create_token_in_function(){
    create_session_token(); ?>
    <input name='user_token' value='<?=$_SESSION[ 'session_token' ]?>' type="hidden">
    <?
}

function injection_replace($txtobject){
    $txtobject1=strtolower($txtobject);
    str_replace("'","&#39",$txtobject );
    str_replace("'","&#39",$txtobject );
    str_replace("<","&#60",$txtobject );
    str_replace(">","&#62",$txtobject );
    str_replace("or","",$txtobject );
    str_replace("delete","",$txtobject);
    str_replace("update","",$txtobject);
    str_replace("insert","",$txtobject);
    $txtobject = stripslashes($txtobject);
    $txtobject = strip_tags($txtobject);
    $txtobject = htmlspecialchars($txtobject);
//	$txtobject=mysql_real_escape_string($txtobject);
    return $txtobject;
}

function get_label_array($type,$id,$coms_conect){
    $sql = "SELECT label_id FROM  new_mudoal_label  where   type=$type and   id='$id'";
    //echo $sql;
    $result = $coms_conect->query($sql);$i=0;
    while($row = $result->fetch_assoc()) {
        $str=',';
        if($i==0)
            $str="";
        $label_arr .=$str.$row['label_id'];$i++;
    }
    return $label_arr;
}


function create_label($value,$madual,$la,$coms_conect){
    $sql = "SELECT name FROM new_keyword a ,new_mudoal_label b  where  a.id=b.label_id and b.type=1 and  b.id='$value'";
    $result = $coms_conect->query($sql);
    while($row = $result->fetch_assoc()) {

        $name=$row['name'];
        $label .="  <a target=_blank href='/$madual/$la/keyword/$name'>$name</a>  ";
    }
    echo $label;
}
function create_label_pishgaman($value,$madual,$la,$coms_conect,$type){
    $sql = "SELECT name FROM new_keyword a ,new_mudoal_label b  where  a.id=b.label_id and b.type=$type and  b.id='$value'";
    $result = $coms_conect->query($sql);
    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $label .=" <li> <a itemprop='keyword' target=_blank href='/$madual/$la/keyword/".insert_dash($name)."/1'>$name</a>  </li>";
    }
    echo $label;
}


function get_modual_cat($type,$modual_id,$coms_conect){
    $sql120 = "SELECT a.name,a.id from new_modules_cat a ,new_modules_catogory b where
						a.id=b.cat_id and b.type=$type and page_id=$modual_id  group by a.id order by a.rang";
    //echo $sql120;exit;
    $resultd10 = $coms_conect->query($sql120);
    $i=0;
    while($row = $resultd10->fetch_assoc()){
        $temp[$i]['id']=$row['id'];
        $temp[$i]['name']=$row['name'];
        $i++;
    }
    return $temp;
}
function get_meta_descripton($site,$la,$madual_id,$modual,$coms_conect,$madual_cat_id,$tem_name){
    ##news

    if($modual=='news'&&$madual_id>0){
        $table='new_news';$str="and site='$site'";$meta_desciption_id=$madual_id;

    }else
        if($modual=='news'&&$madual_cat_id>0){
            $table='new_modules_cat';$meta_desciption_id=$madual_cat_id;
        }

    ##content

    if($modual=='content'&&$madual_id>0){
        $table='new_content';$str="and site='$site'";$meta_desciption_id=$madual_id;

    }else
        if($modual=='content'&&$madual_cat_id>0){
            $table='new_modules_cat';$meta_desciption_id=$madual_cat_id;
        }


##gallery
        else if($modual=='gallery'&&$madual_id>0){
            $table='new_gallery'; $str="and site='$site'";	$meta_desciption_id=$madual_id;
        }else if($modual=='gallery'&&$madual_cat_id>0){
            $table='new_modules_cat';$meta_desciption_id=$madual_cat_id;

##ads
        }else if($modual=='ads'&&$madual_id>0){
            $table='new_ads'; $str="and site='$site'";$meta_desciption_id=$madual_id;
        }
        else if($modual=='ads'&&$madual_cat_id>0){
            $table='new_ads';$meta_desciption_id=$madual_cat_id;

##video
        }else if($modual=='video'&&$madual_id>0){
            $table='new_video'; $str="and site='$site'";$meta_desciption_id=$madual_id;
        }
        else if($modual=='video'&&$madual_cat_id>0){
            $table='new_video';$meta_desciption_id=$madual_cat_id;
        }
##blog
        else  if($modual=='blog'&&$madual_id>0){
            $table='new_blog'; $str="and site='$site'";$meta_desciption_id=$madual_id;
        }
        else if($modual=='blog'&&$madual_cat_id>0){
            $table='new_blog';$meta_desciption_id=$madual_cat_id;

##download
        }else if($modual=='download'&&$madual_id>0){
            $table='new_download'; $str="and site='$site'";$meta_desciption_id=$madual_id;
        }else if($modual=='download'&&$madual_cat_id>0){
            $table='new_download';$meta_desciption_id=$madual_cat_id;

##article
        }else if($modual=='article'&&$madual_id>0){
            $table='new_article'; $str="and site='$site'";$meta_desciption_id=$madual_id;
        }else if($modual=='article'&&$madual_cat_id>0){
            $table='new_article';$meta_desciption_id=$madual_cat_id;

##product
        }else if($modual=='product'&&$madual_id>0){
            $table='new_product'; $str="and site='$site'";$meta_desciption_id=$madual_id;
        }else if($modual=='product'&&$madual_cat_id>0){
            $table='new_product';$meta_desciption_id=$madual_cat_id;

##page
        }else if($madual_id>""){
            $table='new_static_page'; $str="and site='$site'";
            return get_result("select meta_description from $table where name='$madual_id' and la='$la' $str",$coms_conect);

            //return
        }/*else if($modual=='page'&&$madual_cat_id>0){
		$table='new_static_page';$meta_desciption_id=$madual_cat_id;
	} */

    if($table){

        $temp= get_result("select meta_desciption from $table where id=$meta_desciption_id and la='$la' $str",$coms_conect);
        if($temp>"")
            return $temp;
    }
    if($temp='')
        return 	get_tem_result($site,$la,"header_title",$tem_name,$coms_conect);
    else{
        $temp= get_tem_result($site,$la,"header_title",$tem_name,$coms_conect);
        return $temp['text'];
    }

}
function get_meta_keyword($site,$la,$madual_id,$modual,$coms_conect,$madual_cat_id,$tem_name){

##news
    if($modual=='news'&&$madual_id>0){
        $table='new_news';$str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='news'&&$madual_cat_id>0){
        $table='new_modules_cat';$meta_keyword_id=$madual_cat_id;

##content
    }if($modual=='content'&&$madual_id>0){
        $table='new_content';$str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='content'&&$madual_cat_id>0){
        $table='new_modules_cat';$meta_keyword_id=$madual_cat_id;


##gallery
    }else if($modual=='gallery'&&$madual_id>0){
        $table='new_gallery'; $str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='gallery'&&$madual_cat_id>0){
        $table='new_modules_cat';$meta_keyword_id=$madual_cat_id;
##ads
    }else if($modual=='ads'&&$madual_id>0){
        $table='new_ads'; $str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='ads'&&$madual_cat_id>0){
        $table='new_ads';$meta_keyword_id=$madual_cat_id;

##video
    }else if($modual=='video'&&$madual_id>0){
        $table='new_video'; $str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='video'&&$madual_cat_id>0){
        $table='new_video';$meta_keyword_id=$madual_cat_id;

##blog
    }else if($modual=='blog'&&$madual_id>0){
        $table='new_blog'; $str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='blog'&&$madual_cat_id>0){
        $table='new_blog';$meta_keyword_id=$madual_cat_id;

##download
    }else if($modual=='download'&&$madual_id>0){
        $table='new_download'; $str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='download'&&$madual_cat_id>0){
        $table='new_download';$meta_keyword_id=$madual_cat_id;

##article
    }else if($modual=='article'&&$madual_id>0){
        $table='new_article'; $str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='article'&&$madual_cat_id>0){
        $table='new_article';$meta_keyword_id=$madual_cat_id;

##product
    }else if($modual=='product'&&$madual_id>0){
        $table='new_product'; $str="and site='$site'";$meta_keyword_id=$madual_id;
    }else if($modual=='product'&&$madual_cat_id>0){
        $table='new_product';$meta_keyword_id=$madual_cat_id;

##page
    }else if($madual_id>""){
        $table='new_static_page'; $str="and site='$site'";
        return get_result("select meta_keyword from $table where name='$madual_id' and la='$la' $str",$coms_conect);
    }/*else if($modual=='page'&&$madual_cat_id>0){
		$table='new_static_page';$meta_keyword_id=$madual_cat_id;
	}	*/

    if($table){
        $temp= get_result("select meta_keyword from $table where id=$meta_keyword_id and la='$la' $str",$coms_conect);
        if($temp>"")
            return $temp;
    }
    if($temp==''){
        $temp=get_tem_result($site,$la,"header_title",$tem_name,$coms_conect);
        return $temp['link'];
    }

    else{

        $temp= get_tem_result($site,$la,"header_title",$tem_name,$coms_conect);
        return $temp['link'];
    }
}



function create_menu_index_float_mobile($parentID,$site_id,$la,$coms_conect,$a_class,$li_class,$ul_class,$i){
    $sql = "SELECT * FROM new_menu WHERE parent_id='$parentID' and site_id='$site_id' and la='$la' and float_menu=1 and status=1 ORDER BY rang";
    $result = $coms_conect->query($sql);
    if ($result->num_rows > 0) {
        echo "<ul class='$ul_class$i'>";
        while($row = $result->fetch_assoc()) {
            $target=get_target ($row['target']);
            echo "<li class='$li_class$i'>";
            $href=$row['link'];
            $div_class='';
            $i_class='';
            $li_class='';
            if(check_has_child_float($row['id'],$site_id,$la,$coms_conect)==1){
                $i_class="<div class='handle'>  <i class='fa fa-plus extender_btn_0$i'></i>";
                $div_class='</div>';
                $li_class='msmf_has_child_0'.$i;

            }
            echo "$i_class <a   $target class='$a_class' href='$href'><i class='  '></i>{$row['name']}</a>$div_class";
            $i++;
            create_menu_index_float_mobile($row['id'],$site_id,$la,$coms_conect,'','msmf_has_child_0','msmf_rtl_menu_lev_0',$i);
            echo "</li>";
        }
        echo "</ul>";
    }
}








function view_module($type,$coms_conect){


    $date=date('Y-m-d');
    $query="SELECT count_view from new_module_view where date='$date' and type=$type";
    $result = $coms_conect->query($query);

    if ($result->num_rows > 0) {
        $query1="update new_module_view set count_view=count_view+1 where type=$type and date='$date'";
        $coms_conect->query($query1);
        //echo $query1;
    }else{
        $query1="insert into new_module_view(date,type,count_view)values('$date',$type,1)";
        $coms_conect->query($query1);
    }
}

function get_login_form($site_url,$la,$tem_name){
    include 'new_lock_page.php';
    //echo "new_template/$tem_name/blocks/footer.php4";
    include "new_template/$tem_name/blocks/footer.php4";
    exit;
    echo '<br>';
    echo '<br>';

    echo "<div class='col-md-10'>";
    echo "این مطلب مخصوص اعضا می باشد<br><a href='#' data-url='$site_url' id='login_page_modal' data-modual='news' data-toggle='modal' data-target='#myModal'>ورود به سایت</a>";
    echo "</div>";
    echo '<br>';
    echo '<br>';
}

function crate_capcha_pic($la){
    if($la=='fa'||$la=='ar')
        echo '/new_dynamics/captcha_fa.php';
    else
        echo '/new_dynamics/captcha.php';


}
function get_blog_type($id){
    if($id==0) return 'متنی';
    if($id==1) return 'ویدئویی';
    if($id==2) return 'صوتی';
    if($id==3) return 'تصویری';


}



function create_manager_groups($manager_group,$coms_conect){
    $sql = "SELECT name,id FROM new_groups where parrent_id=$manager_group";

    $result = $coms_conect->query($sql);
    while($row = $result->fetch_assoc()) {
        $name=$row['name'];
        $man_id=$row['id'];?>
        <option value="<?=$man_id?>"><?=$name?></option>
        <?if($man_id>"")
            create_manager_groups($man_id,$coms_conect);
    }
}

?>