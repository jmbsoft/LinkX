<?php
if( !defined('LINKX') ) die("Access denied");

$defaults = array('base_url' => "http://{$_SERVER['HTTP_HOST']}" . preg_replace('~/admin/index\.php.*~', '', $_SERVER['REQUEST_URI']),
                  'cookie_domain' => preg_replace('~www\.~', '', $_SERVER['HTTP_HOST']),
                  'page_new' => 'new.html',
                  'page_popular' => 'popular.html',
                  'page_top' => 'top.html',
                  'page_details' => 'l%d.html',
                  'extension' => 'html',
                  'date_format' => 'm-d-Y',
                  'time_format' => 'h:i:s',
                  'dec_point' => '.',
                  'thousands_sep' => ',',
                  'min_desc_length' => 10,
                  'max_desc_length' => 500,
                  'min_title_length' => 10,
                  'max_title_length' => 200,
                  'max_keywords' => 10,
                  'link_weight' => '1.00',
                  'min_comment_length' => 10,
                  'max_comment_length' => 500,
                  'comment_delay' => 60,
                  'max_rating' => 5,
                  'font_dir' => "{$GLOBALS['BASE_DIR']}/fonts",
                  'min_code_length' => 4,
                  'max_code_length' => 6,
                  'cache_index' => '43200',
                  'cache_category' => '43200',
                  'cache_new' => '21600',
                  'cache_popular' => '21600',
                  'cache_top' => '21600',
                  'cache_search' => '3600',
                  'cache_details' => '3600');

if( !isset($C['from_email']) )
{
    $C = array_merge($C, $defaults);
}

define('NODTD', TRUE);
include_once('includes/header.php');
?>

<script language="JavaScript">
$(function()
{
      $('#form').bind('submit', function()
                                {
                                    $('input[@type=checkbox]').each(function()
                                                       {
                                                           if( !this.checked )
                                                           {
                                                               $('#form').append('<input type="hidden" name="'+this.name+'" value="0">');
                                                           }
                                                       });
                                });
});
</script>

