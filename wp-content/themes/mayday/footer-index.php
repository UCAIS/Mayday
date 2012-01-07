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
	<ul id="office-link">
		<li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/images/office1.gif" alt="办公系统" title="办公系统"/></a></li>
		<li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/images/office2.gif" alt="领导信箱" title="领导信箱"/></a></li>
		<li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/images/office3.gif" alt="部门意见箱" title="部门意见箱"/></a></li>
		<li><a href="#"><img src="<?php echo get_template_directory_uri(); ?>/images/office4.gif" alt="邮件系统" title="邮件系统"/></a></li>
	</ul>
	<ul>
        <li>哈尔滨松北区糖厂街1号 P.C:150027&nbsp;&nbsp;<a href="http://www.miibeian.gov.cn/" target="_blank">黑ICP备05006845号</a>&nbsp;&nbsp;<a id="foot_href" href="mailto:WebMaster@USTH.edu.cn"><img border="0" align="absmiddle" longdesc="mailto://web.hit.edu.cn" src="<?php echo get_template_directory_uri(); ?>/images/email.gif" alt="意见反馈"></a></li>
        <li>Copyright © 2009&nbsp;&nbsp;<a title="信息网络中心" href="http://218.7.13.214:81/" target="_blank">黑龙江科技学院-信息网络中心</a></li>
	</ul>

	<select onchange="if(this.options[this.selectedIndex].value!=''){window.open(this.options[this.selectedIndex].value,'_blank');}" name="FriendSite2">
		<option value="" selected="">----------友情链接----------</option>
        <?php
        $index_bookmarks = get_bookmarks( array(
        'orderby' => 'name',
        ));
        foreach ( $index_bookmarks as $bm) {
            printf('<option value="%s">%s</option>', $bm->link_url, $bm->link_name);
        }
        ?>      
	</select>
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

//Tab Toggle
function setTab(name,cursel,n){
	for(i=1;i<=n;i++){
		var menu=document.getElementById(name+i);
		var con=document.getElementById("con_"+name+"_"+i);
		menu.className=i==cursel?"hover":"";
		con.style.display=i==cursel?"block":"none";
	}
}

//picture news show
var t = n = 0, count;
$(document).ready(function(){	
	count=$("#pic_news_list a").length;
	$("#pic_news_list a:not(:first-child)").hide();
	$("#pic_news_info").html($("#pic_news_list a:first-child").find("img").attr('alt'));
	$("#pic_news_info").click(function(){window.open($("#pic_news_list a:first-child").attr('href'), "_blank")});
	$("#pic_news li").click(function() {
		var i = $(this).text() - 1;
		n = i;
		if (i >= count) return;
		$("#pic_news_info").html($("#pic_news_list a").eq(i).find("img").attr('alt'));
		$("#pic_news_info").unbind().click(function(){window.open($("#pic_news_list a").eq(i).attr('href'), "_blank")})
		$("#pic_news_list a").filter(":visible").fadeOut(500).parent().children().eq(i).fadeIn(1000);
		document.getElementById("pic_news").style.background="";
		$(this).toggleClass("on");
		$(this).siblings().removeAttr("class");
	});
	t = setInterval("showAuto()", 4000);
	$("#pic_news").hover(function(){clearInterval(t)}, function(){t = setInterval("showAuto()", 4000);});
})

function showAuto()
{
	n = n >=(count - 1) ? 0 : ++n;
	$("#pic_news li").eq(n).trigger('click');
}
/*--- information animation  --*/
function AutoScroll(obj){
        $(obj).find("ul:first").animate({
                marginTop:"-23px"
        },500,function(){
                $(this).css({marginTop:"0"}).find("li:first").appendTo(this);
        });
}
$(document).ready(function(){
setInterval('AutoScroll("#notice")',4000)
});

</script>

<?php wp_footer(); ?>

</body>
</html>