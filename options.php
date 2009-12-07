<?php

$paged_val = get_query_var('paged');
$paged_val = $paged_val ? "&paged=$paged_val" : '';

$ck_filter_mini_posts_from_loop = get_settings('filter_mini_posts_from_loop') == 1 ? 'checked="checked" ' : '';
$ck_suppress_autop_on_mini_posts = get_settings('suppress_autop_on_mini_posts') == 1 ? 'checked="checked" ' : '';
$ck_filter_mini_posts_from_feeds = get_settings('filter_mini_posts_from_feeds') == 1 ? 'checked="checked" ' : '';

?>
<div class="wrap">
<div id="icon-options-general" class="icon32">
    <br/>
</div>
<h2><?php _e('Minipost Options', 'MiniPosts') ?></h2>
<form method="post" action="">
    <table width="100%" cellspacing="2" cellpadding="5" class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e('Filtering', 'MiniPost') ?></th>
            <td>
                <label for="filter_mini_posts_from_loop"><input type="checkbox" name="filter_mini_posts_from_loop" id="filter_mini_posts_from_loop" <?php echo $ck_filter_mini_posts_from_loop ?> value="1" /> <?php _e('Hide miniposts from the Loop', 'MiniPosts') ?></label><br />
                <label for="filter_mini_posts_from_feeds"><input type="checkbox" name="filter_mini_posts_from_feeds" id="filter_mini_posts_from_feeds" <?php echo $ck_filter_mini_posts_from_feeds ?> value="1" /> <?php _e('Hide miniposts from subscription feeds', 'MiniPosts') ?></label><br />
                <p/>
                <label for="miniposts_maximum"><input type="text" name="miniposts_maximum" id="miniposts_maximum" size="2" value="<?php echo get_option('miniposts_maximum')?>"/> <?php _e('Maximum number of miniposts to display. 0 (default) displays all miniposts', 'MiniPosts') ?></label> 
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e('Auto-paragraphing:', 'MiniPosts') ?></th>
            <td><label for="suppress_autop_on_mini_posts"><input type="checkbox" name="suppress_autop_on_mini_posts" id="suppress_autop_on_mini_posts" <?php echo $ck_suppress_autop_on_mini_posts ?> value="1" /> <?php _e('Suppress auto-paragraphing on mini posts in the Loop', 'MiniPosts') ?></label></td>
        </tr>

        <tr valign="top">
            <th width="25%" scope="row"><?php _e('Widget title', 'MiniPosts') ?></th>
            <td>
                <input type="text" name="miniposts_title" value="<?php 
                    echo htmlspecialchars(get_option("miniposts_title"));
                    ?>" size="60">
                    <div class="miniposts_format_options">
                        The name to display at the top of the miniposts 
                        widget.
                    </div>
            </td>
        </tr>
            
        <tr valign="top">
            <th scope="row"><?php _e('Minipost appearance', 'MiniPosts') ?></th>
            <td><label for="suppress_autop_on_mini_posts"><input type="checkbox" name="suppress_autop_on_mini_posts" id="suppress_autop_on_mini_posts" <?php echo $ck_suppress_autop_on_mini_posts ?> value="1" /> <?php _e('Prevent automatic paragraph breaks in miniposts', 'MiniPosts') ?></label>

                <p/>
                Control the appearance of each minipost by editing the display
                format.

                <div style="padding-left: 3ex;">
                <textarea name="miniposts_format" cols="80"><?php 
                    echo htmlspecialchars(get_option("miniposts_format"));
                ?></textarea>
                <br>
                <div class="miniposts_format_options" style="padding-left: 3ex;">
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
                </div>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php _e('More appearance', 'MiniPosts') ?></th>
            <td>
                <input type="text" name="miniposts_more_text" value="<?php 
                    echo htmlspecialchars(get_option("miniposts_more_text"));
                    ?>" size="60">
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
            <th scope="row"><?php _e('Date Format', 'MiniPosts') ?></th>
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

if ($_GET["miniposts_updated"] == "true"):
?>
    <div class="updated"><p><strong><?php _e('Mini posts updated.', 'MiniPosts') ?></strong></p></div>
<?php
endif;
?>
