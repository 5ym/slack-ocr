<?php
  $slackkey = '';//botのキー
  $workkey = '';//slackのワークスペースのキー
  $googlekey = '';//google cloud visionのキー
  $origin = json_decode(file_get_contents('php://input'));
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/files.sharedPublicURL?token='.$workkey.'&file='.$origin->event->file->id);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $res1 = curl_exec($ch);
  $res2 = curl_getinfo($ch);
  curl_close($ch);
  $res = substr($res1, $res2['header_size']);
  $res = json_decode($res);
  $key = substr($res->file->permalink_public, -10);
  $img = $res->file->url_private.'?pub_secret='.$key;
  $data = array(
    'requests' => array(
      'image' => array(
        'content' => base64_encode(file_get_contents($img))
      ),
      'features' => array(
        'type' => 'TEXT_DETECTION'
      )
    )
  );
  $data_json = json_encode($data);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_URL, 'https://vision.googleapis.com/v1/images:annotate?key='.$googlekey);
  $res1 = curl_exec($ch);
  $res2 = curl_getinfo($ch);
  curl_close($ch);
  $json = substr($res1, $res2["header_size"]);
  $json = json_decode($json);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://slack.com/api/chat.postMessage?token='.$slackkey.'&channel='.$origin->event->channel.'&text='.rawurlencode($json->responses[0]->textAnnotations[0]->description));
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $res1 = curl_exec($ch);
  $res2 = curl_getinfo($ch);
  curl_close($ch);

  file_put_contents('log.txt', file_get_contents('php://input'));
