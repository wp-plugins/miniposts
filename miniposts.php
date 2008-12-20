<?php
/*
Plugin Name: Miniposts
Plugin URI: http://www.piepalace.ca/blog/projects/miniposts/
Description: An approach to "asides", or small posts. Allows you to mark entries as "mini" posts and handle them differently than normal posts. 
Version: 0.6.8
Author: Morgan Doocy and e
Author URI: http://piepalace.ca/blog/
*/

/*
MiniPosts - Small posts, or "asides," plugin for WordPress (http://wordpress.org).
Copyright (C) 2005  Morgan Doocy
Copyright (C) 2008  erigami@piepalace.ca

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*
Changelog
=========
0.1a   - Initial release.
0.2    - Corrected load_plugin_textdomain() domain.
0.3    - Updated to accommodate WP pages.
0.4    - Updated to accommodate feeds; added option to filter mini posts from feeds.
0.5    - Added support for 0-, 1-, and n-comment formatting to get_mini_posts().
        (WARNING: The argument sequence of get_mini_posts() has changed to accommodate this.)
0.5.1  - Added 'save_post' hook
0.5.2  - Fixed label id for "This is a mini post" checkbox
       - Added pagination on Options page
       - Changed all references to the plugin's filename to basename(__FILE__), to avoid file naming issues
       - Added aliases to JOIN and WHERE clauses to avoid collisions with other plugins
        (thanks, Jerome and Mark!)

0.6.0  - Stolen by e. Added widget wrapper for the sidebar, and updated the 
         post callbacks to display a dbx element.
0.6.1  - Fixed bug preventing comment counts from being displayed. 
0.6.2  - Fixed bug that included unapproved comments in comment counts.
       - Added the %date% field, and the date format option. 
0.6.3  - Added excerpt/more support
       - Made Asides appear in search results
       - Made Aside title editable
0.6.4  - Fix error from occuring when widget plugin is not installed.
       - Fix error due to WP error http://wordpress.org/support/topic/102423
0.6.5  - Simplified mini_posts_where() and mini_posts_join(). Now only 
            removes posts when is_home() is true. Stops pages from 
            disappearing from admin view. 
0.6.6  - Added global keyword to allow loading on WP 2.5, fixed 
            mini_posts_where() to filter posts from subscription feeds, 
            fixed up meta box for post edit page to look more WP2.5y.
0.6.7  - Rejigged code so that the minipost box on the post page would 
            behave properly.
       - Added Nathan's smiley fix. 
0.6.8  - Prevent warning message when smileys aren't enabled. 
       - Prevent multiple '_mini_post' meta values from being added to the 
            db (for real this time, honest)
       - Cleaned up JOIN/WHERE clauses so things 
*/

define('MINIPOST_ID', "Miniposts"); // Id for the wp_*_widget() fns

