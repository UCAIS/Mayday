<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package USTH
 * @subpackage Glowin
 * @since Mayday alpha0.1
 */

get_header('index'); ?>
<div id="content">
    <div id="left" style="background: url(<?php echo get_template_directory_uri(); ?>/images/0<?php echo mt_rand(1, 5);?>.gif) no-repeat right 130px;">
        <object data="<?php echo get_template_directory_uri(); ?>/images/school_motto.swf" type="application/x-shockwave-flash" width="300" height="120">
            <param name="movie" value="<?php echo get_template_directory_uri(); ?>/images/school_motto.swf" />
            <param name="wmode" value="transparent"/>
        </object>
    </div>
    <div id="center">
        <div id="news-list">
            <h2><a href="#" class="news-title">科院新闻</a><span style="font-family: ;"><a href="#" class="more">更多新闻</a></span></h2>
            <ul>
            <?php
            $query_news = array(
            'category__not_in' => array( 1, 4, 7),
            'orderby' => date,
            'showposts' => 7
            );
            query_posts( $query_news );
            
            if ( have_posts() ) :

                  /* Start the Loop */
                while ( have_posts() ) : the_post();
            ?>
            <li><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a><span><?php echo esc_attr( get_the_date( 'm-j' ) ) ?></span></li>
            <?php
                endwhile;
            ?>

            <?php else : ?>
            <li><a href="http://www.usth.edu.cn">哇哦，网站暂时没有最新新闻</a></li>
            <?php endif;
            // reset query
                wp_reset_query();
            ?>
            </ul>
        </div><!-- #news-list end -->
        <div id="search">
        <?php get_search_form(); ?>
        </div><!-- #search end -->
        <div id="websites-list">
            <div class="lib_Menubox lib_tabborder">
              <ul>
                <li class="tit_bj_left"></li>
                <li id="one1" onclick="setTab('one',1,4)" class="hover"><span>专题网站</span></li>
                <li id="one2" onclick="setTab('one',2,4)" class=""><span>科院特色</span></li>
                <li id="one3" onclick="setTab('one',3,4)" class=""><span>发展见证</span></li>
                <li id="one4" onclick="setTab('one',4,4)" class=""><span>热点关注</span></li>
              </ul>
            </div>
            <div class="lib_Contentbox lib_tabborder">
            <div id="con_one_1" class="text_div" style="display: block; ">
                <table width="340" border="0">
                    <tbody>
                    <tr>
                        <td><a href="http://cxzyzt.usth.net.cn/" target="_blank">创先争优活动专题网站</a></td>
                        <td><a href="http://222.171.107.124:60080/" target="_blank">干部在线学习中心</a></td>
                    </tr>
                    <tr>
                        <td><a href="http://xyq.usth.net.cn/" target="_blank">黑龙江校研企网络平台</a></td>
                        <td><a href="http://xcbzt.usth.net.cn/" target="_blank">学习型党组织建设专题网站</a></td>
                    </tr>
                    <tr>
                        <td><a href="http://www.hljnews.cn/fou_zt/dzwxz.html" target="_blank">“党在我心中”专题网站</a></td>
                        <td><a href="http://222.171.107.122:81/" target="_blank">高教强省与东部煤电化</a></td>
                    </tr>
                    </tbody>
                </table>         
            </div>
            <div id="con_one_2" class="text_div" style="display: none; ">
                <table width="340" border="0">
                    <tbody>
                    <tr>
                    <td><a href="http://dianqi.jpk.usth.net.cn/" target="_blank">电气工程实验与实践中心</a></td>
                    <td><a href="http://sxzx.usth.net.cn/" target="_blank">工程训练与基础实验中心</a></td>
                    </tr>
                    <tr>
                    <td><a href="http://zhizao.usth.net.cn/" target="_blank">现代制造工程中心</a></td>
                    <td><a href="http://kyyjy.usth.net.cn" target="_blank">黑龙江矿业研究院</a></td>
                    </tr>
                    <tr>
                    <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div id="con_one_3" class="text_div" style="display: none; ">
                <table width="340" border="0">
                <tbody>
                    <tr>
                        <td><a href="http://218.7.13.212/main/fzjs/shipin.html" target="_blank">与时俱进的科技学院</a></td>
                        <td> <a href="http://218.7.13.212/shiyiwu/xwdt/Article/201103/news.html" target="_blank">"十一五"发展的主要成效</a></td>
                    </tr>
                </tbody>
                </table>
            </div>
            <div id="con_one_4" class="text_div" style="display: none; ">
                <table width="340" border="0">
                <tbody>
                <tr>
                    <td><a href="http://usth.cn/videonews/" target="_blank">黑龙江科技学院视频新闻</a></td>
                    <td><a href="http://usth.cn/main/meiti/2005.html" target="_blank">媒体上的黑龙江科技学院</a></td>
                </tr>
                </tbody>
                </table>
            </div>
            </div>
        </div><!-- #websites-list end -->
    </div>
    <div id="right">
        <div id="pic_news">
            <div id="pic_news_info"></div>
            <ul>
                <li class="on">1</li>
                <li>2</li>
                <li>3</li>
                <li>4</li>
            </ul>
            <div id="pic_news_list">
            <?php
            
            if ( have_posts() ) :
                $pic_args = 0;
                  /* Start the Loop */
                while ( have_posts() ) : the_post();
                if ( has_post_thumbnail() ) :
                $pic_title = get_the_title();
                ?>
                <a href="<?php the_permalink(); ?>" target="_blank"><?php the_post_thumbnail( array('title' => $pic_title, 'alt' => $pic_title) ); ?></a>
            <?php
                $pic_args ++;
                if( $pic_args >3) { break;}
                
                endif;
                endwhile;
            ?>

            <?php else : ?>
            <a href="http://www.usth.edu.cn"><img src="<?php echo get_template_directory_uri(); ?>/images/p1.jpg" title="没有图片新闻哦" alt="没有图片新闻" /></a>
            <?php endif; ?>
            </div>
        </div>
        <div id="notice">
            <h2><a href="#" class="notice-title">公告信息</a><span><a href="#" class="more">详细</a></span></h2>
            <div id="notice-ul">
            <ul>
            <?php 
            $query_announce = array(
            'category__in' => array(4),
            'orderby' => date,
            'showposts' => 8
            );
            query_posts( $query_announce );
            
            if ( have_posts() ) :

                  /* Start the Loop */
                while ( have_posts() ) : the_post();
            ?>
            <li><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></li>
            <?php
                endwhile;
            ?>

            <?php else : ?>
            <li><a href="http://www.usth.edu.cn">哇哦，网站暂时没有最新新闻</a></li>
            <?php endif; 
            // reset query
                wp_reset_query();
            ?>
            </ul>
            </div>
        </div><!-- #notice end -->
        <div id="information">
            <h2>信息资源</h2>
            <ul>
                <li><a href="#">网络教学</a></li>
                <li><a href="#">英语在线</a></li>
                <li><a href="#">教学资源</a></li>
                <li><a href="#">学生成绩</a></li>
                <li><a href="#">财务查询</a></li>
                <li><a href="#">资产管理</a></li>
                <li><a href="#">网上预订</a></li>
                <li><a href="#">精品课程</a></li>
                <li><a href="#">校园卡通</a></li>
                <li><a href="#">电费查询</a></li>
            </ul>
        </div>
    </div><!-- #right end -->
    <div id="water">
        <object data="<?php echo get_template_directory_uri(); ?>/images/water.swf" type="application/x-shockwave-flash" width="1000" height="60">
            <param name="movie" value="<?php echo get_template_directory_uri(); ?>/images/water.swf" />
            <param name="wmode" value="transparent"/>
        </object>
    </div>
</div><!-- #content end -->

<?php get_footer('index'); ?>