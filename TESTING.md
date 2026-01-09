# 测试步骤

## 1. 清除所有缓存

### A. 浏览器缓存
- Chrome/Edge: Ctrl + Shift + Delete → 清除缓存和Cookie
- Firefox: Ctrl + Shift + Delete → 清除缓存

### B. WordPress 缓存
在 WordPress 后台执行：
1. 进入 设置 → 固定链接
2. 不做任何修改，直接点击"保存更改"（这会刷新重写规则）

### C. 如果使用了缓存插件
- W3 Total Cache: Performance → Purge All Caches
- WP Super Cache: Settings → Delete Cache
- 或其他缓存插件的清除功能

## 2. 强制刷新

### A. 首页测试
1. 打开首页
2. 按 Ctrl + F5 (强制刷新，跳过缓存)
3. 检查短代码是否显示为链接

### B. 检查源代码
1. 右键 → 查看页面源代码
2. 搜索 `[download id=`
3. 应该看不到这个文本（已被替换为链接）

### C. 检查控制台
1. F12 打开开发者工具
2. 切换到 Console 标签
3. 查看是否有 JavaScript 错误

## 3. 测试下载计数

### A. 点击下载链接
1. 记录当前下载次数（如：5）
2. 点击下载链接
3. 等待重定向完成

### B. 刷新后台
1. 进入 Downloads 管理页面
2. 刷新页面（F5）
3. 查看计数是否 +1（应该是 6）

### C. 如果仍然 +2
取消注释第 127 行的调试日志：
```php
// 将这行：
// error_log("Download count for ID {$download_id}: {$current_count} -> {$new_count}");

// 改为：
error_log("Download count for ID {$download_id}: {$current_count} -> {$new_count}");
```

然后查看日志文件：`wp-content/debug.log`
看是否有两次日志（如果有，说明函数被调用了两次）

## 4. 检查主题兼容性

### A. 切换到默认主题测试
1. 外观 → 主题
2. 临时激活 Twenty Twenty-Four 或其他默认主题
3. 访问首页查看短代码是否正常

### B. 如果默认主题正常
说明是当前主题的问题，可能需要：
- 更新主题
- 联系主题作者
- 或在主题的 functions.php 添加过滤器支持

## 5. 预期结果

✅ **成功标准**：
- 首页摘要显示：`下载 文件名` 或完整链接
- 点击下载链接后计数 +1
- 文件正确下载

❌ **如果失败**：
请提供以下信息：
1. 浏览器控制台的错误信息（F12 → Console）
2. WordPress 调试日志（wp-content/debug.log）
3. Network 面板显示的请求次数（F12 → Network）
4. 当前使用的主题名称和版本
