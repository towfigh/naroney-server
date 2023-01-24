<?php

include_once("db/database.php");

// **********
// Main
// **********
function response($status, $message = '', $data = null)
{
    echo json_encode(
        [
            'status' => $status,
            'msg' => $message,
            'data' => $data,
        ],
        200,
        JSON_UNESCAPED_UNICODE
    );
}

function injection_replace($txtobject)
{
    $txtobject1 = strtolower($txtobject);
    str_replace("'", "&#39", $txtobject);
    str_replace("'", "&#39", $txtobject);
    str_replace("<", "&#60", $txtobject);
    str_replace(">", "&#62", $txtobject);
    str_replace("or", "", $txtobject);
    str_replace("delete", "", $txtobject);
    str_replace("update", "", $txtobject);
    str_replace("insert", "", $txtobject);
    $txtobject = stripslashes($txtobject);
    $txtobject = strip_tags($txtobject);
    $txtobject = htmlspecialchars($txtobject);
    return $txtobject;
}

function getAllFeed()
{
    return
        [
            'contact' => getContactInfo(),
            'categories' => getAllCategries(),
            'colors' => getAllColors(),
            'sizes' => getAllSizes(),
            'products' => getAllProducts(),
        ];
}




// **********
// Contact Info
// **********
function getContactInfo()
{
    global $conn;

    $sql = "SELECT * FROM contact";
    $contact = $conn->query($sql);
    while ($row = $contact->fetch_assoc()) {
        $contacts[] = $row;
    }
    return $contacts;
}
function editContactInfo($tel, $phone1, $phone2, $address, $insta, $email, $subTop, $subDown, $about1, $about2, $date)
{
    global $conn;

    $send_status = 1;

    $contact = getContactInfo();

    if ($contact[0]['value'] != $tel) {
        $query = "UPDATE contact SET value='$tel', updated_at ='$date' WHERE id=1";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[1]['value'] != $phone1) {
        $query = "UPDATE contact SET value='$phone1', updated_at ='$date' WHERE id=2";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[2]['value'] != $phone2) {
        $query = "UPDATE contact SET value='$phone2', updated_at ='$date' WHERE id=3";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[3]['value'] != $address) {
        $query = "UPDATE contact SET value='$address', updated_at ='$date' WHERE id=4";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[4]['value'] != $insta) {
        $query = "UPDATE contact SET value='$insta', updated_at ='$date' WHERE id=5";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[5]['value'] != $email) {
        $query = "UPDATE contact SET value='$email', updated_at ='$date' WHERE id=6";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[6]['value'] != $subTop) {
        $query = "UPDATE contact SET value='$subTop', updated_at ='$date' WHERE id=7";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[7]['value'] != $subDown) {
        $query = "UPDATE contact SET value='$subDown', updated_at ='$date' WHERE id=8";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[8]['value'] != $about1) {
        $query = "UPDATE contact SET value='$about1', updated_at ='$date' WHERE id=9";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }
    if ($contact[9]['value'] != $about2) {
        $query = "UPDATE contact SET value='$about2', updated_at ='$date' WHERE id=10";
        // $conn->query($query);
        if ($conn->query($query) !== TRUE) {
            $send_status = 0;
        }
    }

    return ['stt' => $send_status, 'contact' => getContactInfo()];
}



// **********
// Products
// **********
function addProduct($name, $category, $size, $color, $sell, $rent, $desc, $images, $date)
{
    global $conn;

    $status = true;

    $id = uniqid();
    $main_image = $id . '_0';

    $sql_p = "INSERT INTO products (code, name, description, image, size_id, color_id, category_id, visit_count, score, is_sell, is_rent, is_active, created_at, updated_at, deleted_at) 
    VALUES ('$id', '$name', '$desc', '$main_image', '$size', '$color', '$category', 0, 0, '$sell', '$rent', 1, '$date', '$date', null)";


    if ($conn->query($sql_p) !== TRUE) {
        $status = false;
    }


    $DIR = '/home/rygysdzw/api/images/product/';

    for ($i = 0; $i < count($images); $i++) {

        $file_chunks = explode(";base64,", $images[$i]);
        $base64Img = base64_decode($file_chunks[1]);
        $image_name = $id . '_' . $i;
        $file = $DIR . $image_name . '.jpg';
        file_put_contents($file, $base64Img);

        $img_sql = "INSERT INTO images (product_id, image, is_active, created_at) VALUES ('$id','$image_name', 1, '$date')";
        if ($conn->query($img_sql) !== TRUE) {
            $status = false;
        }
    }


    if ($status === true) {
        return 1;
    } else {
        return 0;
    }
}


