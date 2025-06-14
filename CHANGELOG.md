# 更新日志

所有版本的显著变更都将记录在此文件中。

本项目遵循 [语义化版本控制](https://semver.org/lang/zh-CN/)。

## [1.1.0] - 2025-06-01

### 新增

- 首个正式Releases版本发布
- 修复许可证徽章显示问题，确保正确显示MIT许可证
- 完善项目文档，添加更多使用场景说明

### 改进

- 优化代码结构，提高可维护性
- 增强错误处理机制，提供更友好的错误提示
- 改进测试覆盖率，确保代码质量

## [1.0.6] - 2025-06-01

### 新增

- 添加实际解析结果的JSON输出示例，提高文档直观性
- 新增常见问题(FAQ)部分，解答用户常见疑问
- 添加项目创建起因说明，阐明解决的具体问题

### 改进

- 优化README文档结构，增加更多实用徽章
- 改进示例代码，添加更详细的注释和使用场景
- 更新文档中的链接，确保所有链接正确且有效
- 修正了关于ThinkPHP框架集成的不准确描述

## [1.0.5] - 2025-06-01

### 文档

- 更新贡献指南，添加详细的分支管理策略
- 创建GitHub Issue和PR模板
- 添加贡献部分到README文件

## [1.0.4] - 2025-06-01

### 增强

- 提高代码测试覆盖率从 87% 到 95%
- 增加了特殊域名格式和边界情况的测试用例
- 增加了对 extractRootDomain 方法的全面测试
- 增加了对 matchSubdomain 方法的各种情况测试
- 增加了对 rebuildUrl 方法的测试

## [1.0.3] - 2025-01-04

### 增强

- 增加了多级控制器与名称转换混合模式的测试用例
- 验证了在各种复杂URL格式下的解析正确性，包括多级目录、下划线命名和混合大小写

## [1.0.2] - 2025-01-03

### 修复

- 修复了 `parseController` 方法在 `convert=false` 时不保持操作名原始大小写的问题
- 更新了相关测试用例，确保测试与实现一致

## [1.0.1] - 2025-01-02

### 修复

- 修复了 `convertNames` 方法在 `convert=false` 时不保持操作名原始大小写的问题
- 修复了相关单元测试，确保测试与实现一致

## [1.0.0] - 2025-01-01

### 新增

- 初始版本发布
- 添加 `Parser` 类，支持 URL 解析
- 添加 `parseMac()` 方法解析完整 URL
- 添加 `parseDomain()` 方法解析域名
- 添加 `parseController()` 方法解析控制器路径
- 添加示例文件和单元测试

[1.1.0]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.1.0
[1.0.6]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.0.6
[1.0.5]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.0.5
[1.0.4]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.0.4
[1.0.3]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.0.3
[1.0.2]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.0.2
[1.0.1]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.0.1
[1.0.0]: https://github.com/delay-no-more/thinkphp-mac-parser/releases/tag/v1.0.0