global $wpdb;
// WordPress 1.5 includes this file again on the options page. Avoid duplicate
// declaration:
if ( function_exists("is_plugin_page") && is_plugin_page() ) {
    include(ABSPATH.'wp-blog-header.php');

    $paged_val = get_query_var('paged');
    $paged_val = $paged_val ? "&paged=$paged_val" : '';
    
    if (isset($_POST["update_options"])) {
        $errors = array();

        update_option('filter_mini_posts_from_loop', $_POST['filter_mini_posts_from_loop'] == 1 ? 1 : 0);

        update_option('suppress_autop_on_mini_posts', $_POST['suppress_autop_on_mini_posts'] == 1 ? 1 : 0);

        update_option('filter_mini_posts_from_feeds', $_POST['filter_mini_posts_from_feeds'] == 1 ? 1 : 0);
        
        if (ctype_digit($_POST['miniposts_maximum'])) {
            update_option('miniposts_maximum', $_POST['miniposts_maximum']);
        }
        else {
            $errors[] = __("Maximum number of miniposts must be a number.");
        }
        
        update_option('miniposts_format', stripslashes($_POST['miniposts_format']));
        
        update_option('miniposts_date_format', stripslashes($_POST['miniposts_date_format']));
        
        update_option('miniposts_more_text', stripslashes($_POST['miniposts_more_text']));

        update_option('miniposts_title', stripslashes($_POST['miniposts_title']));

        if (sizeof($errors) == 0) {
            echo '<div class="updated"><p><strong>' . __('Options saved.', 'MiniPosts') . '</strong></p></div>';
        } else {
            echo '<div class="error"><p><strong>';
            echo __('Options partially saved. Error in the data submitted: ', 'MiniPosts');
            foreach ($errors as $e) {
                echo '<li>';
                echo $e;
                echo "</li>\n";
            }
            echo '</strong></p></div>';
        }
    }
    
    $ck_filter_mini_posts_from_loop = get_settings('filter_mini_posts_from_loop') == 1 ? 'checked="checked" ' : '';
    $ck_suppress_autop_on_mini_posts = get_settings('suppress_autop_on_mini_posts') == 1 ? 'checked="checked" ' : '';
    $ck_filter_mini_posts_from_feeds = get_settings('filter_mini_posts_from_feeds') == 1 ? 'checked="checked" ' : '';
    
    ?>
    <div class="wrap">
    <h2><?php _e('Mini Post Options', 'MiniPosts') ?></h2>
    <form method="post" action="./options-general.php?page=<?php echo basename(__FILE__) . $paged_val; ?>">
        <table width="100%" cellspacing="2" cellpadding="5" class="editform">
            <tr valign="top">
                <th width="33%" scope="row"><?php _e('Show/hide:', 'MiniPost') ?></th>
                <td>
                    <label for="filter_mini_posts_from_loop"><input type="checkbox" name="filter_mini_posts_from_loop" id="filter_mini_posts_from_loop" <?php echo $ck_filter_mini_posts_from_loop ?> value="1" /> <?php _e('Filter mini posts from the Loop', 'MiniPosts') ?></label><br />
                    <label for="filter_mini_posts_from_feeds"><input type="checkbox" name="filter_mini_posts_from_feeds" id="filter_mini_posts_from_feeds" <?php echo $ck_filter_mini_posts_from_feeds ?> value="1" /> <?php _e('Filter mini posts from subscription feeds', 'MiniPosts') ?></label><br />
                </td>
            </tr>

            <tr valign="top">
                <th width="33%" scope="row"><?php _e('Maximum number of miniposts to display', 'MiniPost') ?></th>
                <td>
                    <label for="miniposts_maximum"><input type="text" name="miniposts_maximum" id="miniposts_maximum" size="4" value="<?php echo get_option('miniposts_maximum')?>"/> <?php _e('Maximum number of miniposts to display. 0 (default) displays all miniposts', 'MiniPosts') ?></label> </td>
            </tr>

            <tr valign="top">
                <th width="33%" scope="row"><?php _e('Auto-paragraphing:', 'MiniPosts') ?></th>
                <td><label for="suppress_autop_on_mini_posts"><input type="checkbox" name="suppress_autop_on_mini_posts" id="suppress_autop_on_mini_posts" <?php echo $ck_suppress_autop_on_mini_posts ?> value="1" /> <?php _e('Suppress auto-paragraphing on mini posts in the Loop', 'MiniPosts') ?></label></td>
            </tr>

            <tr valign="top">
                <th width="33%" scope="row"><?php _e('Minipost Title', 'MiniPosts') ?></th>
                <td>
                    <input type="text" name="miniposts_title" value="<?php 
                        echo htmlspecialchars(get_option("miniposts_title"));
                        ?>">
                        <div class="miniposts_format_options">
                            The name to display at the top of the miniposts 
                            widget.
                        </div>
                </td>
            </tr>
                
            <tr valign="top">
                <th width="33%" scope="row"><?php _e('Minipost Format', 'MiniPosts') ?></th>
                <td>
                    <textarea name="miniposts_format" cols="80"><?php 
                        echo htmlspecialchars(get_option("miniposts_format"));
                    ?></textarea>
                    <br>
                    <div class="miniposts_format_options">
                        <b>%date%</b> - The date the minipost was 
                                posted. <br/>
                        <b>%title%</b> - The title of the minipost<br/>
                        <b>%post%</b> - The text of the post<br/>
                        <b>%commentcount%</b> - The number of comments for the
                        post<br/>
                        <b>%permalink%</b> - The permalink to the post<br/>
                        <b>%more%</b> - Text to be displayed if the post has 
                        more content than shown. 
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th width="33%" scope="row"><?php _e('More Format', 'MiniPosts') ?></th>
                <td>
                    <input type="text" name="miniposts_more_text" value="<?php 
                        echo htmlspecialchars(get_option("miniposts_more_text"));
                        ?>">
                    <br>
                    <div class="miniposts_format_options">
                        The format of the text to be shown in place of the 
                        <tt>%more%</tt> keyword listed above. This keyword is 
                        shown if you use the <i>Optional Excerpt</i> or 
                        <tt>&lt;!--more--&gt;</tt> in your post. You may use 
                        any other keyword in this text. 
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th width="33%" scope="row"><?php _e('Date Format', 'MiniPosts') ?></th>
                <td>
                    <input type="text" name="miniposts_date_format" value="<?php 
                        echo htmlspecialchars(get_option("miniposts_date_format"));
                        ?>">
                    <br>
                    <div class="miniposts_format_options">
                                The format of the date to display.
                                To set your own, see the list of 
                                <a href="http://ca.php.net/manual/en/function.date.php">standard PHP 
                                <code>date()</code> arguments</a>.
                    </div>
                </td>
            </tr>
        </table>
        <div class="submit"><input type="submit" name="update_options" value="<?php _e('Update Options', 'MiniPosts') ?> &raquo;" /></div>
    </form>
    </div>
    <?php
    
    if ($_GET["miniposts_updated"] == "true")    {
        
    ?>
    <div class="updated"><p><strong><?php _e('Mini posts updated.', 'MiniPosts') ?></strong></p></div>
    <?php
    
    }
} else {
    
    load_plugin_textdomain('MiniPosts');
    
    add_option('filter_mini_posts_from_loop', 1);
    add_option('suppress_autop_on_mini_posts', 1);
    add_option('filter_mini_posts_from_feeds', 0);
    
    add_option('miniposts_format', '<p class="minipost"><a href="%permalink%" class="title">%title%</a><br/> %post% %more% (%commentcount%)</p>', 'Format to use when displaying options.');

    add_option('miniposts_date_format', 'Y/n/j', 'Date format.');
    
    add_option('miniposts_more_text', '<a href="%permalink">More...</a>', 'Text to display in place of "%more" when displaying multipart asides.');
    
    add_option('miniposts_title', 'Miniposts 0.6.8', 'The title to display at the top of the minipost widget.');
    
    add_option('miniposts_maximum', '0', 'The maximum number of miniposts to display. 0 is infinite.');
    
    /** Get the number of comments associted with the named post. Originally 
     * written by Mountain Dew Virus:
     * http://dev.wp-plugins.org/file/comment-count/trunk/comment-count.php
     *
     * Heavily modified from the original. 
     */
    function miniposts_get_comment_count($post) {
    
        global $wpdb;
        $request = "SELECT COUNT(*) FROM $wpdb->comments WHERE 
            comment_post_ID='" . $wpdb->escape($post) . "' AND " 
            . " comment_approved='1'";

        return $wpdb->get_var($request);
    } 
    
    
    function is_mini_post() {
        global $post;
        return (bool) get_post_meta($post->ID, '_mini_post', true);
    }

    /**
     * Return the text to display for a minipost. The preferred order is:
     * <ol>
     *  <li/>post_excerpt - If the post has an excerpt, this is returned.
     *  <li/>post_content, before first &lt;!--more--&gt; - The text of the 
     *      post before the first <tt>more</tt> tag appears.
     *  <li/>post_content- The text of the post.
     * </ol>
     *
     * @param $minipost An object containing the fields 'post_content' (the 
     *              text of the post) and 'post_excerpt' (the excerpt of 
     *              the post). 
     *
     * @returns An array with the text of the post, and a boolean indicating
     *              if there is more to the post. 
     */
    function miniposts_get_text(&$minipost) {
        // See if we have a post excerpt
        if (!is_null($minipost->post_excerpt) 
            && strlen($minipost->post_excerpt) > 0
            && strlen(trim($minipost->post_excerpt)) > 0
        ) {
            return array($minipost->post_excerpt,true);
        }

        // See if we have a <!--more--> tag
        if (preg_match('/(.*)<!--\\s*more\\s*-->/s', $minipost->post_content, $matches)) {
            return array($matches[1], true);
        }

        return array($minipost->post_content, false);
    }
    
    function get_mini_posts(
            $format = null, 
            $limit = null
        ) {
        global $wp_smiliessearch, $wp_smiliesreplace, $wpdb;  
        
            if (is_null($limit)) {
                $limit = get_option('miniposts_maximum');
            }

        if (0 != (int)$limit ) {
            $limit = (int) $limit;
            $limit = ' LIMIT '.$limit;
        }
        else {
            $limit = '';
        }
        
        
        
        $now = current_time('mysql');
        
        if ($miniposts = $wpdb->get_results("SELECT ID, post_date, UNIX_TIMESTAMP(post_date) AS post_date_unix, post_content, post_excerpt, post_title FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) WHERE post_date < '$now' AND post_status = 'publish' AND $wpdb->postmeta.meta_key = '_mini_post' AND $wpdb->postmeta.meta_value = '1' ORDER BY post_date DESC" . $limit)) {
            if (is_null($format)) {
                $format = get_option("miniposts_format");
            }
                
            $dateFormat = get_option("miniposts_date_format");
            $moreText = get_option("miniposts_more_text");

            echo '<div class="miniposts">';
            foreach ($miniposts as $minipost) {
                if ($minipost->post_date != '0000-00-00 00:00:00') {
                    $url  = get_permalink($minipost->ID);
                    $commenturl = "$url#comments";
                    $title = $minipost->post_title;
                        
                    $count = miniposts_get_comment_count($minipost->ID);
                    $commentcount = "<a class=\"minipost_commentlink\" href=\"$commenturl\" title=\"Comments for '$title'\">$count</a>";

                    if ($title) {
                        $title = strip_tags($title);
                    } else {
                        $title = $minipost->ID;
                    }

                    list($text, $hasMore) = miniposts_get_text($minipost);

                    $moreString = '';
                    if ($hasMore) {
                        $moreString = $moreText;
                    }

                    if (isset($wp_smiliessearch) && sizeof($wp_smiliessearch) > 0) {
                        $text = @preg_replace($wp_smiliessearch, $wp_smiliesreplace, $text);
                    }

                    $text = wptexturize($text);
                    
                    $meta = str_replace('%more%', $moreString, $format);
                    $meta = str_replace('%permalink%', get_permalink($minipost->ID), $meta);
                    $meta = str_replace('%title%', $title, $meta);
                    $meta = str_replace('%date%', date($dateFormat, $minipost->post_date_unix), $meta);
                    $meta = str_replace('%commentcount%', $commentcount, $meta);
                    
                    $text = str_replace('%post%', $text, $meta);
                    
                    echo "\t$text\n";
                }
            }
            
            echo '</div>'; /* class="miniposts" */
        }
    }
    
    function mini_posts_join($text) {
        global $wpdb, $pagenow;

        if ( 
            ( (is_home() || is_plugin_page())
                && get_settings("filter_mini_posts_from_loop") )
            ||
            ( is_feed() && get_settings('filter_mini_posts_from_feeds') ) 
        ) {
            $text .= " LEFT JOIN $wpdb->postmeta AS miniposts_meta ON ($wpdb->posts.ID = miniposts_meta.post_id AND miniposts_meta.meta_key='_mini_post' AND miniposts_meta.meta_value=1)";
        }

        return $text;
    }
    
    function mini_posts_where($text) {

        if (
            (is_home() && get_settings('filter_mini_posts_from_loop'))
            || (is_feed() && get_settings('filter_mini_posts_from_feeds') )
        ) {
            $text .= " AND (miniposts_meta.meta_key IS NULL OR miniposts_meta.meta_value = 0)";
        }
        
        return $text;
    }
    
    if (get_settings('suppress_autop_on_mini_posts')) {
        function mini_post_autop($pee, $br = 1) {
            if (!is_mini_post() || is_single()) {
                $pee = $pee . "\n"; // just to make things a little easier, pad the end
                $pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
                // Space things out a little
                $pee = preg_replace('!(<(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "\n$1", $pee); 
                $pee = preg_replace('!(</(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])>)!', "$1\n", $pee);
                $pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines 
                $pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
                $pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "\t<p>$1</p>\n", $pee); // make paragraphs, including one at the end 
                $pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace 
                $pee = preg_replace('!<p>\s*(</?(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|hr|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
                $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
                $pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
                $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
                $pee = preg_replace('!<p>\s*(</?(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|hr|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)!', "$1", $pee);
                $pee = preg_replace('!(</?(?:table|thead|tfoot|caption|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee); 
                if ($br) $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
                $pee = preg_replace('!(</?(?:table|thead|tfoot|caption|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|math|p|h[1-6])[^>]*>)\s*<br />!', "$1", $pee);
                $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)!', '$1', $pee);
                $pee = preg_replace('!(<pre.*?>)(.*?)</pre>!ise', " stripslashes('$1') .  clean_pre('$2')  . '</pre>' ", $pee);
            }
            return $pee; 
        }
    }
    
    add_filter('posts_where', 'mini_posts_where');
    add_filter('posts_join', 'mini_posts_join');
    if (get_settings('suppress_autop_on_mini_posts')) {
        remove_filter('the_content', 'wpautop');
        add_filter('the_content', 'mini_post_autop');
    }
    
    function plugin_minipost2_sidebar() {
        $is_mini = get_post_meta($_REQUEST['post'], '_mini_post', true);
        $check = $is_mini ? 'checked="checked" ' : '';
        ?>
    <label for="is_mini_post">
      <input type="checkbox" name="is_mini_post" id="is_mini_post" value="1" 
            <?php echo $check; ?> />
        <?php print __('This is a mini post', 'MiniPosts'); ?>
    </label>
    <input type="hidden" name="miniposts_nonce" value="<?php 
        print miniposts_nonce();
    ?>">
    <?php
    }
    

    function plugin_minipost2_update_post($id) {
        if ( current_user_can('edit_post', $post_id) 
            && isset($_POST["miniposts_nonce"]) 
            && $_POST['miniposts_nonce'] == miniposts_nonce()
        ) {
            $setting = (isset($_POST["is_mini_post"]) && $_POST["is_mini_post"] == "1") ? 1 : 0;
            add_post_meta($id, '_mini_post', $setting, true);
        }

        return $post_id;
    }

    
    function plugin_minipost2_admin_menu() {
        add_options_page(__('Mini Post Manager', 'MiniPosts'), __('MiniPosts', 'MiniPosts'), 5, basename(__FILE__));
        add_submenu_page('options-general.php', __('Miniposts'), 5, null, __('MiniPosts', 'MiniPosts'), 5, basename(__FILE__));
    
        add_meta_box('minipostsdiv', __( 'Miniposts'), 
                'plugin_minipost2_sidebar', 'post', 'side' );
    }
    
    add_action('save_post', 'plugin_minipost2_update_post');
    add_action('edit_post', 'plugin_minipost2_update_post');
    add_action('publish_post', 'plugin_minipost2_update_post');
    add_action('admin_menu', 'plugin_minipost2_admin_menu');

    remove_action('edit_form_advanced', 'mini_posts_checkbox');
    remove_action('simple_edit_form', 'mini_posts_checkbox');


    function widget_miniposts2_init() {
        function widget_miniposts2_display($args) {
            extract($args);
            ?>
                <?php echo $before_widget; ?>
                <?php echo $before_title
                . apply_filters('widget_title', get_option("miniposts_title"))
                . $after_title; 
            get_mini_posts(null);
            echo $after_widget;
            ?>
                <?php
        }

    
        if (function_exists("wp_register_sidebar_widget")) {
            wp_register_sidebar_widget(MINIPOST_ID, __('Miniposts'), 'widget_miniposts2_display', array('description' => __('Show asides in your side bar')));
        }
    }
    add_action('plugins_loaded', 'widget_miniposts2_init');
	
    
    /** We need a nonce to prevent an attacker from inserting their own 
     * value for is_mini_post during comment edits. This function returns 
     * that nonce. 
     */
    function miniposts_nonce() {
        // Returns the nonce that we use to defend ourself against 
        //  guessing attacks. 
        $nonce = get_option("miniposts_nonce");

        if (is_null($nonce) || strlen($nonce) == 0) {
            $nonce = crc32( time() . $_SERVER['QUERY_STRING'] 
                . $_SERVER['REMOTE_ADDR'] . $_SERVER['SCRIPT_FILENAME'] 
                );
            update_option("miniposts_nonce", $nonce);
        }

        return $nonce;
    }
}


//add_action('plugins_loaded', 'widget_miniposts_init');
?>
