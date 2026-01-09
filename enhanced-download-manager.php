<?php
/**
 * Plugin Name: Enhanced Download Manager
 * Description: 轻量级下载管理插件，支持可视化管理界面和下载统计功能
 * Version: 1.0.0
 * Author: Aikl
 * Author URI: https://github.com/TikatAK
 * Plugin URI: https://github.com/TikatAK/enhanced-download-manager
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: enhanced-download-manager
 */

if (!defined('ABSPATH')) {
    exit; // 防止直接访问
}

class Enhanced_Download_Manager {

    private $post_type = 'dlm_download';

    public function __construct() {
        // 初始化钩子
        add_action('init', array($this, 'register_post_type'));
        add_action('template_redirect', array($this, 'handle_download_redirect'));

        // Meta Box
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post_' . $this->post_type, array($this, 'save_meta_box'), 10, 2);

        // 短代码
        add_shortcode('download', array($this, 'download_shortcode'));

        // 确保短代码在所有内容区域都能正常工作
        add_filter('the_content', array($this, 'force_shortcode_in_content'), 1);
        add_filter('the_excerpt', 'do_shortcode', 11);
        add_filter('widget_text', 'do_shortcode');

        // 后台列表页
        add_filter('manage_' . $this->post_type . '_posts_columns', array($this, 'add_download_count_column'));
        add_action('manage_' . $this->post_type . '_posts_custom_column', array($this, 'display_download_count_column'), 10, 2);

        // 在列表页底部添加使用说明
        add_action('admin_footer-edit.php', array($this, 'add_usage_instructions'));
        add_action('in_admin_footer', array($this, 'add_usage_instructions_content'));

        // 管理脚本和样式
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // 前端脚本（处理未被过滤的短代码）
        add_action('wp_footer', array($this, 'enqueue_frontend_fix'));

        // 缓存清除
        add_action('save_post_' . $this->post_type, array($this, 'clear_download_cache'));
        add_action('delete_post', array($this, 'clear_download_cache'));

        // 激活插件时刷新重写规则
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivation'));
    }

    /**
     * 注册自定义文章类型
     */
    public function register_post_type() {
        $labels = array(
            'name' => '下载管理',
            'singular_name' => '下载项',
            'menu_name' => 'Downloads',
            'add_new' => '添加下载项',
            'add_new_item' => '添加新下载项',
            'edit_item' => '编辑下载项',
            'new_item' => '新下载项',
            'view_item' => '查看下载项',
            'search_items' => '搜索下载项',
            'not_found' => '未找到下载项',
            'not_found_in_trash' => '回收站中未找到下载项'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-download',
            'supports' => array('title', 'editor'),
            'show_in_rest' => false,
            'rewrite' => array('slug' => 'downloads')
        );

        register_post_type($this->post_type, $args);
    }

    /**
     * 处理下载重定向和统计（使用GET参数方式）
     */
    public function handle_download_redirect() {
        // 早期退出：仅在下载请求时执行
        if (!isset($_GET['dlm_download'])) {
            return;
        }

        $download_id = intval($_GET['dlm_download']);

        if (!$download_id) {
            return;
        }

        // 验证下载项是否存在且已发布
        $post = get_post($download_id);
        if (!$post || $post->post_type !== $this->post_type || $post->post_status !== 'publish') {
            status_header(404);
            wp_die('下载项不存在或不可用', '下载错误', array('response' => 404));
        }

        // 获取文件 URL
        $file_url = get_post_meta($download_id, '_dlm_file_url', true);
        if (empty($file_url)) {
            wp_die('下载链接未设置', '下载错误', array('response' => 400));
        }

        // 增加下载计数
        $current_count = (int) get_post_meta($download_id, '_dlm_download_count', true);
        $new_count = $current_count + 1;
        update_post_meta($download_id, '_dlm_download_count', $new_count);

        // 重定向到实际文件
        wp_redirect($file_url, 302);
        exit;
    }