function deleteProduct($id, $code, $date)
{
    global $conn;
    $DIR = '/home/rygysdzw/api/images/product/';


    $sql_old = "SELECT * FROM images WHERE product_id='$code'";
    $olds = $conn->query($sql_old);
    if ($olds->num_rows > 0) {
        while ($row = $olds->fetch_assoc()) {
            $oldImages[] = $row;
        }
        for ($i = 0; $i < count($oldImages); $i++) {
            $old_file = $DIR . $code . '_' . $i . '.jpg';
            unlink($old_file);
        }
        $sql_del_img = "DELETE FROM images WHERE product_id='$code'";
        $olds = $conn->query($sql_del_img);
    }

    $query = "UPDATE products SET deleted_at='$date', updated_at ='$date'
     WHERE id='$id'";

    if ($conn->query($query) === TRUE) {
        return 1;
    } else {
        return $conn->error;
    }

}

function getImagesByCode($code)
{
    global $conn;

    $sql = "SELECT id,image FROM images WHERE product_id='$code'";

    $images = $conn->query($sql);
    if ($images->num_rows > 0) {
        while ($row = $images->fetch_assoc()) {
            $allImages[] = $row;
        }
    } else {
        $allImages = [];
    }

    return $allImages;
}

function editProduct($name, $category, $size, $color, $sell, $rent, $desc, $id, $code, $images, $date, $isOldImg)
{
    global $conn;
    $DIR = '/home/rygysdzw/api/images/product/';

    if ($isOldImg == 1) {
        $sql = "UPDATE products SET name = '$name', category_id = '$category', size_id = '$size', color_id = '$color', is_sell = '$sell', is_rent = '$rent', description = '$desc', updated_at = '$date' WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            return 1;
        } else {
            return 0;
        }
    } else {

        $status = true;
        $newId = uniqid();
        $main_image = $newId . '_0';

        $sql = "UPDATE products SET name = '$name', image = '$main_image' , code = '$newId', category_id = '$category', size_id = '$size', color_id = '$color', is_sell = '$sell', is_rent = '$rent', description = '$desc', updated_at = '$date' WHERE id='$id'";
        if ($conn->query($sql) !== TRUE) {
            $status = false;
        }

        // Delete old images :
        $sql_old = "SELECT * FROM images WHERE product_id='$code'";
        $olds = $conn->query($sql_old);
        if ($olds->num_rows > 0) {
            while ($row = $olds->fetch_assoc()) {
                $oldImages[] = $row;
            }
            for ($i = 0; $i < count($oldImages); $i++) {
                $old_file = $DIR . $code . '_' . $i . '.jpg';
                unlink($old_file);
            }
            $sql_del_img = "DELETE FROM images WHERE product_id='$code'";
            $olds = $conn->query($sql_del_img);
        }

        // Add new images :
        for ($i = 0; $i < count($images); $i++) {
            $file_chunks = explode(";base64,", $images[$i]);
            $base64Img = base64_decode($file_chunks[1]);
            $image_name = $newId . '_' . $i;
            $file = $DIR . $image_name . '.jpg';
            file_put_contents($file, $base64Img);

            $img_sql = "INSERT INTO images (product_id, image, is_active, created_at) VALUES ('$newId','$image_name', 1, '$date')";
            if ($conn->query($img_sql) !== TRUE) {
                $status = false;
            }
        }




        if ($status === true) {
            return 1;
        } else {
            return 0;
        }
    }
}

function getAllProducts()
{
    global $conn;

    $sql = "SELECT * FROM products WHERE deleted_at IS NULL";

    $allProducts = $conn->query($sql);
    if ($allProducts->num_rows > 0) {
        while ($row = $allProducts->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $products = [];
    }

    return $products;
}

function getProductByCode($code)
{
    global $conn;

    $sql = "SELECT * FROM products WHERE code = '$code' AND deleted_at IS NULL";

    $allProducts = $conn->query($sql);

    if ($allProducts->num_rows > 0) {
        while ($row = $allProducts->fetch_assoc()) {
            $product[] = $row;
        }
    } else {
        $product = [];
    }

    $sql_count = "UPDATE products SET visit_count = visit_count+1 WHERE code = '$code'";
    $result = $conn->query($sql_count);

    return
        [
            'product' => $product,
            'result' => $result,
            'images' => getImagesByCode($code),
            'similar' => getProductsByCategory($product[0]['category_id']),
        ];

}

function getProductsByCategory($cat)
{
    global $conn;

    if ($cat == 0) {
        $sql = "SELECT * FROM products WHERE deleted_at IS NULL";

        $allProducts = $conn->query($sql);

        if ($allProducts->num_rows > 0) {
            while ($row = $allProducts->fetch_assoc()) {
                $products[] = $row;
            }
        } else {
            $products = [];
        }

        return $products;
    } else {
        $sql = "SELECT * FROM products WHERE category_id = '$cat' AND deleted_at IS NULL";

        $allProducts = $conn->query($sql);

        if ($allProducts->num_rows > 0) {
            while ($row = $allProducts->fetch_assoc()) {
                $products[] = $row;
            }
        } else {
            $products = [];
        }

        return $products;
    }
}


// **********
// Categories
// **********
function addCategory($name, $imageName, $image, $date)
{
    global $conn;

    $DIR = '/home/rygysdzw/api/images/category/';
    $file_chunks = explode(";base64,", $image);
    $base64Img = base64_decode($file_chunks[1]);
    $file = $DIR . $imageName . '.jpg';
    file_put_contents($file, $base64Img);

    $sql = "INSERT INTO categories (name, image, created_at, updated_at, deleted_at) 
    VALUES ('$name','$imageName','$date','$date',null)";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        return 0;
    }
}
function editCategory($name, $imageName, $image, $date, $isOldImg)
{
    global $conn;
    $DIR = '/home/rygysdzw/api/images/category/';

    if ($isOldImg == 1) {
        $sql = "UPDATE categories SET name = '$name', updated_at = '$date' 
        WHERE image='$imageName'";

        if ($conn->query($sql) === TRUE) {
            return 1;
        } else {
            return 0;
        }
    } else {
        $file = $DIR . $imageName . '.jpg';
        unlink($file);
        $file_chunks = explode(";base64,", $image);
        $newUniqueId = uniqid();
        $base64Img = base64_decode($file_chunks[1]);
        $file = $DIR . $newUniqueId . '.jpg';
        file_put_contents($file, $base64Img);

        $sql = "UPDATE categories SET name = '$name', updated_at = '$date', image = '$newUniqueId' 
        WHERE image='$imageName'";

        if ($conn->query($sql) === TRUE) {
            return 1;
        } else {
            return 0;
        }
    }
}

