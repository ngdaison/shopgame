<?php

$id = $_GET['id'] ?? null;

if ($id === null || !is_numeric($id)) {
  die(json_encode([
    'status'  => 400,
    'message' => 'Không tìm thấy thông tin sản phẩm này',
  ]));
}

$link = "https://lienminhshop.vn/Data/upload/images/SkinsId/" . $id . ".jpg";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $link);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
  die(json_encode([
    'status'  => 400,
    'message' => 'Không tìm thấy thông tin skin này',
  ]));
}

curl_close($ch);

header('Content-Type: image/png');
echo $response;