    /**
     * 添加 Meta Box
     */
    public function add_meta_box() {
        add_meta_box(
            'dlm_file_settings',
            '下载文件设置',
            array($this, 'render_meta_box'),
            $this->post_type,
            'normal',
            'high'
        );

        add_meta_box(
            'dlm_statistics',
            '下载统计',
            array($this, 'render_statistics_box'),
            $this->post_type,
            'side',
            'default'
        );
    }

    /**
     * 渲染 Meta Box
     */
    public function render_meta_box($post) {
        wp_nonce_field('dlm_save_meta_box', 'dlm_meta_box_nonce');

        // 一次性获取所有 meta 数据，减少数据库查询
        $meta_data = get_post_meta($post->ID);
        $file_url = isset($meta_data['_dlm_file_url'][0]) ? $meta_data['_dlm_file_url'][0] : '';
        $is_external = isset($meta_data['_dlm_is_external'][0]) ? $meta_data['_dlm_is_external'][0] : '';

        ?>
        <div class="dlm-meta-box">
            <p>
                <label>
                    <input type="radio" name="dlm_file_type" value="local" <?php checked($is_external, '0'); ?> <?php checked($is_external, ''); ?>>
                    本地文件
                </label>
                <label style="margin-left: 20px;">
                    <input type="radio" name="dlm_file_type" value="external" <?php checked($is_external, '1'); ?>>
                    外部链接
                </label>
            </p>

            <div id="dlm-local-file" class="dlm-option" style="<?php echo ($is_external === '1') ? 'display:none;' : ''; ?>">
                <p>
                    <label for="dlm_local_file_url">文件 URL：</label>
                    <input type="text" id="dlm_local_file_url" name="dlm_file_url_local" value="<?php echo ($is_external !== '1') ? esc_attr($file_url) : ''; ?>" class="widefat" style="margin-bottom: 5px;">
                </p>
                <p>
                    <button type="button" class="button dlm-upload-button">选择文件</button>
                    <button type="button" class="button dlm-clear-button">清除</button>
                </p>
                <p class="description">从媒体库选择或上传文件，或直接输入文件 URL</p>
            </div>

            <div id="dlm-external-link" class="dlm-option" style="<?php echo ($is_external === '1') ? '' : 'display:none;'; ?>">
                <p>
                    <label for="dlm_external_url">外部链接 URL：</label>
                    <input type="text" id="dlm_external_url" name="dlm_file_url_external" value="<?php echo ($is_external === '1') ? esc_attr($file_url) : ''; ?>" class="widefat" placeholder="https://pan.baidu.com/s/xxx">
                </p>
                <p class="description">输入外部下载链接（如百度网盘、Google Drive 等）</p>
            </div>

            <div style="margin-top: 20px; padding: 10px; background: #f0f0f1; border-left: 4px solid #2271b1;">
                <strong>短代码使用：</strong>
                <p><code>[download id="<?php echo $post->ID; ?>"]</code></p>
                <p class="description">将此短代码复制到文章或页面中以显示下载链接</p>
            </div>
        </div>
        <?php
    }

    /**
     * 渲染统计 Meta Box
     */
    public function render_statistics_box($post) {
        $download_count = get_post_meta($post->ID, '_dlm_download_count', true);
        $download_count = $download_count ? $download_count : 0;

        ?>
        <div class="dlm-statistics">
            <p><strong>总下载次数：</strong></p>
            <p style="font-size: 32px; margin: 10px 0; color: #2271b1;">
                <?php echo esc_html($download_count); ?>
            </p>
            <p class="description">此计数器会在用户点击下载链接时自动增加</p>
        </div>
        <?php
    }

