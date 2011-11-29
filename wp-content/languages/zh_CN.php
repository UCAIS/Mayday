<?php
/**
 * WordPress China 中文语言包补丁文件
 *
 * 提供一系列中国用户的常用功能。
 *
 * @package WordPress China
 */

/**
 * 定义当前中文语言包版本号
 *
 * @since 3.0.2
 */
define( 'ZH_CN_PACK_OPTIONS_VERSION' , 3 );

/**
 * 定义本地化补丁目录
 *
 * @since 3.2
 */
define( 'ZH_CN_PACK_LANGUAGE_DIR_TO_CONTENT' , 'languages/' );


/**
 * 注册设置
 *
 * @since 3.0.1
 */
function zh_cn_language_pack_backend_register_settings() {
    add_settings_field( 'zh_cn_language_pack_enable_chinese_fake_oembed',
        '中国媒体嵌入',
        'zh_cn_language_pack_embed_fake_oembed_settings',
        'media',
        'embeds' );

    add_option( 'zh_cn_language_pack_enable_backend_style_modifications', 1 );
    add_option( 'zh_cn_language_pack_enable_chinese_fake_oembed', 1 );
    
    register_setting( 'zh-cn-language-pack-general-settings',
        'zh_cn_language_pack_enable_backend_style_modifications' );
    register_setting( 'zh-cn-language-pack-general-settings',
        'zh_cn_language_pack_enable_chinese_fake_oembed' );

    register_setting( 'media',
        'zh_cn_language_pack_enable_chinese_fake_oembed' );

    // 从用户数据库移除旧设置项
    delete_option( 'zh_cn_language_pack_options_version' ); // TODO 在 3.2 之后移除本行
}

/**
 * 添加菜单
 *
 * @since 3.0.1
 */
function zh_cn_language_pack_backend_create_menu() {
    add_options_page( '中文本地化选项', '中文本地化', 'administrator', 'zh-cn-language-pack-settings', 
        'zh_cn_language_pack_settings_page' );
}

/**
 * 添加控制板帮助文本
 *
 * @since 3.0.1
 */
function zh_cn_language_pack_contextual_help() {
    add_contextual_help('settings_page_zh-cn-language-pack-settings',
        '<p>在这里对 WordPress 官方中文语言包进行自定义。</p>' .
        '<p><strong>后台样式优化</strong> - 开启后可以令后台显示中文更加美观，它不会影响到您站点前台的样式。默认开启。</p>' .
        '<p><strong>中文视频网站视频自动嵌入</strong> - 允许您以在文章添加视频播放页面网址的方式，简单地插入优酷网、56.com 和土豆网视频。默认开启。<br />当前支持的站点、样例 URL 和参数如下：</p>' .
        '<ul>' .
        '   <li><em>优酷网</em> - 如 <code>http://v.youku.com/v_show/id_XMjQxMjc1MDIw.html</code> - 宽 480px，高 400px</li>' .
        '   <li><em>56.com</em> - 如 <code>http://www.56.com/u21/v_NTgxMzE4NDI.html</code> - 宽 480px，高 395px</li>' .
        '   <li><em>土豆网</em> - 如 <code>http://www.tudou.com/programs/view/o9tsm_CL5As/</code> - 宽 480px，高 400px</li>' .
        '</ul>' .
        '<p>您只需在文章另起一段，写入形如上述的播放页面链接。在文章显示时，WordPress 将自动替换这些链接为相应视频播放器。需要您特别注意的是，请不要为 URL 设置超链接，且该 URL 本身必须独立成段。' .
        '<p><strong>更多信息：</strong></p>' .
        '<p>若您发现任何文字上的错误，或有任何意见、建议，欢迎访问下列页面进行回报 ——<br />' .
        '<a href="http://cn.wordpress.org/contact/" target="_blank">WordPress China “联系”页面</a></p>'
    );
}

/**
 * 输出媒体页面设置项代码
 *
 * @since 3.2
 */
function zh_cn_language_pack_embed_fake_oembed_settings() {
    echo '<input type="checkbox" id="zh_cn_language_pack_enable_chinese_fake_oembed" name="zh_cn_language_pack_enable_chinese_fake_oembed" value="1"';
    echo checked( '1', get_option( 'zh_cn_language_pack_enable_chinese_fake_oembed' ) );
    echo ' /> 自动从 URL 嵌入中国视频网站上的视频。详见“设置” → “中文本地化”的帮助。';
}

/**
 * 添加设置页面
 *
 * @since 3.0.1
 */
