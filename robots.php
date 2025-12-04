<?php
header('Content-Type: text/plain; charset=utf-8');

// 현재 도메인 및 경로 자동 감지
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['PHP_SELF']);

// 윈도우 경로 구분자(\)를 웹 표준(/)으로 변환 및 끝 슬래시 정리
$path = str_replace('\\', '/', $path);
$path = rtrim($path, '/');

$base_url = "{$protocol}://{$domain}{$path}";

// robots.txt 내용 출력
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /plugin/\n";
echo "Disallow: /skin/\n";
echo "Disallow: /start/\n";
echo "Disallow: /inc/\n";
echo "Disallow: /lib/\n";
echo "Disallow: /data/\n";
echo "\n";
// sitemap.xml로 연결 (RewriteRule에 의해 sitemap.php가 실행됨)
echo "Sitemap: {$base_url}/sitemap.xml";
?>
