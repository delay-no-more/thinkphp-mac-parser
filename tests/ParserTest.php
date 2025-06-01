<?php

namespace Tests;

use DelayNomore\ThinkMac\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Parser类的单元测试
 */
class ParserTest extends TestCase
{
    /**
     * 测试基本URL解析功能
     */
    public function testBasicUrlParsing()
    {
        // 测试基本URL
        $result = Parser::parseMac('index/blog/read');
        $this->assertEquals('index', $result['module']);
        $this->assertEquals('blog', $result['ctrl']);
        $this->assertEquals('read', $result['action']);
        $this->assertEquals('Blog', $result['class']);
        $this->assertEquals('read', $result['method']);
        $this->assertEquals('index/blog/read', $result['fullpath']);
        $this->assertEquals('index/blog/read', $result['url']);
        $this->assertFalse($result['nested']);
        $this->assertEquals(1, $result['depth']);

        // 测试带PHP文件的URL
        $result = Parser::parseMac('index.php/blog/read');
        $this->assertEquals('index', $result['module']);
        $this->assertEquals('blog', $result['ctrl']);
        $this->assertEquals('read', $result['action']);

        // 测试完整URL
        $result = Parser::parseMac('http://example.com/admin/user/view');
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('user', $result['ctrl']);
        $this->assertEquals('view', $result['action']);
    }

    /**
     * 测试多级控制器解析
     */
    public function testNestedControllerParsing()
    {
        // 测试一级嵌套
        $result = Parser::parseMac('admin/user.profile/edit');
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('profile', $result['ctrl']);
        $this->assertEquals('edit', $result['action']);
        $this->assertEquals('user', $result['dir']);
        $this->assertEquals('user/profile', $result['path']);
        $this->assertEquals('admin/user/profile/edit', $result['fullpath']);
        $this->assertEquals('admin/user.profile/edit', $result['url']);
        $this->assertTrue($result['nested']);
        $this->assertEquals(2, $result['depth']);

        // 测试多级嵌套
        $result = Parser::parseMac('admin/one.two.three.controller/action');
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('controller', $result['ctrl']);
        $this->assertEquals('action', $result['action']);
        $this->assertEquals('one/two/three', $result['dir']);
        $this->assertEquals('one/two/three/controller', $result['path']);
        $this->assertEquals('admin/one/two/three/controller/action', $result['fullpath']);
        $this->assertEquals('admin/one.two.three.controller/action', $result['url']);
        $this->assertTrue($result['nested']);
        $this->assertEquals(4, $result['depth']);
    }

    /**
     * 测试域名绑定模块
     */
    public function testDomainBindingModule()
    {
        $bindDomains = [
            'admin.example.com' => 'admin',
            'api.*' => 'api',
            '*.user' => 'user',
            '*' => 'www'
        ];

        // 测试完整域名匹配
        $result = Parser::parseMac('http://admin.example.com/index/index', [
            'bind_domains' => $bindDomains
        ]);
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('index', $result['ctrl']);
        $this->assertEquals('index', $result['action']);

        // 测试前缀泛域名匹配
        $result = Parser::parseMac('http://api.v2.example.com/index/index', [
            'bind_domains' => $bindDomains
        ]);
        $this->assertEquals('api', $result['module']);

        // 测试后缀泛域名匹配
        $result = Parser::parseMac('http://profile.user.example.com/index/index', [
            'bind_domains' => $bindDomains
        ]);
        $this->assertEquals('user', $result['module']);

        // 测试通配符匹配
        $result = Parser::parseMac('http://other.example.com/index/index', [
            'bind_domains' => $bindDomains
        ]);
        $this->assertEquals('www', $result['module']);
    }

    /**
     * 测试默认模块、控制器和操作
     */
    public function testDefaultValues()
    {
        // 测试默认值
        $result = Parser::parseMac('', [
            'default_module' => 'home',
            'default_controller' => 'index',
            'default_action' => 'index'
        ]);
        $this->assertEquals('home', $result['module']);
        $this->assertEquals('index', $result['ctrl']);
        $this->assertEquals('index', $result['action']);

        // 测试只提供模块
        $result = Parser::parseMac('admin');
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('index', $result['ctrl']);
        $this->assertEquals('index', $result['action']);

        // 测试提供模块和控制器
        $result = Parser::parseMac('admin/user');
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('user', $result['ctrl']);
        $this->assertEquals('index', $result['action']);
    }

