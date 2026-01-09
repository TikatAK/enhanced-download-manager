# Enhanced Download Manager

[English](#english) | [中文](#中文)

---

## 中文

轻量级 WordPress 下载管理插件，提供可视化管理界面和完整的下载统计功能。

### ✨ 特性

- 📊 **下载统计** - 自动记录每次下载/点击次数
- 🎨 **可视化管理** - 友好的后台管理界面
- 📁 **双文件来源** - 支持本地文件和外部链接（网盘等）
- 🔗 **短代码支持** - 轻松在文章中插入下载链接
- ⚡ **性能优化** - 内置缓存机制，减少数据库查询
- 🌐 **完全兼容** - 支持所有 WordPress 固定链接结构
- 🎯 **主题兼容** - 双层短代码处理，确保在任何主题下都能正常工作
- 📱 **响应式设计** - 完美支持移动端

### 📦 安装

#### 方法 1: 从 GitHub 下载

1. 下载最新版本的 ZIP 文件
2. 登录 WordPress 后台
3. 前往 **插件 → 安装插件 → 上传插件**
4. 上传 ZIP 文件并激活

#### 方法 2: 手动安装

1. 克隆或下载此仓库
```bash
git clone https://github.com/TikatAK/enhanced-download-manager.git
```

2. 将 `enhanced-download-manager` 文件夹上传到 `/wp-content/plugins/` 目录
3. 在 WordPress 后台激活插件

### 🚀 使用方法

#### 1. 创建下载项

1. 在 WordPress 后台，前往 **Downloads → 添加下载项**
2. 输入标题和描述（可选）
3. 在"下载文件设置"中选择：
   - **本地文件**: 从媒体库选择或上传文件
   - **外部链接**: 输入网盘链接（如百度网盘、Google Drive 等）
4. 点击"发布"

#### 2. 在文章中使用

在文章或页面的编辑器中插入短代码：

```
[download id="123"]
```

其中 `123` 是下载项的 ID（可在编辑下载项时找到）

#### 3. 查看统计

- **列表页**: Downloads 管理页面的"下载次数"列
- **编辑页**: 编辑下载项时，右侧的"下载统计"框

### 🎯 短代码参数

基本用法：
```
[download id="123"]
```

显示效果：
```
下载 [文件标题]
```

### 📊 功能详解

#### 支持的文件类型

- **本地文件**: 所有 WordPress 媒体库支持的文件格式（PDF、ZIP、图片、视频等）
- **外部链接**: 任何有效的 URL（网盘链接、第三方下载地址等）

#### 下载统计

- 自动记录每次下载/点击
- 通过中间跳转链接隐藏真实文件地址
- 支持外部链接统计
- 实时更新计数器

#### 安全特性

- 仅已发布的下载项可访问
- 所有输出经过安全转义
- 防止直接访问插件文件
- Nonce 验证保护

#### 性能优化

- 短代码输出缓存（1小时）
- 批量获取 meta 数据
- 特定文章类型钩子
- 自动缓存清除机制

### 🔧 系统要求

- WordPress 5.0 或更高版本
- PHP 7.0 或更高版本
- MySQL 5.6 或更高版本

### 📝 变更日志

#### 1.0 (2026-01-09)

- 🎉 初始版本发布
- ✅ 自定义文章类型支持
- ✅ 本地文件和外部链接支持
- ✅ 下载统计功能
- ✅ 短代码系统
- ✅ 可视化管理界面
- ✅ 性能优化和缓存机制
- ✅ 完整的使用说明

### 🤝 贡献

欢迎贡献！请随意提交 Pull Request。

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

### 📄 许可证

本项目基于 GPL v2 或更高版本许可证 - 查看 [LICENSE](LICENSE) 文件了解详情

### 👤 作者

**Aikl**

- GitHub: [@TikatAK](https://github.com/TikatAK)
- 插件主页: [Enhanced Download Manager](https://github.com/TikatAK/enhanced-download-manager)

### 🙏 致谢

感谢所有为这个项目做出贡献的开发者！

---

## English

A lightweight WordPress download manager plugin with visual management interface and comprehensive download statistics.

### ✨ Features

- 📊 **Download Statistics** - Automatically track download/click counts
- 🎨 **Visual Management** - User-friendly admin interface
- 📁 **Dual File Sources** - Support local files and external links (cloud storage, etc.)
- 🔗 **Shortcode Support** - Easily insert download links in posts
- ⚡ **Performance Optimized** - Built-in caching mechanism, reduced database queries
- 🌐 **Fully Compatible** - Works with all WordPress permalink structures
- 🎯 **Theme Compatible** - Dual-layer shortcode processing for universal theme support
- 📱 **Responsive Design** - Perfect mobile support

### 📦 Installation

#### Method 1: Download from GitHub

1. Download the latest ZIP file
2. Log in to WordPress admin
3. Go to **Plugins → Add New → Upload Plugin**
4. Upload the ZIP file and activate

#### Method 2: Manual Installation

1. Clone or download this repository
```bash
git clone https://github.com/TikatAK/enhanced-download-manager.git
```

2. Upload the `enhanced-download-manager` folder to `/wp-content/plugins/` directory
3. Activate the plugin in WordPress admin

### 🚀 Usage

#### 1. Create Download Item

1. In WordPress admin, go to **Downloads → Add New**
2. Enter title and description (optional)
3. In "Download File Settings", choose:
   - **Local File**: Select or upload from media library
   - **External Link**: Enter cloud storage link (Baidu Pan, Google Drive, etc.)
4. Click "Publish"

#### 2. Use in Posts

Insert shortcode in post or page editor:

```
[download id="123"]
```

Where `123` is the download item ID (found when editing the download item)

#### 3. View Statistics

- **List Page**: "Download Count" column in Downloads management page
- **Edit Page**: "Download Statistics" box on the right when editing download items

### 🎯 Shortcode Parameters

Basic usage:
```
[download id="123"]
```

Display result:
```
Download [File Title]
```

### 📊 Features in Detail

#### Supported File Types

- **Local Files**: All file formats supported by WordPress media library (PDF, ZIP, images, videos, etc.)
- **External Links**: Any valid URL (cloud storage links, third-party download addresses, etc.)

#### Download Statistics

- Automatically records each download/click
- Hides real file address through redirect link
- Supports external link statistics
- Real-time counter updates

#### Security Features

- Only published download items are accessible
- All outputs are properly escaped
- Prevents direct access to plugin files
- Nonce verification protection

#### Performance Optimization

- Shortcode output caching (1 hour)
- Batch meta data retrieval
- Post type-specific hooks
- Automatic cache clearing

### 🔧 Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

### 📝 Changelog

#### 1.0 (2026-01-09)

- 🎉 Initial release
- ✅ Custom post type support
- ✅ Local files and external links support
- ✅ Download statistics feature
- ✅ Shortcode system
- ✅ Visual management interface
- ✅ Performance optimization and caching
- ✅ Complete usage documentation

### 🤝 Contributing

Contributions are welcome! Feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details

### 👤 Author

**Aikl**

- GitHub: [@TikatAK](https://github.com/TikatAK)
- Plugin Homepage: [Enhanced Download Manager](https://github.com/TikatAK/enhanced-download-manager)

### 🙏 Acknowledgments

Thanks to all developers who contributed to this project!

---

## 💡 Tips & Tricks

- **Batch Management**: Use WordPress quick edit feature to modify multiple downloads
- **SEO Optimization**: Set meaningful titles and descriptions for better search engine indexing
- **Cloud Storage**: Use external links for large files to save server space
- **File Organization**: Create folders in media library to organize download files
- **Download Links**: Generated links use `?dlm_download=ID` format for simplicity and compatibility

## 🐛 Bug Reports

If you find a bug, please [open an issue](https://github.com/TikatAK/enhanced-download-manager/issues) with:

- WordPress version
- PHP version
- Plugin version
- Description of the issue
- Steps to reproduce
- Expected behavior
- Actual behavior

## 📮 Support

- [GitHub Issues](https://github.com/TikatAK/enhanced-download-manager/issues)
- [GitHub Discussions](https://github.com/TikatAK/enhanced-download-manager/discussions)

---

**Made with ❤️ by Aikl**
