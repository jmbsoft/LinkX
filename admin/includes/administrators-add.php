<?php
if( !defined('LINKX') ) die("Access denied");

$type_options = array(ACCOUNT_ADMINISTRATOR => 'Administrator',
                      ACCOUNT_EDITOR => 'Editor');

include_once('includes/header.php');
?>

<script language="JavaScript">
$(function()
  {
      $('#type').bind('change', function()
                                {
                                    if( this.value == 'administrator' )
                                        $('#privileges').BlindUp(500);
                                    else
                                        $('#privileges').BlindDown(500);
                                });
  });

<?PHP if( $GLOBALS['added'] ): ?>
if( typeof window.parent.Search == 'object' )
    window.parent.Search.search(false);
<?PHP endif; ?>
</script>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/administrators.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      <?php if( $editing ): ?>
      Update this administrator account by making changes to the information below
      <?php else: ?>
      Add a new administrator account by filling out the information below
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

        <fieldset>
          <legend>General Settings</legend>

        <div class="fieldgroup">
            <label for="username">Username:</label>
            <?php if( $editing ): ?>
            <div style="padding: 3px 0px 0px 0px; margin: 0;"><?php echo $_REQUEST['username']; ?></div>
            <input type="hidden" name="username" value="<?php echo $_REQUEST['username']; ?>" />
            <?php else: ?>
            <input type="text" name="username" id="username" size="20" value="<?php echo $_REQUEST['username']; ?>" />
            <?php endif; ?>
        </div>

        <div class="fieldgroup">
            <label for="password">Password:</label>
            <input type="text" name="password" id="password" size="20" value="<?php echo $_REQUEST['password']; ?>" />
            <?php if( $editing ): ?>
            <br /> Leave blank unless you want to change this account's password
            <?php endif; ?>
        </div>

        <div class="fieldgroup">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" size="30" value="<?php echo $_REQUEST['name']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="email">E-mail Address:</label>
            <input type="text" name="email" id="email" size="40" value="<?php echo $_REQUEST['email']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="type">Account Type:</label>
            <select name="type" id="type">
              <?php echo OptionTags($type_options, $_REQUEST['type']); ?>
            </select>
        </div>
        </fieldset>


        <div id="privileges" style="width: 100%<?php if( $_REQUEST['type'] != ACCOUNT_EDITOR ) echo "; display: none;"; ?>">
        <fieldset>
          <legend>Privileges</legend>

          <!--<div class="fieldgroup">
            <label>Link Categories:</label>
            <input type="hidden" name="categories" value="<?php echo $_REQUEST['categories']; ?>" />
          </div>-->

          <div class="fieldgroup">
            <label class="lesspad">Categories:</label>
            <label for="p_cat_a" class="cblabel inline">
            <?php echo CheckBox('p_cat_a', 'checkbox', P_CATEGORY_ADD, $_REQUEST['p_cat_a'], $_REQUEST['rights']); ?> Add/Approve &nbsp;</label>
            <label for="p_cat_m" class="cblabel inline">
            <?php echo CheckBox('p_cat_m', 'checkbox', P_CATEGORY_MODIFY, $_REQUEST['p_cat_m'], $_REQUEST['rights']); ?> Modify &nbsp;</label>
            <label for="p_cat_r" class="cblabel inline">
            <?php echo CheckBox('p_cat_r', 'checkbox', P_CATEGORY_REMOVE, $_REQUEST['p_cat_r'], $_REQUEST['rights']); ?> Remove &nbsp;</label>
          </div>

          <!--<div class="fieldgroup">
            <label class="lesspad">Site Types:</label>
            <label for="p_type_a" class="cblabel inline">
            <?php echo CheckBox('p_type_a', 'checkbox', P_TYPE_ADD, $_REQUEST['p_type_a'], $_REQUEST['rights']); ?> Add/Approve &nbsp;</label>
            <label for="p_type_m" class="cblabel inline">
            <?php echo CheckBox('p_type_m', 'checkbox', P_TYPE_MODIFY, $_REQUEST['p_type_m'], $_REQUEST['rights']); ?> Modify &nbsp;</label>
            <label for="p_type_r" class="cblabel inline">
            <?php echo CheckBox('p_type_r', 'checkbox', P_TYPE_REMOVE, $_REQUEST['p_type_r'], $_REQUEST['rights']); ?> Remove &nbsp;</label>
          </div>-->

          <div class="fieldgroup">
            <label class="lesspad">Links:</label>
            <label for="p_link_a" class="cblabel inline">
            <?php echo CheckBox('p_link_a', 'checkbox', P_LINK_ADD, $_REQUEST['p_link_a'], $_REQUEST['rights']); ?> Add/Approve &nbsp;</label>
            <label for="p_link_m" class="cblabel inline">
            <?php echo CheckBox('p_link_m', 'checkbox', P_LINK_MODIFY, $_REQUEST['p_link_m'], $_REQUEST['rights']); ?> Modify &nbsp;</label>
            <label for="p_link_r" class="cblabel inline">
            <?php echo CheckBox('p_link_r', 'checkbox', P_LINK_REMOVE, $_REQUEST['p_link_r'], $_REQUEST['rights']); ?> Remove &nbsp;</label>
          </div>

          <div class="fieldgroup">
            <label class="lesspad">Comments:</label>
            <label for="p_comment_a" class="cblabel inline">
            <?php echo CheckBox('p_comment_a', 'checkbox', P_COMMENT_ADD, $_REQUEST['p_comment_a'], $_REQUEST['rights']); ?> Add/Approve &nbsp;</label>
            <label for="p_comment_m" class="cblabel inline">
            <?php echo CheckBox('p_comment_m', 'checkbox', P_COMMENT_MODIFY, $_REQUEST['p_comment_m'], $_REQUEST['rights']); ?> Modify &nbsp;</label>
            <label for="p_comment_r" class="cblabel inline">
            <?php echo CheckBox('p_comment_r', 'checkbox', P_COMMENT_REMOVE, $_REQUEST['p_comment_r'], $_REQUEST['rights']); ?> Remove &nbsp;</label>
          </div>

          <div class="fieldgroup">
            <label class="lesspad">Users:</label>
            <label for="p_user_a" class="cblabel inline">
            <?php echo CheckBox('p_user_a', 'checkbox', P_USER_ADD, $_REQUEST['p_user_a'], $_REQUEST['rights']); ?> Add/Approve &nbsp;</label>
            <label for="p_user_m" class="cblabel inline">
            <?php echo CheckBox('p_user_m', 'checkbox', P_USER_MODIFY, $_REQUEST['p_user_m'], $_REQUEST['rights']); ?> Modify &nbsp;</label>
            <label for="p_user_r" class="cblabel inline">
            <?php echo CheckBox('p_user_r', 'checkbox', P_USER_REMOVE, $_REQUEST['p_user_r'], $_REQUEST['rights']); ?> Remove &nbsp;</label>
          </div>

        </fieldset>
        </div>


        <fieldset>
          <legend>E-mail Settings</legend>

          <div class="fieldgroup">
            <label class="lesspad">E-mail On:</label>
            <label for="e_link_add" class="cblabel inline">
            <?php echo CheckBox('e_link_add', 'checkbox', E_LINK_ADD, $_REQUEST['e_link_add'], $_REQUEST['notifications']); ?> New Link &nbsp;</label>
            <span style="padding-left: 21px; margin: 0">&nbsp;</span>
            <label for="e_link_edit" class="cblabel inline">
            <?php echo CheckBox('e_link_edit', 'checkbox', E_LINK_EDIT, $_REQUEST['e_link_edit'], $_REQUEST['notifications']); ?> Edited Link</label>
          </div>

          <div class="fieldgroup">
            <label class="lesspadd"></label>
            <label for="e_comment" class="cblabel inline">
            <?php echo CheckBox('e_comment', 'checkbox', E_COMMENT, $_REQUEST['e_comment'], $_REQUEST['notifications']); ?> New Comment &nbsp;</label>
            <label for="e_payment" class="cblabel inline">
            <?php echo CheckBox('e_payment', 'checkbox', E_PAYMENT, $_REQUEST['e_payment'], $_REQUEST['notifications']); ?> Payment</label>
          </div>
        </fieldset>

    <div class="centered margin-top">
      <button type="submit"><?php echo ($editing ? 'Update' : 'Add'); ?> Account</button>
    </div>

    <input type="hidden" name="r" value="<?php echo ($editing ? 'lxEditAdministrator' : 'lxAddAdministrator'); ?>">

    <?php if( $editing ): ?>
    <input type="hidden" name="editing" value="1">
    <?PHP endif; ?>
    </form>
</div>



</body>
</html>