    /**
     * 保存 Meta Box 数据
     */
    public function save_meta_box($post_id, $post) {
        // 验证 nonce
        if (!isset($_POST['dlm_meta_box_nonce']) || !wp_verify_nonce($_POST['dlm_meta_box_nonce'], 'dlm_save_meta_box')) {
            return;
        }

        // 检查自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // 检查权限
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // 保存文件类型和 URL
        if (isset($_POST['dlm_file_type'])) {
            $is_external = ($_POST['dlm_file_type'] === 'external') ? '1' : '0';
            update_post_meta($post_id, '_dlm_is_external', $is_external);

            if ($is_external === '1') {
                // 外部链接
                if (isset($_POST['dlm_file_url_external'])) {
                    $file_url = esc_url_raw($_POST['dlm_file_url_external']);
                    update_post_meta($post_id, '_dlm_file_url', $file_url);
                }
            } else {
                // 本地文件
                if (isset($_POST['dlm_file_url_local'])) {
                    $file_url = esc_url_raw($_POST['dlm_file_url_local']);
                    update_post_meta($post_id, '_dlm_file_url', $file_url);
                }
            }
        }

        // 初始化下载计数（仅在首次创建时，使用 add_post_meta 避免查询）
        add_post_meta($post_id, '_dlm_download_count', 0, true);
    }

