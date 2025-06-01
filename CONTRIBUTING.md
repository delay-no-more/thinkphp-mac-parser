# 贡献指南

感谢您对 ThinkPHP MAC Parser 项目的关注！我们欢迎并鼓励社区贡献。

## 如何贡献

1. **Fork 仓库**
   - 在 GitHub 上 fork 本仓库到您的账户

2. **克隆您的 fork**
   ```bash
   git clone https://github.com/YOUR-USERNAME/thinkphp-mac-parser.git
   cd thinkphp-mac-parser
   ```

3. **安装依赖**
   ```bash
   composer install
   ```

4. **创建分支**
   ```bash
   git checkout -b feature/your-feature-name
   ```

5. **进行更改**
   - 编写代码
   - 添加或更新测试
   - 确保代码符合项目编码规范

6. **运行测试**
   ```bash
   ./vendor/bin/phpunit
   ```

7. **提交更改**
   ```bash
   git commit -m "描述您的更改"
   ```

8. **推送到您的 fork**
   ```bash
   git push origin feature/your-feature-name
   ```

9. **创建 Pull Request**
   - 在 GitHub 上创建从您的分支到原始仓库主分支的 Pull Request

## 代码规范

- 遵循 PSR-12 编码规范
- 为所有新功能编写单元测试
- 保持测试覆盖率在 90% 以上
- 在代码中添加适当的文档注释

## 提交 Pull Request 前的检查清单

- [ ] 代码遵循项目编码规范
- [ ] 添加了单元测试
- [ ] 所有测试通过
- [ ] 更新了相关文档
- [ ] 如果是新功能或重大更改，请在 CHANGELOG.md 中添加记录

## 报告问题

如果您发现 bug 或有功能请求，请在 GitHub 上创建 issue。请尽可能详细地描述问题，包括：

- 问题的详细描述
- 复现步骤
- 预期行为与实际行为
- 环境信息（PHP 版本、操作系统等）
- 可能的解决方案

## 安全问题

如果您发现安全漏洞，请不要公开披露。请直接联系维护者，发送邮件至 [delaynomore@github.com](mailto:delaynomore@github.com)。

## 行为准则

参与本项目即表示您同意遵守我们的行为准则：

- 尊重所有参与者
- 接受建设性批评
- 关注项目最佳利益
- 对其他社区成员表示同理心

## 许可证

通过贡献您的代码，您同意您的贡献将根据项目的 MIT 许可证进行许可。
