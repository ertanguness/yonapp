<?php
function req($method,$url,$data=null,$headers=[]){
  $opts = ['http'=>['method'=>$method,'ignore_errors'=>true,'header'=>[]]];
  foreach($headers as $k=>$v){ $opts['http']['header'][] = $k.': '.$v; }
  if($data){
    if(is_array($data)){ $data = http_build_query($data); $opts['http']['header'][] = 'Content-Type: application/x-www-form-urlencoded'; }
    $opts['http']['content'] = $data;
  }
  $opts['http']['header'] = implode("\r\n", $opts['http']['header']);
  $ctx = stream_context_create($opts);
  $res = file_get_contents($url,false,$ctx);
  return json_decode($res,true);
}
function b64e($d){ return rtrim(strtr(base64_encode($d), '+/', '-_'), '='); }
function makeJwt($sub){ $h = b64e(json_encode(['alg'=>'HS256','typ'=>'JWT'])); $p = b64e(json_encode(['sub'=>$sub,'exp'=>time()+3600])); $s = hash_hmac('sha256', $h.'.'.$p, getenv('JWT_SECRET') ?: 'devsecret', true); return $h.'.'.$p.'.'.b64e($s); }
$base = 'http://localhost:8000/pages/duyuru-talep/admin/api/APIDuyuru.php';
$out = '';
$list = req('GET', $base.'?datatables=1&draw=1&start=0&length=5');
$out .= "GET list ok: ".(isset($list['data'])? 'yes':'no')."\n";
$jwt = makeJwt('test');
$hdr = ['Authorization'=>'Bearer '.$jwt];
$create = req('POST', $base, [
  'title'=>'Test Duyuru',
  'content'=>'İçerik',
  'start_date'=>date('Y-m-d'),
  'end_date'=>date('Y-m-d', strtotime('+1 day')),
  'status'=>'draft'
], $hdr);
$out .= "POST create status: ".$create['status']." id=".($create['id']??'')."\n";
$id = $create['id'] ?? 0;
if($id){
  $single = req('GET', $base.'?id='.$id, null, $hdr);
  $out .= "GET single status: ".$single['status']." title=".($single['data']['baslik']??'')."\n";
  $upd = req('PUT', $base, [ 'id'=>$id, 'status'=>'published' ], $hdr);
  $out .= "PUT update status: ".$upd['status']."\n";
  $del = req('DELETE', $base, [ 'id'=>$id ], $hdr);
  $out .= "DELETE status: ".$del['status']."\n";
}
file_put_contents(__DIR__.'/announcements-api.test.out.txt', $out);