    /**
     * 加载管理脚本和样式
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;

        // 只在编辑页面加载
        if (($hook !== 'post.php' && $hook !== 'post-new.php') || $post_type !== $this->post_type) {
            return;
        }

        // 加载 WordPress 媒体上传器
        wp_enqueue_media();

        // 添加内联脚本和样式
        wp_add_inline_script('jquery', $this->get_admin_javascript());
        wp_add_inline_style('wp-admin', $this->get_admin_styles());
    }

    /**
     * 获取管理 JavaScript 代码
     */
    private function get_admin_javascript() {
        return "
        jQuery(document).ready(function($) {
            var mediaUploader;

            // 切换文件类型
            $('input[name=\"dlm_file_type\"]').on('change', function() {
                if ($(this).val() === 'local') {
                    $('#dlm-local-file').show();
                    $('#dlm-external-link').hide();
                } else {
                    $('#dlm-local-file').hide();
                    $('#dlm-external-link').show();
                }
            });

            // 上传按钮
            $(document).on('click', '.dlm-upload-button', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: '选择下载文件',
                    button: {
                        text: '使用此文件'
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#dlm_local_file_url').val(attachment.url);
                });

                mediaUploader.open();
            });

            // 清除按钮
            $(document).on('click', '.dlm-clear-button', function(e) {
                e.preventDefault();
                $('#dlm_local_file_url').val('');
            });
        });
        ";
    }

    /**
     * 获取管理样式
     */
    private function get_admin_styles() {
        return "
        .dlm-meta-box {
            padding: 10px 0;
        }
        .dlm-option {
            margin-top: 15px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .dlm-statistics {
            text-align: center;
        }
        ";
    }

    /**
     * 强制处理内容中的短代码（最高优先级）
     */
    public function force_shortcode_in_content($content) {
        // 检查内容中是否包含我们的短代码
        if (strpos($content, '[download') !== false) {
            $content = do_shortcode($content);
        }
        return $content;
    }

    /**
     * 短代码实现
     */
    public function download_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts);

        $download_id = intval($atts['id']);

        if (!$download_id) {
            return '<span class="dlm-error">[下载链接未设置]</span>';
        }

        // 使用对象缓存提高性能
        $cache_key = 'dlm_shortcode_' . $download_id;
        $cached_output = wp_cache_get($cache_key, 'dlm_downloads');

        if (false !== $cached_output) {
            return $cached_output;
        }

        $post = get_post($download_id);

        if (!$post || $post->post_type !== $this->post_type || $post->post_status !== 'publish') {
            return '<span class="dlm-error">[下载链接未设置]</span>';
        }

        $file_url = get_post_meta($download_id, '_dlm_file_url', true);

        if (empty($file_url)) {
            return '<span class="dlm-error">[下载链接未设置]</span>';
        }

        // 生成下载URL（使用GET参数，兼容所有固定链接结构）
        $download_url = add_query_arg('dlm_download', $download_id, home_url('/'));
        $title = get_the_title($download_id);

        $output = sprintf(
            '<a href="%s" class="dlm-download-link" rel="noopener">下载 %s</a>',
            esc_url($download_url),
            esc_html($title)
        );

        // 缓存输出 1 小时
        wp_cache_set($cache_key, $output, 'dlm_downloads', 3600);

        return $output;
    }

    /**
     * 添加下载次数列
     */
    public function add_download_count_column($columns) {
        $new_columns = array();

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            // 在标题后添加下载次数列
            if ($key === 'title') {
                $new_columns['download_count'] = '下载次数';
            }
        }

        return $new_columns;
    }

    /**
     * 显示下载次数列
     */
    public function display_download_count_column($column, $post_id) {
        if ($column === 'download_count') {
            $count = get_post_meta($post_id, '_dlm_download_count', true);
            echo '<strong>' . esc_html($count ? $count : 0) . '</strong>';
        }
    }

    /**
     * 插件激活
     */
    public function plugin_activation() {
        $this->register_post_type();
        flush_rewrite_rules();
    }

    /**
     * 插件停用
     */
    public function plugin_deactivation() {
        flush_rewrite_rules();
    }

    /**
     * 清除下载项缓存
     */
    public function clear_download_cache($post_id) {
        $cache_key = 'dlm_shortcode_' . $post_id;
        wp_cache_delete($cache_key, 'dlm_downloads');
    }

    /**
     * 前端修复脚本（处理主题未过滤短代码的情况）
     */
    public function enqueue_frontend_fix() {
        ?>
        <script type="text/javascript">
        (function() {
            // 查找所有包含 [download id="数字"] 的文本节点
            function processShortcodes() {
                var walker = document.createTreeWalker(
                    document.body,
                    NodeFilter.SHOW_TEXT,
                    null,
                    false
                );

                var nodesToReplace = [];
                var node;

                while (node = walker.nextNode()) {
                    if (/\[download\s+id=["']?\d+["']?\]/.test(node.textContent)) {
                        nodesToReplace.push(node);
                    }
                }

                nodesToReplace.forEach(function(textNode) {
                    var content = textNode.textContent;
                    var regex = /\[download\s+id=["']?(\d+)["']?\]/g;

                    if (regex.test(content)) {
                        content = content.replace(/\[download\s+id=["']?(\d+)["']?\]/g, function(match, id) {
                            var url = '<?php echo home_url('/'); ?>?dlm_download=' + id;
                            return '<a href="' + url + '" class="dlm-download-link" rel="noopener">下载文件</a>';
                        });

                        var wrapper = document.createElement('span');
                        wrapper.innerHTML = content;
                        textNode.parentNode.replaceChild(wrapper, textNode);
                    }
                });
            }

            // 页面加载完成后执行
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', processShortcodes);
            } else {
                processShortcodes();
            }
        })();
        </script>
        <?php
    }

    /**
     * 在下载管理列表页底部添加使用说明（CSS部分）
     */
    public function add_usage_instructions() {
        global $post_type;

        // 只在下载管理列表页显示
        if ($post_type !== $this->post_type) {
            return;
        }

        ?>
        <style>
        #wpbody-content .wrap > .dlm-usage-instructions {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 20px;
            margin: 20px 0;
        }

        .dlm-usage-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        @media screen and (max-width: 782px) {
            .dlm-usage-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // 在表格的容器（包括分页和批量操作）之后插入使用说明
            var usageHtml = $('#dlm-usage-instructions-template').html();
            if (usageHtml) {
                // 找到表格的父容器，在整个列表表单之后插入
                $('form#posts-filter').after(usageHtml);
            }
        });
        </script>
        <?php
    }

    /**
     * 使用说明HTML内容
     */
    public function add_usage_instructions_content() {
        global $post_type;

        // 只在下载管理列表页显示
        if ($post_type !== $this->post_type) {
            return;
        }

        ?>
        <script type="text/template" id="dlm-usage-instructions-template">
            <div class="dlm-usage-instructions">
                <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 2px solid #2271b1;">📖 使用说明</h2>

                <div class="dlm-usage-grid">
                    <!-- 左侧：基本使用 -->
                    <div>
                        <h3 style="color: #2271b1; margin-top: 0;">🚀 快速开始</h3>

                        <h4>1️⃣ 创建下载项</h4>
                        <ul style="line-height: 1.8;">
                            <li>点击上方"添加下载项"按钮</li>
                            <li>输入标题和描述（可选）</li>
                            <li>在"下载文件设置"中选择：
                                <ul>
                                    <li><strong>本地文件</strong>：从媒体库选择或上传文件</li>
                                    <li><strong>外部链接</strong>：输入网盘链接（如百度网盘、Google Drive等）</li>
                                </ul>
                            </li>
                            <li>点击"发布"</li>
                        </ul>

                        <h4>2️⃣ 在文章中使用</h4>
                        <p>在文章或页面中插入短代码：</p>
                        <div style="background: #f0f0f1; padding: 12px; border-radius: 4px; font-family: monospace; margin: 10px 0;">
                            [download id="<span style="color: #d63638;">下载项ID</span>"]
                        </div>
                        <p style="color: #646970; font-size: 13px;">💡 提示：在编辑下载项时，可以在右侧"下载文件设置"框中找到对应的短代码。</p>

                        <h4>3️⃣ 查看统计</h4>
                        <ul style="line-height: 1.8;">
                            <li>在此页面的"下载次数"列查看总下载量</li>
                            <li>在编辑页面右侧的"下载统计"框中查看详细数据</li>
                        </ul>
                    </div>

                    <!-- 右侧：高级功能 -->
                    <div>
                        <h3 style="color: #2271b1; margin-top: 0;">⚙️ 功能特性</h3>

                        <h4>✅ 支持的文件类型</h4>
                        <ul style="line-height: 1.8;">
                            <li><strong>本地文件</strong>：所有WordPress媒体库支持的文件格式（PDF、ZIP、图片、视频等）</li>
                            <li><strong>外部链接</strong>：任何有效的URL（网盘链接、第三方下载地址等）</li>
                        </ul>

                        <h4>📊 下载统计</h4>
                        <ul style="line-height: 1.8;">
                            <li>自动记录每次下载/点击</li>
                            <li>通过中间跳转链接隐藏真实文件地址</li>
                            <li>支持外部链接统计</li>
                        </ul>

                        <h4>🔒 安全特性</h4>
                        <ul style="line-height: 1.8;">
                            <li>仅已发布的下载项可访问</li>
                            <li>所有输出经过安全转义</li>
                            <li>防止直接访问文件URL</li>
                        </ul>

                        <h4>🌐 兼容性</h4>
                        <ul style="line-height: 1.8;">
                            <li>兼容所有WordPress固定链接结构</li>
                            <li>使用GET参数方式，无需配置服务器重写规则</li>
                            <li>支持任意主题和页面编辑器</li>
                        </ul>
                    </div>
                </div>

                <div style="margin-top: 30px; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #2271b1;">💡 实用技巧</h4>
                    <ul style="margin-bottom: 0; line-height: 1.8;">
                        <li><strong>批量管理</strong>：使用WordPress的快速编辑功能批量修改下载项</li>
                        <li><strong>SEO优化</strong>：为下载项设置有意义的标题和描述，有助于搜索引擎收录</li>
                        <li><strong>网盘链接</strong>：对于大文件，建议使用外部网盘链接以节省服务器空间</li>
                        <li><strong>文件组织</strong>：在媒体库中创建文件夹来组织下载文件</li>
                        <li><strong>下载链接</strong>：生成的下载链接格式为 <code>?dlm_download=ID</code>，简洁且兼容性好</li>
                    </ul>
                </div>

                <div style="margin-top: 20px; text-align: center; padding-top: 15px; border-top: 1px solid #dcdcde; color: #646970; font-size: 13px;">
                    <p style="margin: 0;">Enhanced Download Manager v1.0.0 | 轻量级下载管理插件</p>
                </div>
            </div>
        </script>
        <?php
    }
}

// 初始化插件
new Enhanced_Download_Manager();
