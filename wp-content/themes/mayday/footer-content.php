<?php
/**
 * The Footer widget areas for Index.
 *
 * @package USTH
 * @subpackage Glowin
 * @since Mayday alpha0.1
 */
?>

<div id="footer">
			<?php
				/* A sidebar in the footer? Yep. You can can customize
				 * your footer with three columns of widgets.
				 */
				get_sidebar( 'footer' );
			?>
	<span class="foot_img" style="float: left; margin-left: 20px;"><img alt="黑龙江科技学院" src="<?php echo get_template_directory_uri(); ?>/images/logo.gif" align="absMiddle" height="42" width="153"></span>
	<ul style=" margin-left: 150px;">
		<li>哈尔滨松北区糖厂街1号 P.C:150027  <a href="http://www.miibeian.gov.cn/" target="_blank">黑ICP备05006845号</a><a class="foot_href" href="mailto:WebMaster@USTH.edu.cn"><img align="absmiddle" src="<?php echo get_template_directory_uri(); ?>/images/email.gif" alt="意见反馈" /></a></li>
		<li>Copyright © 2009&nbsp;&nbsp;<a title="网络中心" href="http://inc.usth.net.cn/" target="_blank">黑龙江科技学院-信息网络中心</a></li>
	</ul>
</div><!-- #footer end -->

</div><!-- page-container -->
<script type="text/javascript">
/*-- nav toggle --*/
$(function(){
  $('.menu-item').hover(
  function(){
	  $(this).find('a:first').addClass("hover");
	  var current_li=$(this);
	  NavWaitSlide = setTimeout(function() {
		  if(!$(current_li).children('ul').is(':visible'))
		  {
				$(current_li).find('ul').slideDown(200);
		  }
	  },100)
  },
function(){
	  clearTimeout(NavWaitSlide);
	  $(this).find('ul').slideUp(100);
	  $(this).find('a:first').removeClass("hover");
  }
);
}
);
</script>

<?php wp_footer(); ?>

</body>
</html>