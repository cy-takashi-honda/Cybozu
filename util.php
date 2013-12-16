<?php
define('BASE_URL', 'http://FQDN/cgi-bin/cbag/ag.exe?');
define('USER_ID', 'YOUR_CYBOZU_USER_ID');
define('USER_PASS', 'YOUR_CYBOZU_PASSWORD');
define('SEARCH_URL_BASE', '_System=login&_Login=1&LoginMethod=1&_ID=' . USER_ID . '&Password=' . USER_PASS . '&gid=virtual&page=ScheduleIndex&Text=');
define('USER_URL_BASE', '_System=login&_Login=1&LoginMethod=1&_ID=' . USER_ID . '&Password=' . USER_PASS . '&page=UserListView&uid=');


//cybozuのURLにリクエスト
function call_cybozu($post_value) {
    $c = curl_init();

    curl_setopt($c, CURLOPT_URL, BASE_URL);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $post_value);
    curl_setopt($c, CURLOPT_RETURNTRANSFER,true);

    $result = curl_exec($c);
    curl_close($c);

    if ($result === false) {
        echo 'Network ERROR' . "\n";
        exit;
    }

    return mb_convert_encoding($result, 'UTF-8', 'SJIS');
}


//検索結果ページからユーザー情報取得
function get_user_info($html_str) {
    $user_info_list = array();
    $name_list = array();
    $anchor_list = array();
    $html = str_get_html($html_str);

    //ユーザー情報が書いてある部分のリンク取得
    //名前とIDをもらう
    foreach ($html->find('table.schedule tr.eventrow td.usercell a') as $a) {
        $anchor_list[] = $a->outertext;
        $name_list[] = $a->innertext;
    }

    //ユーザーIDを取得
    foreach ($anchor_list as $num => $anchor_outertext) {
        $preg_arr = array();
        preg_match("/(.*UID\=)([0-9]{5})(.*)/", $anchor_outertext, $preg_arr);
        if (isset($preg_arr[2]) === true && in_array($preg_arr[2], $user_info_list) !== true) {
            $user_info_list[$name_list[$num]] = $preg_arr[2];
        }
    }
    //key => value; ユーザー名 => ユーザーID
    return $user_info_list;
}


//ユーザー情報ページから内線番号取得
function parse_inner_tell_num($html_str) {
    $html = str_get_html($html_str);

    $strArray = array();

    //tableから内線番号取得
    //th 内線, td 番号
    foreach($html->find('table.dataView tr') as $tr) {
        foreach($tr->find('th') as $th) {
            if (preg_match('/.*内線.*/', $th->outertext)) {
                foreach ($tr->find('td') as $td) {
                    $call_num = $td->innertext;
                }
            }
        }
    }

    if (!preg_match('/[0-9]{4}/', $call_num)) {
        $call_num = 'NOT FOUND';
    }

    return $call_num;
}
