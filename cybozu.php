<?php
require_once(dirname(__FILE__) . '/util.php');
require_once(dirname(__FILE__) . '/simple_html_dom.php');

if (isset($argv[1]) !== true) {
    echo 'Input keywords';
    exit;
}


$search_url = SEARCH_URL_BASE . $argv[1];

$search_result = call_cybozu($search_url);

$user_list = get_user_info($search_result);

foreach ($user_list as $name => $uid) {
    $post = USER_URL_BASE . $uid;
    $result = call_cybozu($post);
    $call_num = parse_inner_tell_num($result);
    echo $name . '=>' . $call_num . "\n";
}
