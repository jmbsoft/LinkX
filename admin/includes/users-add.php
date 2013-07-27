<?php
if( !defined('LINKX') ) die("Access denied");

$defaults = array('date_added' => MYSQL_NOW,
                  'weight' => 1,
                  'status' => 'active');

$_REQUEST = array_merge($defaults, $_REQUEST);

$jscripts = array('includes/calendar.js');
$csses = array('includes/calendar.css');
include_once('includes/header.php');
?>

<script language="JavaScript">
<?PHP if( $GLOBALS['added'] ): ?>
if( typeof window.parent.Search == 'object' )
    window.parent.Search.search(false);
<?PHP endif; ?>
</script>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/users.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      <?php if( $editing ): ?>
      Update this user account by making changes to the information below
      <?php else: ?>
      Add a new user account by filling out the information below
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
            <label for="status">Status:</label>
            <select name="status" id="status">
            <?php
            $statuses = array('unconfirmed' => 'Unconfirmed',
                              'pending' => 'Pending',
                              'active' => 'Active',
                              'suspended' => 'Suspended');

            echo OptionTags($statuses, $_REQUEST['status']);
            ?>
            </select>
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
            <label for="weight">Weight:</label>
            <input type="text" name="weight" id="weight" size="10" value="<?php echo $_REQUEST['weight']; ?>" />
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
            <label class="lesspad"></label>
            <label for="recip_required" class="cblabel inline"><?php echo CheckBox('recip_required', 'checkbox', 1, $_REQUEST['recip_required']); ?> Require a reciprocal link</label>
        </div>

        <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="allow_redirect" class="cblabel inline"><?php echo CheckBox('allow_redirect', 'checkbox', 1, $_REQUEST['allow_redirect']); ?> Allow URL redirection</label>
        </div>
        </fieldset>

      <?php
      $result = $DB->Query('SELECT * FROM lx_user_field_defs ORDER BY field_id');
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
              <label for="<?php echo $field['name']; ?>" class="cblabel inline"><?php echo FormField($field, $_REQUEST[$field['name']]); ?> <?php echo $field['label']; ?></label>
            <?php endif; ?>
        </div>

        <?php endwhile; ?>
      </fieldset>

    <div class="centered margin-top">
      <button type="submit"><?php echo ($editing ? 'Update' : 'Add'); ?> Account</button>
    </div>

    <input type="hidden" name="r" value="<?php echo ($editing ? 'lxEditUser' : 'lxAddUser'); ?>">

    <?php if( $editing ): ?>
    <input type="hidden" name="username" value="<?php echo $_REQUEST['username']; ?>">
    <input type="hidden" name="editing" value="1">
    <?PHP endif; ?>
    </form>
</div>



</body>
</html>
