=== Plugin Name ===
Contributors: erigami
Tags: aside, minipost, linkblog, widget, twitter
Requires at least: 2.0.2
Tested up to: 2.7
Stable tag: 0.6.14

Create small posts outside the main flow of your blog. Integrates into any theme that supports widgets.


== Description ==

This plugin allows you to mark any post as a minipost. Miniposts are shown 
in a widget on your main page (or elsewhere). Features:

* Integrates easily into any theme that supports widgets
* Complete control of minipost appearance. Show the title, text, comment count, post date, permalink, and custom 'more' text. 
* Posts created with QuickPress admin widget can be marked as miniposts.
* Miniposts will show a teaser if the post has an excerpt or a <code>&lt;!--more--&gt;</code> tag.
* API to integrate minipost display into non-widgetty themes

*NOTE:* Version 0.6.8 and up requires Wordpress 2.7. 

== Installation ==

1. Download and unzip. 
1. Copy the miniposts directory into your [wordpress-install]/wp-content/plugins directory.
1. Activate the "Miniposts" plugin in your administrator panel.
1. Add the "Miniposts" widget to your theme's sidebar.


== Frequently Asked Questions ==

= What is the non-widget API? =

MiniPosts2 provides two functions, <code>is_mini_post()</code>, and <code>get_mini_posts()</code>. 

**<code>is_mini_post()</code>** - This function takes no arguments and can only be called from within the Loop. Returns *true* if the current post is a minipost, and *false* otherwise. 

**<code>get_mini_posts($format = null, $limit = null)</code>** - This function displays the existing miniposts. If either of the arguments are specified as non-null then the display is changed. The *$format* variable is used to specify a format, over-riding the values specified on the Options page.


== Changelog ==

= 0.6.13 =
* Incremented version number in miniposts.php.

= 1.0 =
* Changed versioning scheme to avoid wordpress.org bug.

= 0.6.12 =
* Fixed permissions check in post save. Non-admin users should now be able to create miniposts (thanks for finding this one, [Chris](http://www.theblackrepublican.net/)).

= 0.6.11 =
* Added styling on the comment count. Allows [arbitrary text to be shown for the zeroeth comment link](http://wordpress.org/support/topic/230058?replies=15). Added at the request of [Lovelidicious](http://wordpress.org/support/profile/1524092).
