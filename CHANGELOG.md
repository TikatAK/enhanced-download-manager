# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-09

### Added
- 🎉 初始版本发布
- ✅ 自定义文章类型支持 (`dlm_download`)
- ✅ 本地文件和外部链接支持
- ✅ 下载统计功能（自动记录下载次数）
- ✅ 短代码系统 `[download id="123"]`
- ✅ 可视化管理界面（Meta Box）
- ✅ 媒体库文件选择器集成
- ✅ 下载次数列显示（后台列表页）
- ✅ 下载统计框（编辑页面）
- ✅ 性能优化和缓存机制（短代码输出缓存 1 小时）
- ✅ 完整的使用说明（后台显示）
- ✅ 兼容所有 WordPress 固定链接结构
- ✅ 双层短代码处理，确保主题兼容性
- ✅ 前端 JavaScript 修复脚本（处理未过滤的短代码）
- ✅ 自动缓存清除机制
- ✅ 安全性增强（输出转义、Nonce 验证、权限检查）

### Features
- 📊 下载统计功能
- 🎨 友好的后台管理界面
- 📁 支持本地文件和外部链接（网盘等）
- 🔗 短代码支持
- ⚡ 性能优化（内置缓存机制）
- 🌐 完全兼容所有 WordPress 固定链接结构
- 🎯 主题兼容性（双层短代码处理）
- 📱 响应式设计

### Technical Details
- 使用 GET 参数方式处理下载跳转（`?dlm_download=ID`）
- 对象缓存优化减少数据库查询
- 批量获取 meta 数据
- 特定文章类型钩子优化
- 完整的错误处理和安全验证

---

[1.0.0]: https://github.com/TikatAK/enhanced-download-manager/releases/tag/v1.0.0