function zh_cn_language_pack_settings_page() {
    ?><div class="wrap">
<h2>中文本地化选项</h2>

<form method="post" action="options.php">
    <h3 class="title">调整设置</h3>
    <p>对中文语言包进行自定义。</p>
    <?php settings_fields( 'zh-cn-language-pack-general-settings' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">后台样式优化</th>
            <td>
                <label for="zh_cn_language_pack_enable_backend_style_modifications"><input type="checkbox" id="zh_cn_language_pack_enable_backend_style_modifications" name="zh_cn_language_pack_enable_backend_style_modifications" value="1"<?php checked( '1', get_option( 'zh_cn_language_pack_enable_backend_style_modifications' ) ); ?> /> 对后台样式进行优化。</label>
                <br />
                <span class="description">
                    优化控制板以及登录页面的字体样式。此操作不会影响到您的博客前台。
                </span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">中国视频网站视频自动嵌入</th>
            <td>
                <label for="zh_cn_language_pack_enable_chinese_fake_oembed"><input type="checkbox" id="zh_cn_language_pack_enable_chinese_fake_oembed" name="zh_cn_language_pack_enable_chinese_fake_oembed" value="1"<?php checked( '1', get_option( 'zh_cn_language_pack_enable_chinese_fake_oembed' ) ); ?> /> 自动从 URL 嵌入中国视频网站上的视频。</label>
                <br />
                <span class="description">
                    自动嵌入优酷网、56.com 和土豆网的视频。用法及显示大小，请见页面上方“帮助”选项卡。
                </span>
            </td>
        </tr>
    </table>
    
    <p class="submit">
        <input type="submit" class="button-primary" value="保存更改" />
    </p>
</form>

</div><?php
}

/**
 * 显示文章时将 URL 替换成媒体嵌入代码
 *
 * @since 3.0.5
 */
function zh_cn_language_pack_substitute_chinese_video_urls( $content ) {
    $schema = array('/^<p>http:\/\/v\.youku\.com\/v_show\/id_([a-z0-9_=\-]+)\.html((\?|#|&).*?)*?\s*<\/p>\s*$/im' => '<p><embed src="http://player.youku.com/player.php/sid/$1/v.swf" quality="high" width="480" height="400" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed></p>',
        '/^<p>http:\/\/www\.56\.com\/[a-z0-9]+\/v_([a-z0-9_\-]+)\.html((\?|#|&).*?)*?\s*<\/p>\s*$/im' => '<p><embed src="http://player.56.com/v_$1.swf" type="application/x-shockwave-flash" width="480" height="395" allowNetworking="all" allowScriptAccess="always"></embed></p>',
        '/^<p>http:\/\/www\.tudou\.com\/programs\/view\/([a-z0-9_\-]+)[\/]?((\?|#|&).*?)*?\s*<\/p>\s*$/im' => '<p><embed src="http://www.tudou.com/v/$1/v.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque" width="480" height="400"></embed></p>');

    foreach ( $schema as $pattern => $replacement ) {
        $content = preg_replace( $pattern, $replacement, $content );
    }
    
    return $content;
}
    
/**
 * 输出后台样式优化代码
 *
 * @since 3.0.1
 */
function zh_cn_language_pack_backend_style_modify() {
    $styleUrl = WP_CONTENT_URL . '/' . ZH_CN_PACK_LANGUAGE_DIR_TO_CONTENT . 'zh_CN-dashboard.css';
    $styleFile = WP_CONTENT_DIR . '/' . ZH_CN_PACK_LANGUAGE_DIR_TO_CONTENT . 'zh_CN-dashboard.css';
    if ( file_exists( $styleFile ) ) {
        wp_register_style( 'zh-cn-pack-style-dashboard', $styleUrl, array(), '1.0');
        wp_enqueue_style( 'zh-cn-pack-style-dashboard' );
    }
}

/**
 * 输出登录页面样式优化代码
 *
 * @since 3.0.1
 */
function zh_cn_language_pack_login_screen_style_modify() {
    // 贡献：CSS 代码由 moja 提供
    // 您可以在 http://cn.wordpress.org/contact/ 提交您的意见
    echo <<<EOF
<style type="text/css" media="screen">
    * { font: 12px Segoe UI,Tahoma,Arial,Verdana,simsun,sans-serif,"Microsoft YaHei"; }
</style>

EOF;
}


// 准备控制板页面
if ( is_admin() ) {
    add_action( 'admin_init', 'zh_cn_language_pack_backend_register_settings' );
    add_action( 'admin_menu', 'zh_cn_language_pack_backend_create_menu' );
    add_action( 'admin_head-settings_page_zh-cn-language-pack-settings', 'zh_cn_language_pack_contextual_help' );
}

// 后台样式优化
if ( get_option('zh_cn_language_pack_enable_backend_style_modifications') == 1 ) {
    add_action( 'admin_init', 'zh_cn_language_pack_backend_style_modify' );
    add_action( 'login_head', 'zh_cn_language_pack_login_screen_style_modify' );
}

// 中国媒体嵌入
if ( get_option('zh_cn_language_pack_enable_chinese_fake_oembed') == 1 ) {
    add_filter( 'the_content', 'zh_cn_language_pack_substitute_chinese_video_urls' );
}

?>
