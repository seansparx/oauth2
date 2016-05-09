<?php

session_start();

define("CLIENT_ID", "rk0001");
define("CLIENT_PWD", "rk@123456");

if ($_SESSION['refresh_time']) {
    if (time() >= $_SESSION['refresh_time']) {
        $token = json_decode(refresh_token($_SESSION['refresh_token']));
        $_SESSION['token'] = $token;
        $_SESSION['access_token'] = $token->access_token;
        $_SESSION['refresh_token'] = $token->refresh_token;
        $_SESSION['refresh_time'] = date("Y-m-d H:i:s", strtotime("+" . $token->expires_in . " Seconds"));

        echo 'Token Refreshed : ';
    } else {
        echo "Token will Expire on : " . date("d/m/Y, h:i:s a", $_SESSION['refresh_time']);
    }
} else {
    $auth_code = get_authorize_code();
    $token = json_decode(get_token($auth_code, "1918bc730067bfdb01bda70b161e82e533d4867b"));
    $_SESSION['token'] = $token;
    $_SESSION['access_token'] = $token->access_token;
    $_SESSION['refresh_token'] = $token->refresh_token;
    $_SESSION['refresh_time'] = strtotime("+" . $token->expires_in . " Seconds");

    echo 'New Token is : ';
}

$result = json_decode(read_resource($_SESSION['access_token']));

echo '<pre>';
print_r($result);
echo '</pre>';


function read_resource($access_token) {
    return Post("http://localhost/oauth2/CodeIgniter/api/resource", array('access_token' => $access_token));
}

function get_authorize_code() {
    return Post("http://localhost/oauth2/CodeIgniter/api/authorize", array('response_type' => 'code', "client_id" => CLIENT_ID, "state" => "abc"));
}

function get_token($auth_code, $refresh_token = null) {
    $client = CLIENT_ID . ":" . CLIENT_PWD;
    return Post("http://localhost/oauth2/CodeIgniter/api/token", array("grant_type" => "authorization_code", "code" => $auth_code), $client);
}

function refresh_token($refresh_token) {
    $client = CLIENT_ID . ":" . CLIENT_PWD;
    return Post("http://localhost/oauth2/CodeIgniter/api/token", array("grant_type" => "refresh_token", "refresh_token" => $refresh_token), $client);
}


/** 
 * Send a HTTP POST to a remote url
 */
function Post($url, $param, $client = null)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    if ($client) {
        curl_setopt($ch, CURLOPT_USERPWD, $client); //Your credentials goes here
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //IMP if the url has https and you don't want to verify source certificate    
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);

    curl_close($ch);
    return $output;
}

?>