<div id="info"></div>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">

    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/settings.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Use this page to adjust the software's general settings
    </div>

        <?php if( isset($GLOBALS['no_access_list']) ): ?>
        <div style="text-align: center; margin-top: 10px; margin-bottom: 5px;">
          <div style="text-align: left; margin-left: auto; margin-right: auto;" class="warn">
          ENHANCED SECURITY: You have not yet setup an access list, which will add increased security to your control panel.
          <a href="docs/access-list.html" target="_blank"><img src="images/help-small.gif" border="0" width="12" height="12"></a>
          </div>
        </div>
        <?php endif; ?>

        <?php if( $GLOBALS['message'] ): ?>
        <div class="notice margin-bottom">
          <?php echo $GLOBALS['message']; ?>
        </div>
        <?php endif; ?>

        <?php if( $GLOBALS['errstr'] ): ?>
        <div class="alert margin-bottom">
          <?php echo $GLOBALS['errstr']; ?>
        </div>
        <?php endif; ?>

      <fieldset>
        <legend>Basic Settings</legend>
        <div class="fieldgroup">
            <label for="base_url">Base URL:</label>
            <input type="text" name="base_url" id="base_url" size="70" value="<?PHP echo $C['base_url']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="base_url">Cookie Domain:</label>
            <input type="text" name="cookie_domain" id="cookie_domain" size="30" value="<?PHP echo $C['cookie_domain']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="from_email">E-mail Address:</label>
            <input type="text" name="from_email" id="from_email" size="40" value="<?PHP echo $C['from_email']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="from_email_name">E-mail Name:</label>
            <input type="text" name="from_email_name" id="from_email_name" size="40" value="<?PHP echo $C['from_email_name']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="email_type">E-mail Sender:</label>
            <select name="email_type">
              <?php
              $email_types = array(MT_PHP => 'PHP mail() function',
                                   MT_SENDMAIL => 'Sendmail',
                                   MT_SMTP => 'SMTP Server');
              echo OptionTags($email_types, $C['email_type']);
              ?>
            </select>
            <input type="text" name="mailer" id="mailer" size="40" value="<?PHP echo $C['mailer']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="page_new">New Links Page:</label>
            <input type="text" name="page_new" id="page_new" size="20" value="<?PHP echo $C['page_new']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="page_popular">Popular Links Page:</label>
            <input type="text" name="page_popular" id="page_popular" size="20" value="<?PHP echo $C['page_popular']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="page_top">Top Links Page:</label>
            <input type="text" name="page_top" id="page_top" size="20" value="<?PHP echo $C['page_top']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="page_details">Link Details Page:</label>
            <input type="text" name="page_details" id="page_details" size="20" value="<?PHP echo $C['page_details']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="extension">File Extension:</label>
            <input type="text" name="extension" id="extension" size="20" value="<?PHP echo $C['extension']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="date_format">Date Format:</label>
            <input type="text" name="date_format" id="date_format" size="20" value="<?PHP echo $C['date_format']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="time_format">Time Format:</label>
            <input type="text" name="time_format" id="time_format" size="20" value="<?PHP echo $C['time_format']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="timezone">Timezone:</label>
            <select name="timezone" id="timezone">
            <?PHP
            $zones = array('-12' => '(GMT -12:00) Eniwetok, Kwajalein',
                           '-11' => '(GMT -11:00) Midway Island, Samoa',
                           '-10' => '(GMT -10:00) Hawaii',
                           '-9' => '(GMT -9:00) Alaska',
                           '-8' => '(GMT -8:00) Pacific Time (US & Canada)',
                           '-7' => '(GMT -7:00) Mountain Time (US & Canada)',
                           '-6' => '(GMT -6:00) Central Time (US & Canada), Mexico City',
                           '-5' => '(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima',
                           '-4' => '(GMT -4:00) Atlantic Time (Canada), La Paz, Santiago',
                           '-3.5' => '(GMT -3:30) Newfoundland',
                           '-3' => '(GMT -3:00) Brazil, Buenos Aires, Georgetown',
                           '-2' => '(GMT -2:00) Mid-Atlantic',
                           '-1' => '(GMT -1:00 hour) Azores, Cape Verde Islands',
                           '0' => '(GMT) Western Europe Time, London, Lisbon, Casablanca',
                           '1' => '(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris',
                           '2' => '(GMT +2:00) Kaliningrad, South Africa',
                           '3' => '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg',
                           '3.5' => '(GMT +3:30) Tehran',
                           '4' => '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi',
                           '4.5' => '(GMT +4:30) Kabul',
                           '5' => '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent',
                           '5.5' => '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi',
                           '6' => '(GMT +6:00) Almaty, Dhaka, Colombo',
                           '6.5' => '(GMT +6:30) Yangon, Cocos Islands',
                           '7' => '(GMT +7:00) Bangkok, Hanoi, Jakarta',
                           '8' => '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong',
                           '9' => '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
                           '9.5' => '(GMT +9:30) Adelaide, Darwin',
                           '10' => '(GMT +10:00) Eastern Australia, Guam, Vladivostok',
                           '11' => '(GMT +11:00) Magadan, Solomon Islands, New Caledonia',
                           '12' => '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka');

            echo OptionTags($zones, $C['timezone']);
            ?>
            </select>
        </div>

        <div class="fieldgroup">
            <label for="dec_point">Decimal Point:</label>
            <input type="text" name="dec_point" id="dec_point" size="10" value="<?PHP echo $C['dec_point']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="thousands_sep">Thousands Separator:</label>
            <input type="text" name="thousands_sep" id="thousands_sep" size="10" value="<?PHP echo $C['thousands_sep']; ?>" />
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="mod_rewrite" class="cblabel inline"><?php echo CheckBox('mod_rewrite', 'checkbox', 1, $C['mod_rewrite']); ?>
            Use mod_rewrite to create search engine friendly URLs</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="custom_categories" class="cblabel inline"><?php echo CheckBox('custom_categories', 'checkbox', 1, $C['custom_categories']); ?>
            Using custom templates for category pages</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="log_searches" class="cblabel inline"><?php echo CheckBox('log_searches', 'checkbox', 1, $C['log_searches']); ?>
            Keep statistics on the search terms entered by surfers</label>
        </div>
      </fieldset>

      <fieldset>
        <legend>Link Submission Settings</legend>
        <div class="fieldgroup">
            <label for="min_desc_length">Description Length:</label>
            <input type="text" name="min_desc_length" id="min_desc_length" size="5" value="<?PHP echo $C['min_desc_length']; ?>" /> to
            <input type="text" name="max_desc_length" id="max_desc_length" size="5" value="<?PHP echo $C['max_desc_length']; ?>" /> characters
        </div>

        <div class="fieldgroup">
            <label for="min_title_length">Title Length:</label>
            <input type="text" name="min_title_length" id="min_title_length" size="5" value="<?PHP echo $C['min_title_length']; ?>" /> to
            <input type="text" name="max_title_length" id="max_title_length" size="5" value="<?PHP echo $C['max_title_length']; ?>" /> characters
        </div>

        <div class="fieldgroup">
            <label for="max_keywords">Keywords Allowed:</label>
            <input type="text" name="max_keywords" id="max_keywords" size="5" value="<?PHP echo $C['max_keywords']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="time_format">Default Link Weight:</label>
            <input type="text" name="link_weight" id="link_weight" size="10" value="<?PHP echo $C['link_weight']; ?>" />
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="allow_redirect" class="cblabel inline"><?php echo CheckBox('allow_redirect', 'checkbox', 1, $C['allow_redirect']); ?>
            Allow URL redirection</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="recip_required" class="cblabel inline"><?php echo CheckBox('recip_required', 'checkbox', 1, $C['recip_required']); ?>
            Require a reciprocal link</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="user_for_links" class="cblabel inline"><?php echo CheckBox('user_for_links', 'checkbox', 1, $C['user_for_links']); ?>
            Only registered users can submit links</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="email_links" class="cblabel inline"><?php echo CheckBox('email_links', 'checkbox', 1, $C['email_links']); ?>
            Send confirmation e-mail message to new links</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="confirm_links" class="cblabel inline"><?php echo CheckBox('confirm_links', 'checkbox', 1, $C['confirm_links']); ?>
            New links must be confirmed through e-mail before activation</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="approve_link_edits" class="cblabel inline"><?php echo CheckBox('approve_link_edits', 'checkbox', 1, $C['approve_link_edits']); ?>
            Edited links must be approved by an administrator before activation</label>
        </div>
      </fieldset>

      <fieldset>
        <legend>Comment Settings</legend>
        <div class="fieldgroup">
            <label for="min_comment_length">Comment Length:</label>
            <input type="text" name="min_comment_length" id="min_comment_length" size="5" value="<?PHP echo $C['min_comment_length']; ?>" /> to
            <input type="text" name="max_comment_length" id="max_comment_length" size="5" value="<?PHP echo $C['max_comment_length']; ?>" /> characters
        </div>

        <div class="fieldgroup">
            <label for="comment_delay">Comment Delay:</label>
            <input type="text" name="comment_delay" id="comment_delay" size="8" value="<?PHP echo $C['comment_delay']; ?>" />
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="user_for_comments" class="cblabel inline"><?php echo CheckBox('user_for_comments', 'checkbox', 1, $C['user_for_comments']); ?>
            Only registered users can leave comments for links</label>
        </div>

         <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="approve_comments" class="cblabel inline"><?php echo CheckBox('approve_comments', 'checkbox', 1, $C['approve_comments']); ?>
            New comments must be approved by an administrator before display</label>
        </div>
      </fieldset>

      <fieldset>
        <legend>Rating Settings</legend>
        <div class="fieldgroup">
            <label for="max_rating">Maximum Rating:</label>
            <input type="text" name="max_rating" id="max_rating" size="10" value="<?PHP echo $C['max_rating']; ?>" />
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="user_for_rate" class="cblabel inline"><?php echo CheckBox('user_for_rate', 'checkbox', 1, $C['user_for_rate']); ?>
            Only registered users can rate links</label>
        </div>
      </fieldset>

      <fieldset>
        <legend>Account Settings</legend>
        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="email_accounts" class="cblabel inline"><?php echo CheckBox('email_accounts', 'checkbox', 1, $C['email_accounts']); ?>
            Send confirmation e-mail message to new accounts</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="confirm_accounts" class="cblabel inline"><?php echo CheckBox('confirm_accounts', 'checkbox', 1, $C['confirm_accounts']); ?>
            New accounts must be confirmed through e-mail before activation</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="approve_accounts" class="cblabel inline"><?php echo CheckBox('approve_accounts', 'checkbox', 1, $C['approve_accounts']); ?>
            New accounts must be approved by an administrator before activation</label>
        </div>
      </fieldset>


      <fieldset>
        <legend>Verification Code Settings</legend>

        <div class="fieldgroup">
            <label for="font_dir">Font Directory:</label>
            <input type="text" name="font_dir" id="font_dir" size="60" value="<?PHP echo $C['font_dir']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="min_code_length">Code Length:</label>
            <input type="text" name="min_code_length" id="min_code_length" size="5" value="<?PHP echo $C['min_code_length']; ?>" /> to
            <input type="text" name="max_code_length" id="max_code_length" size="5" value="<?PHP echo $C['max_code_length']; ?>" /> characters
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="use_words" class="cblabel inline"><?php echo CheckBox('use_words', 'checkbox', 1, $C['use_words']); ?>
            Use words file for verification codes</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="link_captcha" class="cblabel inline"><?php echo CheckBox('link_captcha', 'checkbox', 1, $C['link_captcha']); ?>
            Require verification code on link submission form</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="account_captcha" class="cblabel inline"><?php echo CheckBox('account_captcha', 'checkbox', 1, $C['account_captcha']); ?>
            Require verification code on account signup form</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="rate_captcha" class="cblabel inline"><?php echo CheckBox('rate_captcha', 'checkbox', 1, $C['rate_captcha']); ?>
            Require verification code to rate links</label>
        </div>

         <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="comments_captcha" class="cblabel inline"><?php echo CheckBox('comments_captcha', 'checkbox', 1, $C['comments_captcha']); ?>
            Require verification code to leave comments</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="report_captcha" class="cblabel inline"><?php echo CheckBox('report_captcha', 'checkbox', 1, $C['report_captcha']); ?>
            Require verification code to submit broken link reports</label>
        </div>
      </fieldset>

      <fieldset>
        <legend>Cache Settings</legend>
        <div class="fieldgroup">
            <label for="cache_index">Index Page:</label>
            <input type="text" name="cache_index" id="cache_index" size="10" value="<?PHP echo $C['cache_index']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="cache_category">Category Page:</label>
            <input type="text" name="cache_category" id="cache_category" size="10" value="<?PHP echo $C['cache_category']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="cache_new">New Links Page:</label>
            <input type="text" name="cache_new" id="cache_new" size="10" value="<?PHP echo $C['cache_new']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="cache_popular">Popular Links Page:</label>
            <input type="text" name="cache_popular" id="cache_popular" size="10" value="<?PHP echo $C['cache_popular']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="cache_top">Top Links Page:</label>
            <input type="text" name="cache_top" id="cache_top" size="10" value="<?PHP echo $C['cache_top']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="cache_search">Search Page:</label>
            <input type="text" name="cache_search" id="cache_search" size="10" value="<?PHP echo $C['cache_search']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="cache_details">Details Page:</label>
            <input type="text" name="cache_details" id="cache_details" size="10" value="<?PHP echo $C['cache_details']; ?>" />
        </div>
      </fieldset>

    <div class="centered margin-top">
      <button type="submit">Save Settings</button>
    </div>

    <input type="hidden" name="r" value="lxSaveGeneralSettings">
    </form>
</div>


</body>
</html>