function deleteCategory($id, $date)
{
    global $conn;

    $query = "UPDATE categories SET deleted_at='$date', updated_at ='$date'
     WHERE image='$id'";

    if ($conn->query($query) === TRUE) {
        return 1;
    } else {
        return $conn->error;
    }

}

function getAllCategries()
{
    global $conn;

    $sql = "SELECT id, name, image, created_at, deleted_at, updated_at
     FROM categories WHERE deleted_at IS NULL";
    $allCategory = $conn->query($sql);
    if ($allCategory->num_rows > 0) {
        while ($row = $allCategory->fetch_assoc()) {
            $categories[] = $row;
        }
    } else {
        return [];
    }
    return $categories;
}

// **********
// User
// **********
function getUser($username, $password)
{
    global $conn;

    $sql = "SELECT * FROM users where username='{$username}' ";
    $user = $conn->query($sql)->fetch_assoc();
    return $user;
}

function getUserById($id)
{
    global $conn;

    $sql = "SELECT * FROM users where id='$id' ";
    $user = $conn->query($sql)->fetch_assoc();
    return $user;
}

function editUser($username, $password, $tel, $email, $userId, $date)
{
    global $conn;

    $query = "UPDATE users SET username='$username', password='$password' , email ='$email' , tel ='$tel' , updated_at ='$date' WHERE id=$userId";
    if ($conn->query($query) === TRUE) {
        $sql = "SELECT * FROM users where username='{$username}' ";
        $user = $conn->query($sql)->fetch_assoc();
        return ['stt' => 'ok', 'user' => $user];
    } else {
        return ['stt' => 'err', 'user' => null];
    }
}


// **********
// Colors
// **********

function getAllColors()
{
    global $conn;

    $sql = "SELECT * FROM colors WHERE deleted_at IS NULL";
    $colors = $conn->query($sql);
    if ($colors->num_rows > 0) {
        while ($row = $colors->fetch_assoc()) {
            $result[] = $row;
        }
        return $result;
    } else {
        return [];
    }
}



// **********
// Sizes
// **********

function getAllSizes()
{
    global $conn;

    $sql = "SELECT * FROM sizes WHERE deleted_at IS NULL";
    $sizes = $conn->query($sql);
    if ($sizes->num_rows > 0) {
        while ($row = $sizes->fetch_assoc()) {
            $result[] = $row;
        }
        return $result;
    } else {
        return [];
    }
}


// **********
// Messages
// **********

function sendMessage($name, $mobile, $subject, $text, $date)
{
    global $conn;

    $sql = "INSERT INTO messages (name, phone, subject, text, seen_at, created_at, deleted_at) 
    VALUES ('$name','$mobile','$subject' ,'$text' , null ,'$date',null)";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        return 0;
    }
}

function seenMessage($id, $date)
{
    global $conn;

    $query = "UPDATE messages SET seen_at='$date' WHERE id='$id'";
    if ($conn->query($query) === TRUE) {
        return 1;
    } else {
        return 0;
    }
}

function deleteMessage($id, $date)
{
    global $conn;

    $query = "UPDATE messages SET deleted_at='$date' WHERE id='$id'";
    if ($conn->query($query) === TRUE) {
        return 1;
    } else {
        return 0;
    }
}

function getAllMessages()
{
    global $conn;

    $sql = "SELECT * FROM messages WHERE deleted_at IS NULL";
    $sizes = $conn->query($sql);
    if ($sizes->num_rows > 0) {
        while ($row = $sizes->fetch_assoc()) {
            $result[] = $row;
        }
        return $result;
    } else {
        return [];
    }
}



?>