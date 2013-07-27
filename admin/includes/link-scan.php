<?php
if( !defined('LINKX') ) die("Access denied");

define('NODTD', TRUE);
include_once('includes/header.php');
?>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/link-scan.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
    </div>

    <fieldset>
      <legend>Blacklist Items Found</legend>

      <?php if( $blacklisted !== FALSE ): ?>

      <table width="100%" cellpadding="2">
      <?php foreach($blacklisted as $blacklist): ?>
        <tr>
          <td valign="top" width="200">
            <?php echo htmlspecialchars($blacklist['match']); ?>
            <span class="light">[<?php echo htmlspecialchars($blacklist['type']); ?>]</span>
            <?php echo htmlspecialchars($blacklist['reason']); ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </table>

      <?php else: ?>
      <div class="notice">
      No blacklist items found
      </div>
      <?php endif; ?>

    </fieldset>


    <fieldset>
      <legend>Site URL Scan Results</legend>

      <table width="100%" cellpadding="4">
        <tr>
          <td valign="top" align="right" width="120" style="padding-top: 7px;">
            <b>Working</b>
          </td>
          <td>
            <?php if( $results['site_url']['working'] ): ?>
            <div class="notice" style="padding-left: 4px;">Yes</div>
            <?php else: ?>
            <div class="alert" style="padding-left: 4px;">No<br />
            <?php echo htmlspecialchars($results['site_url']['error']); ?></div>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td valign="top" align="right" width="120">
            <b>HTTP Status</b>
          </td>
          <td>
            <?php echo htmlspecialchars($results['site_url']['status']); ?>
          </td>
        </tr>
        <tr>
          <td valign="top" align="right" width="120">
            <b>IP Address</b>
          </td>
          <td>
            <?php echo htmlspecialchars($results['site_url']['ip_address']); ?>
          </td>
        </tr>
        <tr>
          <td valign="top" align="right" width="120" style="padding-top: 7px;">
            <b>Recip Link</b>
          </td>
          <td>
            <?php if( $results['site_url']['has_recip'] ): ?>
            <div class="notice" style="padding-left: 4px;">Found</div>
            <?php else: ?>
            <div class="alert" style="padding-left: 4px;">Not Found</div>
            <?php endif; ?>
          </td>
        </tr>
      </table>

    </fieldset>

    <fieldset>
      <legend>Recip URL Scan Results</legend>

      <?php if( !is_array($results['recip_url']) ): ?>
      <div class="notice">
      No recip URL is set for this link
      </div>
      <?php else: ?>
      <table width="100%" cellpadding="4">
        <tr>
          <td valign="top" align="right" width="120" style="padding-top: 7px;">
            <b>Working</b>
          </td>
          <td>
            <?php if( $results['recip_url']['working'] ): ?>
            <div class="notice" style="padding-left: 4px;">Yes</div>
            <?php else: ?>
            <div class="alert" style="padding-left: 4px;">No<br />
            <?php echo htmlspecialchars($results['recip_url']['error']); ?></div>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td valign="top" align="right" width="120">
            <b>HTTP Status</b>
          </td>
          <td>
            <?php echo htmlspecialchars($results['recip_url']['status']); ?>
          </td>
        </tr>
        <tr>
          <td valign="top" align="right" width="120">
            <b>IP Address</b>
          </td>
          <td>
            <?php echo htmlspecialchars($results['recip_url']['ip_address']); ?>
          </td>
        </tr>
        <tr>
          <td valign="top" align="right" width="120" style="padding-top: 7px;">
            <b>Recip Link</b>
          </td>
          <td>
            <?php if( $results['recip_url']['has_recip'] ): ?>
            <div class="notice" style="padding-left: 4px;">Found</div>
            <?php else: ?>
            <div class="alert" style="padding-left: 4px;">Not Found</div>
            <?php endif; ?>
          </td>
        </tr>
      </table>
      <?php endif; ?>

    </fieldset>

</div>

</body>
</html>
