<?php

// 确保错误会被显示
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 加载自动加载器
require __DIR__ . '/../vendor/autoload.php';

use DelayNomore\ThinkMac\Parser;

echo "### ThinkPHP MAC 解析器 - URL解析测试 ###\n\n";

// 定义各种绑定规则
$bindDomains = [
    'api.social-fast.local' => 'api',            // 完整域名绑定
    'admin' => 'admin',                          // 子域名绑定
    'blog' => 'blog',                            // 子域名绑定
    'admin.thinkphp.com.cn' => 'admin_system',   // 完整域名绑定
    'api.*' => 'apihub',                         // 前缀泛域名绑定
    '*.user' => 'user_center',                   // 泛三级域名绑定
    '*' => 'wildcard',                           // 泛二级域名绑定
    '192.168.1.1' => 'intranet',                 // IP绑定
    '127.0.0.1' => 'localhost'                   // IP绑定
];

// 测试用的URL集合
$urls = [
    // 基本URL测试
    "index/social/index",
    "index.php/social/index",
    "http://social-fast.local",
    "http://social-fast.local/",
    "/index/social/index.html?aaa=111&bbb=222#ccc",
    "/index.php/social/index.html?aaa=111&bbb=222#ccc",
    "http://social-fast.local/index/social/index.html?aaa=111&bbb=222#ccc",

    // 大小写测试
    "http://social-fast.local/index/BlogTest/read",
    "http://social-fast.local/index/blog_test/read_info",

    // 多级控制器测试
    "http://social-fast.local/index/one.blog/index",
    "http://social-fast.local/index/one.two.blog_test/read_info",

    // 子域名和完整域名绑定测试
    "http://admin.social-fast.local/index/social/index.html?aaa=111&bbb=222&ccc=333",
    "http://admin.social-fast.local/one.two.blog_test/read_info",
    "http://api.social-fast.local/social/index.html?aaa=111&bbb=222&ddd=444",
    "http://api.v2.social-fast.local/?aaa=111&bbb=222&fff=666",

    // 特殊域名测试
    "http://blog.thinkphp.com.cn/one.two_three.blog_test/read_info",
    "http://admin.thinkphp.com.cn/user/list",
    "http://hello.user.thinkphp.com.cn/index/test",

    // IP绑定测试
    "http://192.168.1.1/admin/index",
    "http://127.0.0.1/index/test"
];

echo "\n\n### 1. 测试Parser::parseMac方法 ###\n";
foreach ($urls as $url) {
    $result = Parser::parseMac($url, [
        'bind_domains' => $bindDomains,
        'domain_root' => 'thinkphp.com.cn'       // 指定根域名，用于特殊后缀
    ]);
    echo "原始URL: " . $url . "\n";
    echo "解析结果:\n";
    echo "- 模块: " . $result['module'] . "\n";
    echo "- 控制器子目录: " . $result['dir'] . "\n";
    echo "- 控制器(URL): " . $result['ctrl'] . "\n";
    echo "- 控制器类: " . $result['class'] . "\n";
    echo "- 操作(URL): " . $result['action'] . "\n";
    echo "- 操作方法: " . $result['method'] . "\n";
    echo "- 完整路径: " . $result['fullpath'] . "\n";
    echo "- URL格式: " . $result['url'] . "\n";
    if ($result['nested']) {
        echo "- 多级控制器: 是 (层级: " . $result['depth'] . ")\n";
    }
    echo "--------------------------------\n";
}

// 测试不转换大小写的情况
echo "\n### 测试不转换大小写模式 (convert=false) ###\n";
$testURLs = [
    "http://social-fast.local/index/BlogTest/read",
    "http://social-fast.local/index/blog_test/read_info",
    "http://social-fast.local/index/one.two_three.blog_test/read_info"
];

foreach ($testURLs as $url) {
    $result = Parser::parseMac($url, [
        'convert' => false,
        'bind_domains' => $bindDomains
    ]);
    echo "原始URL: " . $url . "\n";
    echo "- 模块: " . $result['module'] . "\n";
    echo "- 控制器(URL): " . $result['ctrl'] . "\n";
    echo "- 控制器类: " . $result['class'] . "\n";
    echo "- 操作(URL): " . $result['action'] . "\n";
    echo "- 操作方法: " . $result['method'] . "\n";
    echo "- 完整路径: " . $result['fullpath'] . "\n";
    echo "- URL格式: " . $result['url'] . "\n";
    echo "--------------------------------\n";
}
