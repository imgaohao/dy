<?php
const DS = DIRECTORY_SEPARATOR;
//原始json数据
const DATA_DIR = __DIR__ . DS .  'data' . DS . 'json' . DS;
//待下载视频数据
const DATA_VIDEO_A_DIR = __DIR__ . DS .  'data' . DS . 'video_a' . DS;
//已下载视频数据
const DATA_VIDEO_B_DIR = __DIR__ . DS .  'data' . DS . 'video_b' . DS;
//视频保存目录
const SAVE_DIR = __DIR__ . DS .  'videos' . DS;
//视频数据后缀
const DATA_VIDEO_EXT = '.txt';
//视频后缀
const VIDEO_EXT = '.mp4';

$curl = getCurl();
jsonToVideoData();
videoDataA();

function videoDataA()
{
    set_time_limit(0);
    while ($file_list = getFiles(DATA_VIDEO_A_DIR)) {
        foreach ($file_list as $file_name) {
            $content = file_get_contents($file_name);
            $data = explode(PHP_EOL, $content);
            $save_name = str_replace(DATA_VIDEO_EXT, '', $file_name);
            $save_name = str_replace(DATA_VIDEO_A_DIR, '', $save_name);
            $share_url = $data[0];
            $video_url = !empty($data[1]) ? $data[1] : getVideoUrl($share_url);

            if ($video_url) {
                $result = saveVideoUrl($video_url, $save_name);
                if ($result) {
                    rename($file_name, str_replace(DATA_VIDEO_A_DIR, DATA_VIDEO_B_DIR, $file_name));
                }
            }
        }
    }
}

function videoExist($name)
{
    return file_exists(SAVE_DIR . $name . VIDEO_EXT);
}

function jsonToVideoData()
{
    $file_list = getFiles(DATA_DIR);
    foreach ($file_list as $file_name) {
        $data = getDataFromJsonFile($file_name);
        if (empty($data['aweme_list'])) {
            unlink($file_name);
            continue;
        }
        foreach ($data['aweme_list'] as $aweme) {
            $aweme_id = $aweme['aweme_id'];
            $uid = $aweme['author']['uid'];
            $share_url = $aweme['share_url'];
            $video_url = getVideoUrl($share_url);
            $data_file = $uid . '_' . $aweme_id . DATA_VIDEO_EXT;
            saveDataVideo($data_file, $share_url, $video_url);
        }
        unlink($file_name);
    }
}

function saveDataVideo($data_file, $share_url, $video_url)
{
    $data_content = $share_url . PHP_EOL . $video_url;
    file_put_contents(DATA_VIDEO_A_DIR . $data_file, $data_content);
}

function getVideoUrl($share_url)
{
    $html_content = getUrl($share_url);
    sleep(1);
    preg_match('/playAddr:\s*"(.*?)"/', $html_content, $matches);
    return !empty($matches[1]) ? $matches[1] : '';
}

function getCurl()
{
    $curl = curl_init();
    curl_setopt_array($curl, [
//    CURLOPT_PROXY => '127.0.0.1:1080',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
        ]
    ]);
    return $curl;
}

function getUrl($url)
{
    global $curl;
    curl_setopt($curl, CURLOPT_URL, $url);
    return curl_exec($curl);
}

function getDataFromJsonFile($file_path)
{
    $json_content = file_get_contents($file_path);
    return json_decode($json_content, true);
}

function getFiles($dir)
{
    $list = array_diff(scandir($dir), ['.', '..']);
    foreach ($list as &$file_name) {
        $file_name = $dir . $file_name;
    }
    return $list;
}

function saveVideoUrl($video_url, $save_name)
{
    if (videoExist($save_name)) {
        return true;
    }
    return saveVideoContent(getUrl($video_url), $save_name);
}

function saveVideoContent($video_content, $save_name)
{
    return file_put_contents(SAVE_DIR . $save_name . VIDEO_EXT, $video_content);
}
