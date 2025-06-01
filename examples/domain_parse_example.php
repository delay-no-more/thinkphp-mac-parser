<?php

// 确保错误会被显示
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 加载自动加载器
require __DIR__ . '/../vendor/autoload.php';

use DelayNomore\ThinkMac\Parser;

echo "### ThinkPHP MAC 解析器 - 域名解析测试 ###\n\n";

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

// 测试域名集合
$testDomains = [
    // 基本域名测试
    "social-fast.local",
    "admin.social-fast.local",
    "api.social-fast.local",
    "api.v2.social-fast.local",

    // 特殊域名测试
    "blog.thinkphp.com.cn",
    "admin.thinkphp.com.cn",
    "hello.user.thinkphp.com.cn",
    "other.subdomain.thinkphp.com.cn",

    // IP绑定测试
    "192.168.1.1",
    "127.0.0.1",

    // URL测试
    "http://api.v3.social-fast.local/some/path?query=value"
];

echo "### 测试Parser::parseDomain方法 ###\n";
foreach ($testDomains as $domain) {
    $result = Parser::parseDomain($domain, $bindDomains, "thinkphp.com.cn");
    echo "域名/URL: " . $domain . "\n";
    echo "- 模块: " . $result['module'] . "\n";
    echo "- 域名: " . $result['domain'] . "\n";
    echo "- 根域名: " . $result['root'] . "\n";
    echo "- 子域名: " . $result['sub'] . "\n";
    echo "- 匹配规则: " . (!empty($result['rules']) ? key($result['rules']) . " => " . reset($result['rules']) : "无匹配") . "\n";
    echo "--------------------------------\n";
}

// 测试不同类型的泛域名匹配
echo "\n### 泛域名匹配详细测试 ###\n";

// 多级子域名测试
$subdomainTests = [
    "one.social-fast.local",
    "one.two.social-fast.local",
    "one.two.three.social-fast.local"
];

// 添加更多泛域名规则
$wildcardRules = $bindDomains + [
    '*.*.social-fast.local' => 'deep_sub',   // 二级以上泛域名
    'admin.*.social-fast.local' => 'admin_wild'  // 混合泛域名
];

foreach ($subdomainTests as $domain) {
    $result = Parser::parseDomain($domain, $wildcardRules);
    echo "域名: " . $domain . "\n";
    echo "- 模块: " . $result['module'] . "\n";
    echo "- 子域名: " . $result['sub'] . "\n";
    echo "- 匹配规则: " . (!empty($result['rules']) ? key($result['rules']) . " => " . reset($result['rules']) : "无匹配") . "\n";
    echo "--------------------------------\n";
}
