<?php

namespace DelayNomore\ThinkMac\example;

use DelayNomore\ThinkMac\Parser;

/**
 * 示例类，展示如何使用Parser类
 */
class Example
{
    /**
     * 演示解析URL
     *
     * @return void
     */
    public static function demoParseUrl(): void
    {
        // 基本用法
        $result1 = Parser::parseMac('https://example.com/admin/user/view');
        echo "基本URL解析：\n";
        self::printResult($result1);

        // 自定义配置选项
        $options = [
            'default_module' => 'home',
            'default_controller' => 'index',
            'default_action' => 'index',
            'convert' => true,
            'bind_domains' => [
                'admin.example.com' => 'admin',
                'api.*' => 'api'
            ]
        ];
        $result2 = Parser::parseMac('https://api.example.com/user/list', $options);
        echo "\n带域名绑定的URL解析：\n";
        self::printResult($result2);

        // 解析多级控制器
        $result3 = Parser::parseMac('admin/user.profile/edit');
        echo "\n多级控制器解析：\n";
        self::printResult($result3);
    }

    /**
     * 演示解析域名
     *
     * @return void
     */
    public static function demoParseDomain(): void
    {
        // 域名绑定规则
        $rules = [
            'admin.example.com' => 'admin',
            'api.*' => 'api',
            '*.user' => 'user',
            '*' => 'www'
        ];

        // 完整域名匹配
        $result1 = Parser::parseDomain('admin.example.com', $rules);
        echo "完整域名匹配：\n";
        self::printDomainResult($result1);

        // 前缀泛域名匹配
        $result2 = Parser::parseDomain('api.v2.example.com', $rules);
        echo "\n前缀泛域名匹配：\n";
        self::printDomainResult($result2);

        // 后缀泛域名匹配
        $result3 = Parser::parseDomain('profile.user.example.com', $rules);
        echo "\n后缀泛域名匹配：\n";
        self::printDomainResult($result3);

        // 泛二级域名匹配
        $result4 = Parser::parseDomain('blog.example.com', $rules);
        echo "\n泛二级域名匹配：\n";
        self::printDomainResult($result4);
    }

    /**
     * 演示解析控制器
     *
     * @return void
     */
    public static function demoParseController(): void
    {
        // 基本控制器解析
        $result1 = Parser::parseController('user/view');
        echo "基本控制器解析：\n";
        self::printControllerResult($result1);

        // 多级控制器解析
        $result2 = Parser::parseController('admin.user.profile/edit');
        echo "\n多级控制器解析：\n";
        self::printControllerResult($result2);

        // 不进行名称转换
        $options = [
            'convert' => false,
            'default_controller' => 'index',
            'default_action' => 'index',
        ];
        $result3 = Parser::parseController('User/View', $options);
        echo "\n不转换名称的控制器解析：\n";
        self::printControllerResult($result3);
    }

    /**
     * 打印解析结果
     *
     * @param array $result 解析结果
     * @return void
     */
    private static function printResult(array $result): void
    {
        echo "模块: {$result['module']}\n";
        echo "控制器: {$result['ctrl']} (类名: {$result['class']})\n";
        echo "操作: {$result['action']} (方法名: {$result['method']})\n";
        echo "目录: " . ($result['dir'] ?: '无') . "\n";
        echo "完整路径: {$result['fullpath']}\n";
        echo "URL格式: {$result['url']}\n";
        echo "是否多级控制器: " . ($result['nested'] ? '是' : '否') . "\n";
        echo "控制器层级: {$result['depth']}\n";
    }

    /**
     * 打印域名解析结果
     *
     * @param array $result 解析结果
     * @return void
     */
    private static function printDomainResult(array $result): void
    {
        echo "域名: {$result['domain']}\n";
        echo "根域名: {$result['root']}\n";
        echo "子域名: {$result['sub']}\n";
        echo "模块: {$result['module']}\n";
        echo "匹配规则: " . implode(', ', array_keys($result['rules'])) . "\n";
    }

    /**
     * 打印控制器解析结果
     *
     * @param array $result 解析结果
     * @return void
     */
    private static function printControllerResult(array $result): void
    {
        echo "原始路径: {$result['raw']}\n";
        echo "目录: " . ($result['dir'] ?: '无') . "\n";
        echo "控制器路径: {$result['path']}\n";
        echo "控制器名: {$result['ctrl']} (类名: {$result['class']})\n";
        echo "操作名: {$result['action']} (方法名: {$result['method']})\n";
        echo "是否多级控制器: " . ($result['nested'] ? '是' : '否') . "\n";
        echo "控制器层级: {$result['depth']}\n";
    }
}
