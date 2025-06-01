<?php

// 确保错误会被显示
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 加载自动加载器
require __DIR__ . '/../vendor/autoload.php';

use DelayNomore\ThinkMac\Parser;

echo "### ThinkPHP MAC 解析器 - 控制器解析测试 ###\n\n";

// 测试控制器URL集合
$testControllerURLs = [
    // 基础测试
    "index/blog/read",
    "index.php/blog/read",
    "/index/blog/read.html",

    // 大小写测试 (convert=true)
    "index/BlogTest/read",
    "index/blogtest/read",
    "index/blog_test/read",

    // 多级控制器测试
    "index/one.blog/index",
    "index/one.two.three.blog/index",
    "index/one.two_three.blog_test/read_info",

    // 完整URL测试
    "http://localhost/index.php/Index/BlogTest/read",
    "http://localhost/index.php/index/blog_test/read_info",
    "http://localhost/index.php/index/one.two_three.blog_test/read_info"
];

echo "#### 默认参数测试 (convert=true) ####\n";
foreach ($testControllerURLs as $url) {
    $result = Parser::parseController($url);
    echo "URL: " . $url . "\n";
    echo "- 原始路径: " . $result['raw'] . "\n";
    echo "- 控制器子目录: " . $result['dir'] . "\n";
    echo "- 完整路径: " . $result['path'] . "\n";
    echo "- 控制器(URL): " . $result['ctrl'] . "\n";
    echo "- 控制器类: " . $result['class'] . "\n";
    echo "- 操作(URL): " . $result['action'] . "\n";
    echo "- 操作方法: " . $result['method'] . "\n";
    echo "- 多级控制器: " . ($result['nested'] ? "是" : "否") . "\n";
    echo "- 控制器层级: " . $result['depth'] . "\n";
    echo "--------------------------------\n";
}

echo "\n#### 不转换大小写测试 (convert=false) ####\n";
foreach (["index/BlogTest/read", "index/blog_test/read", "index/one.two_three.blog_test/read_info"] as $url) {
    $result = Parser::parseController($url, ['convert' => false]);
    echo "URL: " . $url . "\n";
    echo "- 控制器(URL): " . $result['ctrl'] . "\n";
    echo "- 控制器类: " . $result['class'] . "\n";
    echo "- 操作(URL): " . $result['action'] . "\n";
    echo "- 操作方法: " . $result['method'] . "\n";
    echo "--------------------------------\n";
}

// 自定义控制器和操作名测试
echo "\n#### 自定义默认控制器和操作名测试 ####\n";
$customOptions = [
    'default_controller' => 'custom',
    'default_action' => 'default',
];

foreach (["", "index", "index/", "/index"] as $url) {
    $result = Parser::parseController($url, $customOptions);
    echo "URL: " . $url . "\n";
    echo "- 控制器(URL): " . $result['ctrl'] . "\n";
    echo "- 操作(URL): " . $result['action'] . "\n";
    echo "--------------------------------\n";
}
