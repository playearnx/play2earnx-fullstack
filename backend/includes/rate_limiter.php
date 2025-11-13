<?php
function rate_limit_check($key, $max=3, $window=60){
  $file = sys_get_temp_dir() . '/rate_' . md5($key);
  $data = ['time'=>0,'hits'=>0];
  if(file_exists($file)) $data = json_decode(file_get_contents($file), true) ?: $data;
  $now = time();
  if($now - $data['time'] > $window) $data = ['time'=>$now,'hits'=>1'];
  else $data['hits'] += 1;
  file_put_contents($file, json_encode($data));
  return $data['hits'] <= $max;
}
