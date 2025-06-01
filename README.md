# ThinkPHP MAC Parser

[![PHP Tests](https://github.com/delay-no-more/thinkphp-mac-parser/actions/workflows/php-tests.yml/badge.svg)](https://github.com/delay-no-more/thinkphp-mac-parser/actions/workflows/php-tests.yml)

ThinkPHP MAC (Module-Action-Controller) 解析器，提供增强的URL解析功能，支持模块、控制器、方法的解析。

## 功能特点

- 解析URL获取模块、控制器、方法
- 支持域名绑定模块（支持多种匹配模式）
- 支持多级控制器解析
- 灵活的命名转换规则
- 支持特殊域名后缀处理

## 安装

通过 Composer 安装：

```bash
composer require delaynomore/thinkphp-mac-parser
```

## 快速开始

### 基本用法

```php
<?php

use DelayNomore\ThinkMac\Parser;

// 解析完整URL
$result = Parser::parseMac('https://example.com/admin/user/view');

// 输出结果
var_dump($result);
```

### 自定义配置选项

```php
<?php

use DelayNomore\ThinkMac\Parser;

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

$result = Parser::parseMac('https://api.example.com/user/list', $options);
```

## 主要方法

### parseMac(string $url, array $options = []): array

解析完整URL获取模块、控制器、方法。

**参数**:
- `$url`: URL地址
- `$options`: 选项数组
  - `bind_module`: 绑定模块，设置后bind_files和bind_domains无效
  - `bind_domains`: 绑定域名，格式为 ['domain' => 'module']，优先级高于bind_files
  - `bind_files`: 绑定文件，格式为 ['fileName' => 'module']，优先级低于bind_domains
  - `default_module`: 默认模块
  - `default_controller`: 默认控制器
  - `default_action`: 默认操作方法
  - `domain_root`: 根域名，用于处理特殊后缀，例如 'thinkphp.com.cn'
  - `convert`: 是否开启URL中控制器和操作名的自动转换

### parseDomain(string $url, array $rules = [], ?string $domainRoot = null): array

解析域名并提取模块名

### parseController(string $url, array $options = []): array

解析控制器路径和操作方法

## 返回结果说明

`parseMac()` 方法返回包含以下键的数组：

- `module`: 模块名
- `controller`: 控制器名称（兼容旧版，等同于ctrl）
- `action`: 操作名（兼容旧版，等同于action）
- `ctrl`: 控制器名称，小写+下划线格式
- `class`: 控制器类名，大驼峰格式
- `method`: 操作方法名，小驼峰格式
- `dir`: 控制器子目录，多级控制器的目录部分，小写+下划线格式
- `path`: 控制器路径（不含模块），小写+下划线格式
- `fullpath`: 完整路径，包含模块/控制器/操作，如"module/dir/controller/action"
- `url`: URL格式路径，如"module/controller/action"或"module/dir.controller/action"
- `nested`: 是否为多级控制器
- `depth`: 控制器层级数量

## 示例

可以查看 `examples` 目录下的示例文件，了解更多使用方法：

- `demo.php`: 基本演示
- `url_parse_example.php`: URL解析示例
- `controller_parse_example.php`: 控制器解析示例
- `domain_parse_example.php`: 域名解析示例

也可以直接使用内置的示例类：

```php
use DelayNomore\ThinkMac\example\Example;

Example::demoParseUrl();
Example::demoParseDomain();
Example::demoParseController();
```

## 许可证

MIT
