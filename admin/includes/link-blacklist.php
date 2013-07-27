<?php
if( !defined('LINKX') ) die("Access denied");

define('NODTD', TRUE);
include_once('includes/header.php');
?>

<script language="JavaScript">
function cutUrl(field)
{
    var url = field.value;

    url = url.replace(/http:\/\//, '');
    url = url.replace(/\/.*/, '');
    url = url.replace(/www\./, '');

    field.value = url;
}

function cutEmail(field)
{
    var email = field.value;

    email = email.replace(/^[^@]+@/, '');

    field.value = email;
}
</script>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/blacklist.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Select and/or modify the items that you want to blacklist
    </div>

    <fieldset>
      <legend>Blacklist Items</legend>

        <div class="fieldgroup">
            <label for="value">E-mail Address:</label>
            <input type="text" name="email" id="email" size="60" value="<?php echo htmlspecialchars($link['email']); ?>" />
            &nbsp;
            <img src="images/cut.gif" border="0" onclick="cutEmail($('email'))" style="cursor: pointer">
            &nbsp;
            <img src="images/x.gif" border="0" onclick="$('email').value = ''" style="cursor: pointer">
        </div>

        <div class="fieldgroup">
            <label for="value">Site URL:</label>
            <input type="text" name="url" id="url" size="60" value="<?php echo htmlspecialchars($link['site_url']); ?>" />
            &nbsp;
            <img src="images/cut.gif" border="0" onclick="cutUrl($('url'))" style="cursor: pointer">
            &nbsp;
            <img src="images/x.gif" border="0" onclick="$('url').value = ''" style="cursor: pointer">
        </div>

        <div class="fieldgroup">
            <label for="value">Domain IP:</label>
            <input type="text" name="domain_ip" id="domain_ip" size="20" value="<?php echo htmlspecialchars($link['domain_ip']); ?>" />
            &nbsp;
            <img src="images/x.gif" border="0" onclick="$('domain_ip').value = ''" style="cursor: pointer">
        </div>

        <div class="fieldgroup">
            <label for="value">Submit IP:</label>
            <input type="text" name="submit_ip" id="submit_ip" size="20" value="<?php echo htmlspecialchars($link['submit_ip']); ?>" />
            &nbsp;
            <img src="images/x.gif" border="0" onclick="$('submit_ip').value = ''" style="cursor: pointer">
        </div>

        <div class="fieldgroup">
            <label for="value">Blacklist Reason:</label>
            <input type="text" name="reason" id="reason" size="60" value="" />
        </div>
    </fieldset>

    <div class="centered margin-top">
      <button type="submit">Blacklist Link</button>
    </div>

    <input type="hidden" name="r" value="lxBlacklistLink">
    <input type="hidden" name="link_id" value="<?php echo htmlspecialchars($link['link_id']); ?>">
    </form>
</div>



</body>
</html>
