<?php

namespace DelayNomore\ThinkMac;

/**
 * Parser 类 - 提供URL解析功能，支持模块、控制器、方法的解析
 *
 * 此类提供了三个主要的静态方法：
 * - parseMac(): 解析完整URL获取模块、控制器、方法
 * - parseDomain(): 解析域名并提取模块名
 * - parseController(): 解析控制器路径和操作方法
 */
class Parser
{
    /**
     * 解析URL并提取模块、控制器、方法。
     *
     * @param string $url URL地址
     * @param array $options 选项
     *      - bind_module(string): 绑定模块, 设置后bind_files和bind_domains无效
     *      - bind_domains(array): 绑定域名,格式为 ['domain' => 'module'], 优先级高于bind_files
     *      - bind_files(array): 绑定文件,格式为 ['fileName' => 'module'], 优先级低于bind_domains
     *      - default_module(string): 默认模块
     *      - default_controller(string): 默认控制器
     *      - default_action(string): 默认操作方法
     *      - domain_root(string): 根域名,用于处理特殊后缀,例如 'thinkphp.com.cn'
     *      - convert(bool): 是否开启URL中控制器和操作名的自动转换
     * @return array 包含解析结果的数组
     *      - module(string): 模块名
     *      - controller(string): 控制器名称（兼容旧版，等同于ctrl）
     *      - action(string): 操作名（兼容旧版，等同于action）
     *      - ctrl(string): 控制器名称，小写+下划线格式
     *      - class(string): 控制器类名，大驼峰格式
     *      - method(string): 操作方法名，小驼峰格式
     *      - dir(string): 控制器子目录，多级控制器的目录部分，小写+下划线格式
     *      - path(string): 控制器路径（不含模块），小写+下划线格式
     *      - fullpath(string): 完整路径，包含模块/控制器/操作，如"module/dir/controller/action"
     *      - url(string): URL格式路径，如"module/controller/action"或"module/dir.controller/action"
     *      - nested(bool): 是否为多级控制器
     *      - depth(int): 控制器层级数量
     */
    public static function parseMac(string $url, array $options = []): array
    {
        $options = array_merge([
            'bind_module' => null,
            'bind_files' => [],
            'bind_domains' => [],
            'default_module' => 'index',
            'default_controller' => 'index',
            'default_action' => 'index',
            'domain_root' => null,
            'convert' => true,
        ], $options);

        // 解析URL获取基本组件
        $parseResult = self::parseUrl($url);
        $urlPath = $parseResult['path'] ?? '';
        $domain = $parseResult['host'] ?? '';

        // 对URL路径进行处理
        $decodedPath = self::processPath($urlPath);
        $parts = explode('/', trim($decodedPath, '/'));

        // 初始化变量
        $module = null;
        $fromPath0 = false; // 标记模块名是否从$parts[0]解析出来

        // 1. 如果bind_module不为空, 则直接使用bind_module的值作为模块名
        if (!empty($options['bind_module'])) {
            $module = $options['bind_module'];
        }
        // 2. 如果bind_module为空, 则使用bind_domains解析模块名
        elseif (!empty($domain) && !empty($options['bind_domains'])) {
            // 使用增强的域名解析功能
            $domainResult = self::parseDomain($domain, $options['bind_domains'], $options['domain_root']);
            if (!empty($domainResult['module'])) {
                $module = $domainResult['module'];
                $fromPath0 = true;
            }
        }

        // 3. 如果bind_domains为空或未匹配, 则使用bind_files解析模块名
        if (is_null($module) && !empty($parts) && !empty($options['bind_files'])) {
            if (isset($options['bind_files'][$parts[0]])) {
                $module = $options['bind_files'][$parts[0]];
                $fromPath0 = true;
            }
        }

        // 4. 检测$parts[0]是否是php文件
        if (is_null($module) && !empty($parts)) {
            if (preg_match('/^(.+)\.php$/i', $parts[0], $matches)) {
                $module = $matches[1];
                $fromPath0 = true;
            } elseif (!empty($parts[0])) {
                $module = $parts[0];
                $fromPath0 = true;
            }
        }

        // 5. 如果上述都没有解析出模块名, 则使用default_module
        if (is_null($module)) {
            $module = $options['default_module'];
        }

        // 构建控制器URL部分
        $controllerUrl = $url;
        if ($fromPath0 && !empty($parts)) {
            // 如果模块名是从URL路径中解析出来的，需要移除URL路径中的模块部分
            array_shift($parts);

            // 重新构建URL路径
            $newPath = implode('/', $parts);
            if (!empty($newPath)) {
                if (isset($parseResult['scheme'])) {
                    // 如果是完整URL，重建URL
                    $controllerUrl = self::rebuildUrl($parseResult, $newPath);
                } else {
                    // 如果是相对URL，直接使用路径
                    $controllerUrl = $newPath;
                }
            } else {
                // 如果没有剩余部分，使用默认控制器和动作
                $controllerUrl = $options['default_controller'] . '/' . $options['default_action'];
            }
        }

        // 使用parseController解析控制器和方法
        $controllerResult = self::parseController($controllerUrl, [
            'convert' => $options['convert'],
            'default_controller' => $options['default_controller'],
            'default_action' => $options['default_action'],
        ]);

        // 构建包含模块的完整路径
        $fullpath = $module . '/' .
                   ($controllerResult['dir'] ? $controllerResult['dir'] . '/' : '') .
                   $controllerResult['ctrl'] . '/' .
                   $controllerResult['action'];

        // 构建URL格式路径（使用点号表示多级控制器）
        $urlFormat = $module . '/';
        if ($controllerResult['nested']) {
            // 如果是多级控制器，使用点号连接
            $dotPath = str_replace('/', '.', $controllerResult['dir']) .
                       ($controllerResult['dir'] ? '.' : '') .
                       $controllerResult['ctrl'];
            $urlFormat .= $dotPath . '/' . $controllerResult['action'];
        } else {
            $urlFormat .= $controllerResult['ctrl'] . '/' . $controllerResult['action'];
        }

        // 合并模块和控制器解析结果
        return array_merge(
            ['module' => $module],
            $controllerResult,
            [
                // 兼容旧版返回格式
                'controller' => $controllerResult['ctrl'],
                'action' => $controllerResult['action'],
                // 新增包含模块的完整路径
                'fullpath' => $fullpath,
                // URL格式路径
                'url' => $urlFormat
            ]
        );
    }

