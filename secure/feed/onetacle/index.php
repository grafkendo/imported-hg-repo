<?php
$url = domainUrl()."/secure/feed/spotlight/content.php?scode=".(isset($_GET["scode"])? $_GET["scode"]: "");
$content = @file_get_contents($url);
header('Content-Type: text/xml; charset=utf-8', true);
echo trim($content);
//get current domain
function domainUrl() {
  $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 
    $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  $domainName = $_SERVER['HTTP_HOST'];
  return $protocol.$domainName;
}