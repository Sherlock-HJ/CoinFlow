<?php


///5.5.0以下的版本
$isFollowing5_5_0 = version_compare(PHP_VERSION, '5.5.0', '<');
///跨域
header("Access-Control-Allow-Origin: *");

$method = 'GET';
if (!empty($_SERVER['REQUEST_METHOD'])) {
    $method = $_SERVER['REQUEST_METHOD'];
}


$url = 'http:/' . $_SERVER["PATH_INFO"] . "?" . $_SERVER["QUERY_STRING"];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);


curl_setopt($ch, CURLOPT_HEADER, false);

if ($method == 'POST') {
    curl_setopt($ch, CURLOPT_POST, 1);

    /**
     * 传递一个数组到 CURLOPT_POSTFIELDS，
     * cURL会把数据编码成 multipart/form-data，
     * 而然传递一个URL-encoded字符串时，
     * 数据会被编码成 application/x-www-form-urlencoded。
     * */
    $data = null;
    $codeType = empty($_SERVER['CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : $_SERVER['CONTENT_TYPE'];
    if (strpos($codeType, "application/x-www-form-urlencoded") !== false) {
        $data = http_build_query($_POST);
    } elseif (strpos($codeType, "multipart/form-data") !== false) {
        $files = [];
        foreach ($_FILES as $key => $value) {


            if ($isFollowing5_5_0) {
                $files[$key] = '@' . $value['tmp_name'];

            } else {
                $files[$key] = new CURLFile($value['tmp_name'], $value['type'], $value['name']);;

            }
        }
        $data = array_merge($_POST, $files);
    }


    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, !$isFollowing5_5_0);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

}

curl_exec($ch);

curl_close($ch);


?>