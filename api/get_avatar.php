<?php
require_once '../config/db.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

function outputErrorImage($text = '?') {
    header('Content-Type: image/png');
    $image = imagecreatetruecolor(100, 100);
    $bgColor = imagecolorallocate($image, 200, 200, 200);
    imagefill($image, 0, 0, $bgColor);
    $textColor = imagecolorallocate($image, 100, 100, 100);
    imagestring($image, 5, 35 - strlen($text) * 5, 40, $text, $textColor);
    imagepng($image);
    imagedestroy($image);
    exit;
}

function getMimeType($data) {
    $info = getimagesizefromstring($data);
    if ($info !== false) {
        return $info['mime'];
    }
    $ext = '.bin';
    if (substr($data, 0, 2) == "\xff\xd8") $ext = '.jpg';
    elseif (substr($data, 0, 4) == "\x89PNG") $ext = '.png';
    elseif (substr($data, 0, 3) == 'GIF') $ext = '.gif';
    elseif (substr($data, 0, 12) == "\x52\x49\x46\x46\x00\x00\x00\x00\x57\x45\x42\x50") $ext = '.webp';
    
    $mimeMap = [
        '.jpg' => 'image/jpeg',
        '.png' => 'image/png',
        '.gif' => 'image/gif',
        '.webp' => 'image/webp',
        '.bin' => 'application/octet-stream'
    ];
    return isset($mimeMap[$ext]) ? $mimeMap[$ext] : 'application/octet-stream';
}

function fetchExternalImage($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, '');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
        ]
    ]);
    return file_get_contents($url, false, $context);
}

if (!$user_id) {
    outputErrorImage('?');
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT avatar, avatar_data FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        outputErrorImage('?');
    }

    if ($user['avatar'] && preg_match('/^https?:\/\//i', $user['avatar'])) {
        $selfUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/api/get_avatar.php';
        if (strpos($user['avatar'], $selfUrl) === 0) {
            if ($user['avatar_data']) {
                $imageData = $user['avatar_data'];
                $mime = getMimeType($imageData);
                header('Content-Type: ' . $mime);
                header('Content-Length: ' . strlen($imageData));
                echo $imageData;
                exit;
            }
        } else {
            $imageData = fetchExternalImage($user['avatar']);
            if ($imageData !== false && strlen($imageData) > 0) {
                $mime = getMimeType($imageData);
                header('Content-Type: ' . $mime);
                header('Content-Length: ' . strlen($imageData));
                echo $imageData;
                exit;
            }
        }
    }

    if ($user['avatar_data']) {
        $imageData = $user['avatar_data'];
        $mime = getMimeType($imageData);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . strlen($imageData));
        echo $imageData;
        exit;
    }

    outputErrorImage('?');
} catch (PDOException $e) {
    outputErrorImage('ERR');
}
?>