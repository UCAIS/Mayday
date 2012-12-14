=== Plugin Name ===
Contributors: eriksef
Donate link: http://www.sorben.org/really-static/index.html#donate
Tags: cache, html, wp-super-cache, wp-cache, cacheing, performance, speed, cdn, wp cache, super cache, ftp, Post, admin, posts, plugin, comments, images, links, page, rss, widget
Requires at least: 2.5.0
Tested up to: 3.4
Stable tag: 0.31

Generates static HTML-files from your Blog. That makes your Blog incredible fast! Better than any other cache.

== Description ==
	
Really-static generates static html-files out of your blog. Every time a Post is published/edited/delted or a comment is posted, changes will be automaticly written to the static blog.

**NEW BETA-Version**

At [our forum](http://really-static-support.php-welt.net/development-f9.html) you can download the newest version. If you have problems, informations about bugs or ideas [report them to us](http://really-static-support.php-welt.net/bugs-f8.html).

**Advantages:**

 * its incredible fast, faster than any cache solution
 * saving static files via local, FTP, SFTP
 * its secure, because you can hide your wordpressinstallation
 * if you dont have PHP/MySQL support on your server you can host the wordpressinstallation local

 
http://blog.phpwelt.net using really-static, so you can see how it works !

**Credits:**

 + English
 + translation to German 
 + (translation to Spanish by <a href="http://sigt.net/">Hector Delcourt</a>)
 + (translation to Russian by <a href="http://www.comfi.com">M.Comfi</a>)
 + (translation to belorussian by <a href="http://www.antsar.info">ilyuha</a>)

**Premiumversion**

 + auto-config
 + coustom aboutinfo
 + no Donateinfo

== Installation ==

Really-static is preconfigured, so you just need to activate this plugin. All generatet files are stored in a folder called "static". This ensures that you can test really-static as long as you want without somebody sees it.

== Frequently Asked Questions ==
= Questions and Bug reports =
If you got any Problem please use the debugfunktion and send me a report.
Or ask the community at [Facebook](http://really-static-support.php-welt.net/bugs-f8.html)


= I just get 0-Byte files = 
 please check your settings. 0-Byte files means that really static get errors while reading. 

= make RSS, Sitemap e.g. static =
http://blog.phpwelt.net/303-how-to-make-rss-files-static.html 

= Cachehit ? =
I implement this because its unessary update static page when the sourcefile didnt change. Its also better for Google, because if the static file dont change, the filedate also keeps the same and google ranks files that didnt change for a long time better!

= I only got webspace without PHP-support =
Download the free [Uniform Server](http://sourceforge.net/projects/miniserver/files/MiniServer/MiniServer_%20Wordpress/mini_server_16_wordpress_v1_1.zip/download "More Informations") that inlucdes a local on you PC runnig Webserver with installed Wordpress. Install an configuarte Really-Static Plugin and 
[Disqus Comment Plugin](http://wordpress.org/extend/plugins/disqus-comment-system/ "More Informations").

If you want to posting a new blogentry, start the Uniform Server on your PC. Than login to the now local on you PC runnig Wordpressinstallation and write your blogentry an puplish it. After this you can stop the uniform server. If someone getting on you internetwebsite he sees the static html files (generatet by really static) and he can post comments (because Disqus got there ownservers)

= I want additional features in Really-static =
Programing your own really-static plugins! http://really-static-support.php-welt.net/hooks-for-writing-your-own-plugin-t17.html

= Make Really-Static work with other Wordpressplugins =
$url= filename without siteprefix, that means just e.g.: "nonwordpresspage.html"

**Make a page static:**
> reallystaticsinglepagehook($url);

**Delete a page:**
> reallystaticsinglepagedeletehook($url);

> reallystaticdeletepage("");

== Screenshots ==

1. Statitics from Google sitemaps (1=No Cache,2=WP Super Cache,3=really static)
2. :-) large Picture: http://wordpress.org/extend/plugins/really-static/screenshot-2.jpg
3. large Picture: http://wordpress.org/extend/plugins/really-static/screenshot-3.jpg
4. blog.domain.com = place where the non-static blog lies and www.domain.com =  the location of the static one; larger Picture: http://wordpress.org/extend/plugins/really-static/screenshot-4.jpg

== Changelog ==

for changelog please look in to our [forum](http://really-static-support.php-welt.net/development-f9.html)

== Translations ==
really-static comes with various translations, located in the directory languages. if you would like to add a new translation, just take the file default.po and edit it to add your translations (e.g. with poedit).

== Upgrade Notice ==
added SFTP upload