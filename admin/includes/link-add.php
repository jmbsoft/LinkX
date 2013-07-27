<?php
if( !defined('LINKX') ) die("Access denied");

$defaults = array('submit_ip' => $_SERVER['REMOTE_ADDR'],
                  'status' => 'active',
                  'date_added' => MYSQL_NOW,
                  'date_modified' => '',
                  'clicks' => 0,
                  'ratings' => 0,
                  'rating_total' => 0,
                  'weight' => $C['link_weight']);

if( !$editing )
{
    $_REQUEST = array_merge($defaults, $_REQUEST);
}

$jscripts = array('includes/calendar.js');
$csses = array('includes/calendar.css');
include_once('includes/header.php');
?>

<script language="JavaScript">
var popup = null;

$(function()
{
    if( $('#category_id').val() != '' )
    {
        updateSelected($('#category_id').val(), '#category_id');
    }

    $('#getinfo').bind('click', function()
                                {
                                    $('#getinfo').attr('src', 'images/activity-small.gif');

                                    $.ajax({type: 'POST',
                                            url: 'ajax.php',
                                            dataType: 'json',
                                            data: 'r=lxExtractSiteInfo&url=' + escape($('#site_url').val()),
                                            error: function(request, status, error) { $('#getinfo').attr('src', 'images/go-down.png'); },
                                            success: function(json)
                                                     {
                                                         $('#getinfo').attr('src', 'images/go-down.png');

                                                         if( json.status == JSON_SUCCESS )
                                                         {
                                                             $('#title').val(json.title);
                                                             $('#description').val(json.description);
                                                             $('#keywords').val(json.keywords);
                                                         }
                                                     }
                                           });
                                });

    updateRating();
});


function updateRating()
{
    var ratings = parseInt($('#ratings').val());
    var total = parseInt($('#rating_total').val());

    if( ratings > 0 )
        $('#rating_avg').html(Math.round((total/ratings)*100)/100);
    else
        $('#rating_avg').html('0');
}


function searchUsers()
{
    var term = $('#username').val();

    if( !term )
    {
        alert('Please enter an e-mail address or username to search for');
        return;
    }

    $('#user_search').html('<img src="images/activity-small.gif">');

    $.ajax({type: 'POST',
            url: 'ajax.php',
            dataType: 'json',
            data: 'r=lxQuickUserSearch&term=' + escape(term),
            error: function(request, status, error) { },
            success: function(json)
                     {
                         if( json.status == JSON_SUCCESS )
                         {
                             var select = $('#user_search');

                             if( json.results.length > 0 )
                             {
                                 var options = new Array();

                                 $.each(json.results, function(index, item) { options.push('<option value="'+item.username+'">'+item.username+' ('+item.email+')</option>'); });

                                 select.html('<select id="user_search_results" onchange="$(\'#username\').val($(this).val())">' +
                                             '<option value="">Select a user...</option>' +
                                             options.join("\n") +
                                             '</select>');
                             }
                             else
                             {
                                 select.html('<span style="color: red; font-weight: bold;">No Matches<span>');
                             }

                             select.show();
                         }
                     }
           });
}

<?PHP if( $GLOBALS['added'] && empty($_REQUEST['nosearch']) ): ?>
if( typeof window.parent.Search == 'object' )
    window.parent.Search.search(false);
<?PHP endif; ?>
</script>

