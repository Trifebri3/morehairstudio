<?php
$ch = curl_init('http://127.0.0.1:8000/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP code: $code\n";
echo "Length: " . strlen($html) . "\n";
file_put_contents(__DIR__ . '/curl_dump.html', $html);
