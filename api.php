<?php
declare(strict_types=1);
// PHP 8.1+. Private state is created OUTSIDE the server document root.
ini_set('display_errors','0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
function respond(int $status, array $data): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); exit; }
function input(): array { if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0)>100000) respond(413,['error'=>'Request too large']); $raw=file_get_contents('php://input',false,null,0,100001); if(strlen($raw)>100000)respond(413,['error'=>'Request too large']); $data=json_decode($raw,true);if(!is_array($data))respond(400,['error'=>'JSON input required']);return $data; }
try {
 $documentRoot=realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) ?: __DIR__;
 $private=getenv('PROSPER_PRIVATE_DIR') ?: dirname($documentRoot).'/.prosper-private-'.substr(hash('sha256',__DIR__),0,12);
 if (!is_dir($private) && !mkdir($private,0700,true) && !is_dir($private)) throw new RuntimeException('Private directory unavailable');
 $real=realpath($private);if(!$real || $real===$documentRoot || str_starts_with($real,$documentRoot.DIRECTORY_SEPARATOR))throw new RuntimeException('Private directory must be outside document root');
 $credentials=$private.'/credentials.json'; $codeFile=$private.'/setup-code.txt'; $saved=$private.'/content.json';
 $lock=fopen($private.'/state.lock','c');if(!$lock || !flock($lock,LOCK_EX))throw new RuntimeException('Cannot lock storage');
 if(!is_file($credentials)&&!is_file($codeFile)){file_put_contents($codeFile,bin2hex(random_bytes(24)),LOCK_EX);chmod($codeFile,0600);}
 flock($lock,LOCK_UN);
 $secure=(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off');
 $sessionDir=$private.'/sessions';if(!is_dir($sessionDir)&&!mkdir($sessionDir,0700,true)&&!is_dir($sessionDir))throw new RuntimeException('Cannot create session storage');session_save_path($sessionDir);
 ini_set('session.use_strict_mode','1');ini_set('session.use_only_cookies','1');session_name('prosper_admin');
 session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Strict']);if(!session_start())throw new RuntimeException('Cannot start session');
 if(isset($_SESSION['expires'])&&$_SESSION['expires']<time()){$_SESSION=[];session_regenerate_id(true);}
 $route=$_GET['route']??'';$method=$_SERVER['REQUEST_METHOD'];$authenticated=isset($_SESSION['user'],$_SESSION['csrf']);
 if($route==='content'&&$method==='GET'){ $content=json_decode(file_get_contents(is_file($saved)?$saved:__DIR__.'/content.json'),true,512,JSON_THROW_ON_ERROR);respond(200,$content); }
 if($route==='status'&&$method==='GET')respond(200,['setup'=>!is_file($credentials),'authenticated'=>$authenticated,'csrf'=>$authenticated?$_SESSION['csrf']:null]);
 if(in_array($route,['login','setup'],true)&&$method==='POST'){
  if(!str_starts_with($_SERVER['CONTENT_TYPE']??'','application/json'))respond(415,['error'=>'JSON required']);
  if(($_SERVER['HTTP_SEC_FETCH_SITE']??'')==='cross-site')respond(403,['error'=>'Cross-site request rejected']);
  $data=input();$username=$data['username']??'';$password=$data['password']??'';
  if(!is_string($username)||!is_string($password)||strlen($password)>256)respond(400,['error'=>'Invalid sign-in details']);
  flock($lock,LOCK_EX);$rateFile=$private.'/attempts.json';$rates=is_file($rateFile)?json_decode(file_get_contents($rateFile),true):[];if(!is_array($rates))$rates=[];
  foreach($rates as $key=>$entry)if(($entry['until']??0)<time())unset($rates[$key]);
  $ip=hash('sha256',$_SERVER['REMOTE_ADDR']??'local');$rate=$rates[$ip]??['count'=>0,'until'=>time()+900];$rate['count']++;$rates[$ip]=$rate;file_put_contents($rateFile,json_encode($rates));chmod($rateFile,0600);
  if($rate['count']>10){flock($lock,LOCK_UN);respond(429,['error'=>'Too many attempts. Try again in 15 minutes.']);}
  if($route==='setup'){
   if(is_file($credentials)){flock($lock,LOCK_UN);respond(409,['error'=>'Account already configured']);}
   if(!is_string($data['code']??null)||!hash_equals(trim(file_get_contents($codeFile)),trim($data['code']))){flock($lock,LOCK_UN);respond(403,['error'=>'Invalid setup code']);}
   if(!preg_match('/^[A-Za-z0-9_.-]{3,40}$/D',$username)||strlen($password)<12){flock($lock,LOCK_UN);respond(400,['error'=>'Use a 3–40 character username and a password of at least 12 characters.']);}
   file_put_contents($credentials,json_encode(['username'=>$username,'hash'=>password_hash($password,PASSWORD_DEFAULT)]),LOCK_EX);chmod($credentials,0600);unlink($codeFile);
  }else{
   if(!is_file($credentials)){flock($lock,LOCK_UN);respond(409,['error'=>'Complete first-time setup first']);}
   $account=json_decode(file_get_contents($credentials),true);
   if(!hash_equals($account['username'],$username)||!password_verify($password,$account['hash'])){flock($lock,LOCK_UN);respond(401,['error'=>'Invalid username or password']);}
  }
  unset($rates[$ip]);file_put_contents($rateFile,json_encode($rates));flock($lock,LOCK_UN);session_regenerate_id(true);$_SESSION=['user'=>$username,'csrf'=>bin2hex(random_bytes(32)),'expires'=>time()+3600];respond(200,['ok'=>true,'csrf'=>$_SESSION['csrf']]);
 }
 if($route==='content'&&$method==='PUT'){
  if(!$authenticated)respond(401,['error'=>'Please sign in again']);
  if(!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??''))respond(403,['error'=>'Invalid security token']);
  $data=input();$output=[];foreach(['name','title','summary','phone','address']as$key){if(!isset($data[$key])||!is_string($data[$key])||!trim($data[$key])||strlen($data[$key])>3000)respond(400,['error'=>'Invalid '.$key]);$output[$key]=trim($data[$key]);}
  if(!isset($data['projects'])||!is_array($data['projects'])||count($data['projects'])!==7)respond(400,['error'=>'Seven project entries are required']);$output['projects']=[];
  foreach($data['projects']as$project){$p=[];foreach(['title','category','type','description','challenge','approach']as$key){if(!isset($project[$key])||!is_string($project[$key])||strlen($project[$key])>5000)respond(400,['error'=>'Invalid project entry']);$p[$key]=$project[$key];}$output['projects'][]=$p;}
  flock($lock,LOCK_EX);$temp=$private.'/content.tmp';if(file_put_contents($temp,json_encode($output,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))===false)throw new RuntimeException('Cannot save content');chmod($temp,0600);if(!rename($temp,$saved))throw new RuntimeException('Cannot save content');flock($lock,LOCK_UN);respond(200,['ok'=>true]);
 }
 if($route==='logout'&&$method==='POST'){if(!$authenticated||!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??''))respond(403,['error'=>'Invalid security token']);$_SESSION=[];session_destroy();setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Strict']);respond(200,['ok'=>true]);}
 respond(404,['error'=>'Not found']);
} catch(Throwable $error){error_log('Prosper dashboard: '.$error->getMessage());respond(500,['error'=>'Server storage or PHP configuration needs attention. See README.md and your hosting error log.']);}
