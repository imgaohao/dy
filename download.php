<?php
const DATA_DIR = __DIR__ . DIRECTORY_SEPARATOR .  'data' . DIRECTORY_SEPARATOR;

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_PROXY => '127.0.0.1:1080',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
    ]
]);

$file_list = array_diff(scandir(DATA_DIR), ['.', '..']);
foreach ($file_list as $file_name) {
    $file = DATA_DIR . $file_name;
    $json_content = file_get_contents($file);
    $data = json_decode($json_content, true);
    foreach ($data['aweme_list'] as $aweme) {
        curl_setopt($curl, CURLOPT_URL, $aweme['share_url']);
        $html_content = curl_exec($curl);
        preg_match('/playAddr:\s*"(.*?)"/', $html_content, $matches);
        if (!empty($matches[1])) {
            curl_setopt($curl, CURLOPT_URL, $matches[1]);
            $video_content = curl_exec($curl);
            file_put_contents(time() . '.mp4', $video_content);
        } else {

        }
        exit;
    }
}
