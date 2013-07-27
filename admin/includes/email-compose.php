<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
?>

<div style="padding: 10px;">
  <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      Send an e-mail message to the selected recipients by filling out the information below
    </div>

    <?php if( $GLOBALS['message'] ): ?>
    <div class="notice margin-bottom">
      <?php echo $GLOBALS['message']; ?>
    </div>
    <?php endif; ?>

    <fieldset>
      <legend>E-mail Message</legend>

      <div class="fieldgroup">
        <label>To:</label>
        <div style="padding: 3px 0px 0px 0px; margin: 0;"><?php echo $_REQUEST['to']; ?></div>
        <input type="hidden" name="to" value="<?php echo $_REQUEST['to_list']; ?>" />
      </div>

      <div class="fieldgroup">
        <label for="subject">Subject:</label>
        <input type="text" name="subject" id="subject" size="60" value="<?PHP echo $_REQUEST['subject']; ?>" />
      </div>

      <div class="fieldgroup">
        <label for="plain">Text Body:<br />
        <img src="images/html.png" border="0" width="16" height="16" alt="To HTML" onclick="textToHtml('#plain', '#html')" style="cursor: pointer; margin-top: 5px;">
        </label>
        <textarea name="plain" id="plain" rows="15" cols="100" wrap="off"><?php echo $_REQUEST['plain']; ?></textarea>
    </div>

    <div class="fieldgroup">
        <label for="html">HTML Body:</label>
        <textarea name="html" id="html" rows="15" cols="100" wrap="off"><?php echo $_REQUEST['html']; ?></textarea>
    </div>

    </fieldset>

    <div class="centered margin-top"><button type="submit">Send</button></div>
    <input type="hidden" name="r" value="<?PHP echo $function; ?>">
  </form>
</div>


</body>
</html>
