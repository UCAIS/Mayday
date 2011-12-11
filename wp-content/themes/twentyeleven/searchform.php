<?php
/**
 * The template for displaying search forms in Twenty Eleven
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>
    <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
        <label for="s" class="assistive-text">搜索</label>
        <input type="text" name="s" id="keyword" maxlength="255" onblur="if (value ==''){value='请输入关键字'}" onmouseover="this.focus()" onfocus="this.select()" onclick="if(this.value=='请输入关键字') this.value=''" value="请输入关键字" />
        <input id="submit" type="image" alt="站内搜索" src="<?php echo get_template_directory_uri(); ?>/images/search_1.gif" name="sa" />
    </form>
    <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
        <label for="s" class="assistive-text">搜索</label>
        <input type="text" name="s" id="keyword" maxlength="255" onblur="if (value ==''){value='请输入关键字'}" onmouseover="this.focus()" onfocus="this.select()" onclick="if(this.value=='请输入关键字') this.value=''" value="请输入关键字" />
        <input id="submit" type="image" alt="站内搜索" src="<?php echo get_template_directory_uri(); ?>/images/search_2.gif" name="sa" />
    </form>