    /**
     * 测试名称转换功能
     */
    public function testNameConversion()
    {
        // 测试convert=true (默认)
        $result = Parser::parseMac('admin/user_profile/get_info');
        $this->assertEquals('user_profile', $result['ctrl']);
        $this->assertEquals('UserProfile', $result['class']);
        $this->assertEquals('get_info', $result['action']);
        $this->assertEquals('getInfo', $result['method']);

        // 测试convert=false
        $result = Parser::parseMac('admin/UserProfile/getInfo', [
            'convert' => false
        ]);
        $this->assertEquals('UserProfile', $result['ctrl']);
        $this->assertEquals('UserProfile', $result['class']);
        $this->assertEquals('getInfo', $result['action']);
        $this->assertEquals('getInfo', $result['method']);
    }

    /**
     * 测试单独解析域名功能
     */
    public function testParseDomainMethod()
    {
        $rules = [
            'admin.example.com' => 'admin',
            'api.*' => 'api',
            '*.user' => 'user',
            '*' => 'www'
        ];

        // 测试完整域名
        $result = Parser::parseDomain('admin.example.com', $rules);
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('admin.example.com', $result['domain']);
        $this->assertEquals('example.com', $result['root']);
        $this->assertEquals('admin', $result['sub']);

        // 测试前缀泛域名
        $result = Parser::parseDomain('api.v1.example.com', $rules);
        $this->assertEquals('api', $result['module']);
        $this->assertEquals('api.v1', $result['sub']);

        // 测试后缀泛域名
        $result = Parser::parseDomain('something.user.example.com', $rules);
        $this->assertEquals('user', $result['module']);
        $this->assertEquals('something.user', $result['sub']);

        // 测试URL格式
        $result = Parser::parseDomain('http://api.example.com/path?query=value', $rules);
        $this->assertEquals('api', $result['module']);
        $this->assertEquals('api.example.com', $result['domain']);
    }

    /**
     * 测试单独解析控制器功能
     */
    public function testParseControllerMethod()
    {
        // 测试基本控制器路径
        $result = Parser::parseController('user/view');
        $this->assertEquals('user', $result['ctrl']);
        $this->assertEquals('User', $result['class']);
        $this->assertEquals('view', $result['action']);
        $this->assertEquals('view', $result['method']);
        $this->assertFalse($result['nested']);

        // 测试多级控制器
        $result = Parser::parseController('admin.user.profile/edit');
        $this->assertEquals('profile', $result['ctrl']);
        $this->assertEquals('Profile', $result['class']);
        $this->assertEquals('edit', $result['action']);
        $this->assertEquals('edit', $result['method']);
        $this->assertEquals('admin/user', $result['dir']);
        $this->assertEquals('admin/user/profile', $result['path']);
        $this->assertTrue($result['nested']);
        $this->assertEquals(3, $result['depth']);

        // 测试下划线分隔的名称
        $result = Parser::parseController('user_center/get_info');
        $this->assertEquals('user_center', $result['ctrl']);
        $this->assertEquals('UserCenter', $result['class']);
        $this->assertEquals('get_info', $result['action']);
        $this->assertEquals('getInfo', $result['method']);
    }

    /**
     * 测试多级控制器与名称转换的混合模式
     */
    public function testMixedNestedAndConversion()
    {
        // 测试多级控制器 + 下划线名称 + convert=true (默认)
        $result = Parser::parseMac('admin/api.user_center.account_manager/get_user_info');
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('account_manager', $result['ctrl']);
        $this->assertEquals('AccountManager', $result['class']);
        $this->assertEquals('get_user_info', $result['action']);
        $this->assertEquals('getUserInfo', $result['method']);
        $this->assertEquals('api/user_center', $result['dir']);
        $this->assertEquals('api/user_center/account_manager', $result['path']);
        $this->assertEquals('admin/api/user_center/account_manager/get_user_info', $result['fullpath']);
        $this->assertEquals('admin/api.user_center.account_manager/get_user_info', $result['url']);
        $this->assertTrue($result['nested']);
        $this->assertEquals(3, $result['depth']);

        // 测试多级控制器 + 混合大小写 + convert=false
        $result = Parser::parseMac('admin/api.userCenter.AccountManager/getUserInfo', [
            'convert' => false
        ]);
        $this->assertEquals('admin', $result['module']);
        $this->assertEquals('AccountManager', $result['ctrl']);
        $this->assertEquals('AccountManager', $result['class']);
        $this->assertEquals('getUserInfo', $result['action']);
        $this->assertEquals('getUserInfo', $result['method']);
        $this->assertEquals('api/usercenter', $result['dir']);
        $this->assertEquals('api/usercenter/AccountManager', $result['path']);
        $this->assertEquals('admin/api/usercenter/AccountManager/getUserInfo', $result['fullpath']);
        $this->assertEquals('admin/api.usercenter.AccountManager/getUserInfo', $result['url']);
        $this->assertTrue($result['nested']);
        $this->assertEquals(3, $result['depth']);
    }
}
