<?php

// 加载自动加载器
require __DIR__ . '/../vendor/autoload.php';

use DelayNomore\ThinkMac\example\Example;

echo "=================================================\n";
echo "ThinkPHP MAC 解析器演示\n";
echo "=================================================\n\n";

// 演示URL解析
echo "## 1. URL 解析演示 ##\n";
echo "=================================================\n";
Example::demoParseUrl();

echo "\n\n";
echo "## 2. 域名解析演示 ##\n";
echo "=================================================\n";
Example::demoParseDomain();

echo "\n\n";
echo "## 3. 控制器解析演示 ##\n";
echo "=================================================\n";
Example::demoParseController();

// 也可以直接使用 Parser 类
echo "\n\n";
echo "## 4. 直接使用 Parser 类的示例 ##\n";
echo "=================================================\n";

use DelayNomore\ThinkMac\Parser;

// 基本URL解析
$url = 'http://admin.example.com/admin/user/view?id=1';
$result = Parser::parseMac($url, [
    'bind_domains' => [
        'admin.example.com' => 'admin',
        'api.*' => 'api'
    ]
]);

echo "解析URL: $url\n";
echo "解析结果:\n";
echo "- 模块: {$result['module']}\n";
echo "- 控制器: {$result['ctrl']} (类名: {$result['class']})\n";
echo "- 操作: {$result['action']} (方法名: {$result['method']})\n";
echo "- 完整路径: {$result['fullpath']}\n";
echo "- URL格式: {$result['url']}\n";
echo "- 是否多级控制器: " . ($result['nested'] ? '是' : '否') . "\n";