<div style="padding: 10px;">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/links.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      <?php if( $editing ): ?>
      Update this link by making changes to the information below
      <?php else: ?>
      Add a new link by filling out the information below
      <?php endif; ?>
    </div>

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

    <?php
    if( $DB->Count('SELECT COUNT(*) FROM `lx_categories`') < 1 ):
    ?>
    <div class="alert margin-bottom">
    There must be at least one category defined before you can add links to the database
    </div>
    <?php
    else:
    ?>

    <form action="index.php" method="POST" id="form">
      <fieldset>
        <legend>General Information</legend>

        <div class="fieldgroup">
            <label for="email">E-mail:</label>
            <input type="text" name="email" id="email" size="30" value="<?php echo $_REQUEST['email']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="name">Submitter Name:</label>
            <input type="text" name="name" id="name" size="30" value="<?php echo $_REQUEST['name']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="submit_ip">Submitter IP:</label>
            <input type="text" name="submit_ip" id="submit_ip" size="16" value="<?php echo $_REQUEST['submit_ip']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="site_url">Site URL:</label>
            <input type="text" name="site_url" id="site_url" size="90" value="<?php echo $_REQUEST['site_url']; ?>" />
            <img src="images/go-down.png" border="0" id="getinfo" class="click">
        </div>

        <div class="fieldgroup">
            <label for="recip_url">Recip URL:</label>
            <input type="text" name="recip_url" id="recip_url" size="90" value="<?php echo $_REQUEST['recip_url']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" size="90" value="<?php echo $_REQUEST['title']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="5" cols="90"><?php echo $_REQUEST['description']; ?></textarea>
        </div>

        <div class="fieldgroup">
            <label for="keywords">Keywords:</label>
            <input type="text" name="keywords" id="keywords" size="90" value="<?php echo $_REQUEST['keywords']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="category">Categories:</label>
            <div style="float: left; width: 500px; padding-bottom: 5px;">
            <img src="images/add-small.png" width="12" height="12" alt="Add" class="addimg" style="margin-top: 3px;" onclick="showSelectCat('category_id', 'checkbox')" />
            <img src="images/remove-small.png" width="12" height="12" alt="Delete" class="addimg" style="margin-top: 3px;" onclick="clearCat('category_id')" />
            <div id="category_id_div"></div>
            <input type="hidden" name="category_id" id="category_id" value="<?php echo $_REQUEST['category_id']; ?>" />
            </div>
        </div>

        <div class="fieldgroup">
            <label for="status">Status:</label>
            <select name="status">
            <?php
            $statuses = array('unconfirmed' => 'Unconfirmed',
                              'pending' => 'Pending',
                              'active' => 'Active',
                              'disabled' => 'Disabled');

            echo OptionTags($statuses, $_REQUEST['status']);
            ?>
            </select>
        </div>

        <div class="fieldgroup">
            <label for="type">Type:</label>
            <select name="type">
            <?php
            $types = array('regular' => 'Regular',
                           'premium' => 'Premium',
                           'featured' => 'Featured');

            echo OptionTags($types, $_REQUEST['type']);
            ?>
            </select>
            &nbsp;
            <input type="text" name="expires" id="expires" value="<?php echo $_REQUEST['expires']; ?>" class="calendarSelectDate" />
        </div>

        <div class="fieldgroup">
            <label for="username">Account:</label>
            <input type="text" name="username" id="username" size="30" value="<?php echo $_REQUEST['username']; ?>" /> &nbsp;
            <img src="images/search.png" height="12" width="12" alt="Search" onclick="searchUsers()" style="cursor: pointer;"> &nbsp;
            <span id="user_search" style="display: none;"></span>
        </div>

        <div class="fieldgroup">
            <label for="password">Password:</label>
            <input type="text" name="password" id="password" size="30" value="<?php echo $_REQUEST['password']; ?>" />
            <?php if( $editing ): ?>
            &nbsp; Leave blank to retain current password
            <?php endif; ?>
        </div>

        <div class="fieldgroup">
            <label for="clicks">Clicks:</label>
            <input type="text" name="clicks" id="clicks" size="10" value="<?php echo $_REQUEST['clicks']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="date_added">Date Added:</label>
            <input type="text" name="date_added" id="date_added" size="20" value="<?php echo $_REQUEST['date_added']; ?>" class="calendarSelectDate" />
        </div>

        <div class="fieldgroup">
            <label for="date_modified">Date Modified:</label>
            <input type="text" name="date_modified" id="date_modified" size="20" value="<?php echo $_REQUEST['date_modified']; ?>" class="calendarSelectDate" />
        </div>

        <div class="fieldgroup">
            <label for="weight">Weight:</label>
            <input type="text" name="weight" id="weight" size="10" value="<?php echo $_REQUEST['weight']; ?>"/>
        </div>

        <div class="fieldgroup">
            <label for="ratings">Ratings/Total:</label>
            <input type="text" name="ratings" id="ratings" size="10" value="<?php echo $_REQUEST['ratings']; ?>" onkeyup="updateRating()" />
            /
            <input type="text" name="rating_total" id="rating_total" size="10" value="<?php echo $_REQUEST['rating_total']; ?>" onkeyup="updateRating()" /> &nbsp;
            Average Rating: <span id="rating_avg"></span>
        </div>

        <div class="fieldgroup">
            <label for="icons">Icon HTML:</label>
            <textarea name="icons" id="icons" rows="3" cols="90"><?php echo $_REQUEST['icons']; ?></textarea>
        </div>

        <div class="fieldgroup">
            <label for="admin_comments">Admin Comments:</label>
            <textarea name="admin_comments" id="admin_comments" rows="3" cols="90"><?php echo $_REQUEST['admin_comments']; ?></textarea>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="recip_required" class="cblabel inline"><?php echo CheckBox('recip_required', 'checkbox', 1, $_REQUEST['recip_required']); ?> Require a reciprocal link</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="allow_redirect" class="cblabel inline"><?php echo CheckBox('allow_redirect', 'checkbox', 1, $_REQUEST['allow_redirect']); ?> Allow URL redirection</label>
        </div>
      </fieldset>

      <?php
      $result = $DB->Query('SELECT * FROM lx_link_field_defs ORDER BY field_id');
      ?>
      <fieldset<?php if( $DB->NumRows($result) < 1 ) echo ' style="display: none;"'; ?>>
        <legend>User Defined Fields</legend>

        <?php
        while( $field = $DB->NextRow($result) ):
            ArrayHSC($field);
            AdminFormField($field);
        ?>

        <div class="fieldgroup">
            <?php if( $field['type'] != FT_CHECKBOX ): ?>
              <label for="<?php echo $field['name']; ?>"><?php echo $field['label']; ?>:</label>
              <?php echo FormField($field, $_REQUEST[$field['name']]); ?>
            <?php else: ?>
              <label style="height: 1px; font-size: 1px;"></label>
              <label for="<?php echo $field['name']; ?>" class="cblabel inline">
              <?php echo FormField($field, $_REQUEST[$field['name']]); ?> <?php echo $field['label']; ?></label>
            <?php endif; ?>
        </div>

        <?php endwhile; ?>
      </fieldset>

      <div class="centered margin-top">
      <button type="submit"><?php echo ($editing ? 'Update' : 'Add'); ?> Link</button>
    </div>

    <input type="hidden" name="r" value="<?php echo ($editing ? 'lxEditLink' : 'lxAddLink'); ?>">
    <input type="hidden" name="nosearch" value="<?php echo $_REQUEST['nosearch']; ?>">

    <?php if( $editing ): ?>
    <input type="hidden" name="link_id" value="<?php echo $_REQUEST['link_id']; ?>">
    <input type="hidden" name="editing" value="1">
    <?PHP endif; ?>
    </form>

    <?php
    endif;
    ?>
</div>

</body>
</html>