    /**
     * 解析域名并提取模块名
     *
     * @param string $url URL地址或域名
     * @param array $rules 域名绑定规则
     *      格式为 ['domain' => 'module']
     *      支持以下几种匹配方式:
     *      1. 子域名绑定: ['blog' => 'blog']
     *         匹配: blog.domain.com -> blog模块
     *      2. 完整域名绑定: ['admin.thinkphp.cn' => 'admin']
     *         匹配: admin.thinkphp.cn -> admin模块
     *      3. IP绑定: ['114.23.4.5' => 'admin']
     *         匹配: 114.23.4.5 -> admin模块
     *      4. 泛二级域名绑定: ['*' => 'book']
     *         匹配: hello.thinkphp.cn -> book模块
     *              quickstart.thinkphp.cn -> book模块
     *      5. 泛三级域名绑定: ['*.user' => 'user']
     *         匹配: hello.user.thinkphp.cn -> user模块
     *      6. 前缀泛域名绑定: ['api.*' => 'apihub']
     *         匹配: api.example.com、api.v2.example.com -> apihub模块
     * @param string|null $domainRoot 根域名
     *      可以通过 domain_root 配置指定根域名,用于处理特殊后缀
     *      例如: 'thinkphp.com.cn'
     *
     * @return array 包含解析结果的数组
     *      - module: 匹配到的模块名
     *      - domain: 当前域名
     *      - root: 根域名
     *      - sub: 匹配的子域名, 例如 api.v2.domain.com 返回 api.v2
     *      - rules: 匹配的规则, 例如 ['api.*', 'api']
     */
    public static function parseDomain(string $url, array $rules = [], ?string $domainRoot = null): array
    {
        // 解析URL获取域名
        $domain = self::extractDomain($url);

        if (empty($domain)) {
            return [
                'module' => '',
                'domain' => '',
                'root'   => '',
                'sub'    => '',
                'rules'  => [],
            ];
        }

        // 提取根域名
        $rootDomain = self::extractRootDomain($domain, $domainRoot);

        // 获取子域名部分
        $subDomain = '';
        if ($rootDomain && $rootDomain !== $domain) {
            $subDomain = rtrim(substr($domain, 0, -strlen($rootDomain) - 1), '.');
        }

        // 初始化匹配结果
        $module = '';
        $matchedRule = '';
        $subDomainParts = $subDomain ? explode('.', $subDomain) : [];

        // 优先级1: 完整域名匹配
        // ---------------------------------------
        if (isset($rules[$domain])) {
            $module = $rules[$domain];
            $matchedRule = $domain;
            return [
                'module' => $module,
                'domain' => $domain,
                'root'   => $rootDomain,
                'sub'    => $subDomain,
                'rules'  => [$matchedRule => $module],
            ];
        }

        // IP地址匹配也视为完整域名匹配
        if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $domain) && isset($rules[$domain])) {
            $module = $rules[$domain];
            $matchedRule = $domain;
            return [
                'module' => $module,
                'domain' => $domain,
                'root'   => $rootDomain,
                'sub'    => $subDomain,
                'rules'  => [$matchedRule => $module],
            ];
        }

        // 优先级2: 子域名匹配（按长度排序，优先匹配长的子域名）
        // ---------------------------------------
        if ($subDomain) {
            $matchResult = self::matchSubdomain($subDomain, $rules);
            if (!empty($matchResult)) {
                return array_merge($matchResult, [
                    'domain' => $domain,
                    'root'   => $rootDomain,
                    'sub'    => $subDomain,
                ]);
            }

            // 优先级3: 泛域名匹配（根据特定性排序，优先匹配更具体的规则）
            // ---------------------------------------
            $wildcardResult = self::matchWildcardDomain($subDomain, $rules);
            if (!empty($wildcardResult)) {
                return array_merge($wildcardResult, [
                    'domain' => $domain,
                    'root'   => $rootDomain,
                    'sub'    => $subDomain,
                ]);
            }
        }

        // 没有匹配到任何规则
        return [
            'module' => $module,
            'domain' => $domain,
            'root'   => $rootDomain,
            'sub'    => $subDomain,
            'rules'  => $matchedRule ? [$matchedRule => $module] : [],
        ];
    }

    /**
     * 解析控制器路径和操作方法
     *
     * 此函数提供增强的控制器解析能力，支持多级控制器和灵活的命名转换规则。
     *
     * @param string $url URL地址或控制器路径
     * @param array $options 选项
     *      - convert(bool): 是否开启URL中控制器和操作名的自动转换
     *                       true: 控制器名和操作名不区分大小写，下划线分隔的名称会转换为驼峰格式
     *                       false: 控制器名大小写敏感，目录名仍保持小写
     *      - default_controller(string): 默认控制器名称
     *      - default_action(string): 默认操作方法名称
     *
     * @return array 包含解析结果的数组
     *      - raw(string): 原始字符串，URL的原始path，不带参数和后缀扩展名
     *      - dir(string): 控制器子目录，多级控制器的目录部分，小写+下划线格式
     *      - path(string): 控制器路径，包含控制器名的完整path，小写+下划线格式
     *      - action(string): 操作名，小写+下划线格式
     *      - ctrl(string): 控制器名称，小写+下划线格式
     *      - method(string): 操作方法名，小驼峰格式
     *      - class(string): 控制器类名，大驼峰格式
     *      - nested(bool): 是否为多级控制器
     *      - depth(int): 控制器层级数量
     */
    public static function parseController(string $url, array $options = []): array
    {
        $options = array_merge([
            'convert' => true,              // 是否开启URL中控制器和操作名的自动转换
            'default_controller' => 'index', // 默认控制器
            'default_action' => 'index',     // 默认操作
        ], $options);

        // 解析URL获取path部分
        $parseResult = self::parseUrl($url);
        $urlPath = $parseResult['path'] ?? '';

        // 处理URL路径
        $decodedPath = self::processPath($urlPath);
        $raw = trim($decodedPath, '/');

        // 将路径按"/"分割
        $parts = explode('/', $raw);

        // 初始化变量
        $controllerPart = '';
        $actionPart = '';

        // 确定控制器和操作部分
        if (count($parts) > 1) {
            $controllerPart = $parts[count($parts) - 2] ?? '';
            $actionPart = $parts[count($parts) - 1] ?? '';
        } elseif (count($parts) == 1) {
            $controllerPart = $parts[0];
            $actionPart = '';
        }

        // 如果未找到操作名，使用默认值
        if (empty($actionPart)) {
            $actionPart = $options['default_action'];
        }

        // 如果未找到控制器，使用默认值
        if (empty($controllerPart)) {
            $controllerPart = $options['default_controller'];
        }

        // 处理多级控制器（包含点号分隔的路径）
        $controllerParts = explode('.', $controllerPart);
        $isMultiLevel = count($controllerParts) > 1;
        $levelCount = count($controllerParts);

        // 最后一部分是控制器名，前面的都是目录
        $controllerName = end($controllerParts);

        // 目录部分，始终小写
        $dirParts = array_slice($controllerParts, 0, -1);
        $dirPath = implode('/', array_map('strtolower', $dirParts));

        // 根据convert选项处理名称转换
        $result = self::convertNames($controllerName, $actionPart, $options['convert']);

        // 确保操作名的格式正确
        $actionSnake = $options['convert'] ? strtolower($actionPart) : $actionPart;

        // 构建完整控制器路径（目录+控制器名）
        $fullPath = !empty($dirPath) ? $dirPath . '/' . $result['controllerSnake'] : $result['controllerSnake'];

        return [
            'raw'    => $raw,
            'dir'    => $dirPath,
            'path'   => $fullPath,
            'action' => $actionSnake,
            'ctrl'   => $result['controllerSnake'],
            'method' => $result['actionCamel'],
            'class'  => $result['controllerClass'],
            'nested' => $isMultiLevel,
            'depth'  => $levelCount,
        ];
    }

    /**
     * 解析URL获取组件部分
     *
     * @param string $url URL地址
     * @return array 解析后的URL组件
     */
    private static function parseUrl(string $url): array
    {
        if (preg_match('/^(http|https):\/\//i', $url)) {
            return parse_url($url) ?: [];
        } else {
            return ['path' => $url];
        }
    }

    /**
     * 处理URL路径，移除扩展名并解码
     *
     * @param string $path URL路径
     * @return string 处理后的路径
     */
    private static function processPath(string $path): string
    {
        return preg_replace('/\.[^\/\.]+$/i', '', urldecode($path));
    }

    /**
     * 从URL中提取域名
     *
     * @param string $url URL地址或域名
     * @return string 提取的域名，如果没有则返回空字符串
     */
    private static function extractDomain(string $url): string
    {
        if (preg_match('/^(http|https):\/\//i', $url)) {
            $parseURL = parse_url($url);
            return $parseURL['host'] ?? '';
        } else {
            // 如果输入的是域名而不是URL
            return preg_replace('/:\d+$/', '', $url); // 移除端口号
        }
    }

    /**
     * 提取根域名
     *
     * @param string $domain 域名
     * @param string|null $domainRoot 指定的根域名
     * @return string 根域名
     */
    private static function extractRootDomain(string $domain, ?string $domainRoot = null): string
    {
        if ($domainRoot) {
            return $domainRoot;
        }

        // 自动提取根域名（排除IP地址）
        if (!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $domain)) {
            $domainParts = explode('.', $domain);
            $count = count($domainParts);

            // 处理特殊后缀如com.cn、net.cn等
            if ($count > 2) {
                $lastPart = $domainParts[$count - 1];
                $secondLastPart = $domainParts[$count - 2];

                if (in_array($lastPart, ['cn', 'uk']) && in_array($secondLastPart, ['com', 'net', 'org', 'gov', 'edu'])) {
                    // 如果是 .com.cn, .net.cn 等形式
                    if ($count > 3) {
                        return $domainParts[$count - 3] . '.' . $secondLastPart . '.' . $lastPart;
                    } else {
                        return $domain;
                    }
                } else {
                    // 普通域名形式
                    if ($count > 2) {
                        return $domainParts[$count - 2] . '.' . $domainParts[$count - 1];
                    } else {
                        return $domain;
                    }
                }
            } else {
                return $domain;
            }
        } else {
            // 如果是IP地址，根域名就是IP地址本身
            return $domain;
        }
    }

    /**
     * 匹配子域名
     *
     * @param string $subDomain 子域名
     * @param array $rules 域名绑定规则
     * @return array|null 匹配结果，如果没有匹配则返回null
     */
    private static function matchSubdomain(string $subDomain, array $rules): ?array
    {
        // 构建所有可能的子域名组合，从最长到最短
        $possibleSubdomains = [];
        $parts = explode('.', $subDomain);
        $totalParts = count($parts);

        // 完整子域名
        $possibleSubdomains[] = $subDomain;

        // 处理各种长度的子域名组合
        if ($totalParts > 1) {
            // 从最长到最短生成子域名组合
            for ($i = 1; $i < $totalParts; $i++) {
                $possibleSubdomains[] = implode('.', array_slice($parts, $i));
            }
        }

        // 尝试匹配，从最长的子域名开始
        foreach ($possibleSubdomains as $subdomain) {
            if (isset($rules[$subdomain])) {
                $module = $rules[$subdomain];
                $matchedRule = $subdomain;
                return [
                    'module' => $module,
                    'rules'  => [$matchedRule => $module],
                ];
            }
        }

        return null;
    }

    /**
     * 匹配泛域名
     *
     * @param string $subDomain 子域名
     * @param array $rules 域名绑定规则
     * @return array|null 匹配结果，如果没有匹配则返回null
     */
    private static function matchWildcardDomain(string $subDomain, array $rules): ?array
    {
        // 收集所有可能的泛域名匹配
        $wildcardMatches = [];

        // 检查每个规则
        foreach ($rules as $rulePattern => $ruleModule) {
            // 跳过非泛域名规则
            if (strpos($rulePattern, '*') === false) {
                continue;
            }

            // 泛三级域名匹配 (*.user)
            if (strpos($rulePattern, '*.') === 0) {
                $suffix = substr($rulePattern, 2); // 去掉 '*.'
                // 检查子域名是否以这个后缀结尾
                if (substr($subDomain, -strlen($suffix)) === $suffix &&
                    (strlen($subDomain) === strlen($suffix) ||
                     substr($subDomain, -strlen($suffix) - 1, 1) === '.')) {
                    // 计算特定性 - 越长越具体
                    $specificity = strlen($suffix) + 1; // +1 for *
                    $wildcardMatches[$rulePattern] = [
                        'module' => $ruleModule,
                        'specificity' => $specificity
                    ];
                }
            }
            // 前缀泛域名匹配 (api.*)
            elseif (strpos($rulePattern, '.*') !== false && strpos($rulePattern, '.*') === strlen($rulePattern) - 2) {
                $prefix = substr($rulePattern, 0, -2); // 去掉 '.*'
                // 检查子域名是否以这个前缀开头
                if (strpos($subDomain, $prefix) === 0 &&
                    (strlen($subDomain) === strlen($prefix) ||
                     substr($subDomain, strlen($prefix), 1) === '.')) {
                    // 计算特定性 - 越长越具体
                    $specificity = strlen($prefix) + 1; // +1 for *
                    $wildcardMatches[$rulePattern] = [
                        'module' => $ruleModule,
                        'specificity' => $specificity
                    ];
                }
            }
            // 泛二级域名匹配 (*)
            elseif ($rulePattern === '*') {
                $wildcardMatches[$rulePattern] = [
                    'module' => $ruleModule,
                    'specificity' => 0 // 最低特定性
                ];
            }
        }

        // 按特定性排序
        if (!empty($wildcardMatches)) {
            uasort($wildcardMatches, function($a, $b) {
                return $b['specificity'] - $a['specificity'];
            });

            // 获取最匹配的规则
            $topRulePattern = key($wildcardMatches);
            $topRule = $wildcardMatches[$topRulePattern];

            $module = $topRule['module'];
            $matchedRule = $topRulePattern;

            return [
                'module' => $module,
                'rules'  => [$matchedRule => $module],
            ];
        }

        return null;
    }

    /**
     * 根据转换选项处理控制器名和方法名
     *
     * @param string $controllerName 控制器名
     * @param string $actionName 方法名
     * @param bool $convert 是否转换
     * @return array 转换后的名称
     */
    private static function convertNames(string $controllerName, string $actionName, bool $convert): array
    {
        if ($convert) {
            // 转换为下划线格式的控制器名（用于URL）
            $controllerSnake = strtolower($controllerName);

            // 转换为小驼峰格式的操作名
            $actionCamel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $actionName))));

            // 转换为大驼峰格式的控制器类名
            $controllerClass = str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerName)));
        } else {
            // 不进行大小写转换，完全保持原样
            $controllerSnake = $controllerName;

            // 操作名完全保持原样
            $actionCamel = $actionName;

            // 控制器类名完全保持原样
            $controllerClass = $controllerName;
        }

        return [
            'controllerSnake' => $controllerSnake,
            'actionCamel' => $actionCamel,
            'controllerClass' => $controllerClass,
        ];
    }

    /**
     * 重建URL
     *
     * @param array $parseResult 解析结果
     * @param string $newPath 新路径
     * @return string 重建后的URL
     */
    private static function rebuildUrl(array $parseResult, string $newPath): string
    {
        return $parseResult['scheme'] . '://' .
               ($parseResult['host'] ?? '') .
               (!empty($parseResult['port']) ? ':' . $parseResult['port'] : '') .
               '/' . $newPath .
               (!empty($parseResult['query']) ? '?' . $parseResult['query'] : '') .
               (!empty($parseResult['fragment']) ? '#' . $parseResult['fragment'] : '');
    }
}
