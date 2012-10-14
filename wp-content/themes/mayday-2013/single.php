<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header('single'); ?>


<div id="content">
	<div id="left">
    <?php get_header('nav'); ?>o
	</div>
	<div id="center">
		<div id="article-detail">
			<div id="article-nav">
    <?php
            if (function_exists('get_breadcrumbs')){
                get_breadcrumbs();
            }
     ?>

			</div>
			<div id="article-content">
            <?php while ( have_posts() ) : the_post(); ?>
				<div class="hd">
					<h1><?php the_title(); ?></h1>
					<div class="info">
                    <?php printf('<span class="writer" rel="author">作者：<a href="%1$s" title="%2$s">%3$s</a></span><span class="pub-time">更新时间：2011-12-2</span>',
                    		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
                    		sprintf('查看%s所有的文章', get_the_author() ),
                    		esc_html( get_the_author() ),
                            esc_html( get_the_date() )
                    	); 
                    ?>
					</div>
				</div>
				<div class="bd">
                <?php the_content(); ?>
				</div>
            <?php endwhile; // end of the loop. ?>
			</div>
		</div><!-- #article-detail end -->
	</div>

	<div id="article-footer">
		
	</div>
</div><!-- #content end -->

<?php get_footer('single'); ?>