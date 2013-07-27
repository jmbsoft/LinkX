<tr id="<?php echo $item['username']; ?>" class="<?php echo $item['status']; ?>">
  <td valign="top">
    <input class="checkbox autocb" name="username[]" value="<?php echo $item['username']; ?>" type="checkbox">
  </td>
  <td valign="top">
    <b style="color: #f9a239; font-size: 10pt;"><?php echo $item['username']; ?></b>

    <div style="margin-left: 20px;">
    <div style="margin-top: 4px; position: relative;">
    <b>Name:</b>&nbsp;
    <?php echo $item['name']; ?>

    <span style="left: 225px; position: absolute;"><b>E-mail:</b>&nbsp;
    <a href="mailto:<?php echo $item['email']; ?>"><?php echo $item['email']; ?></a></span>

    <span style="left: 475px; position: absolute;"><b>Links:</b>&nbsp;
    <?php echo number_format($item['num_links'], 0, $C['dec_point'], $C['thousands_sep']); ?></span>
    </div>

    <div style="margin-top: 4px; position: relative;">
    <b>Added:</b>&nbsp;
    <?php echo date(DF_SHORT, strtotime($item['date_added'])); ?>

    <span style="left: 225px; position: absolute;"><b>Modified:</b>&nbsp;
    <?php echo !empty($item['date_modified']) && $item['date_modified'] != DATE_EMPTY ? date(DF_SHORT, strtotime($item['date_modified'])) : '-'; ?></span>

    <span style="left: 475px; position: absolute;"><b>Weight:</b>&nbsp;
    <?php echo number_format($item['weight'], 2, $C['dec_point'], $C['thousands_sep']); ?></span>
    </div>


    <div style="margin-top: 4px; position: relative;">
    <b>Allow Redirect:</b>&nbsp;
    <?PHP echo $item['recip_required'] ? 'Yes' : 'No'; ?>

    <span style="left: 225px; position: absolute;"><b>Recip Required:</b>&nbsp;
    <?PHP echo $item['recip_required'] ? 'Yes' : 'No'; ?></span>
    </div>

    <?php
    foreach( $GLOBALS['_user_fields_'] as $field ):
        if( !IsEmptyString($item[$field['name']]) ):
    ?>
    <div style="margin-top: 4px;"><b><?php echo StringChopTooltip(htmlspecialchars($field['label']), 25); ?>:</b>&nbsp; <?php echo $item[$field['name']]; ?></div>
    <?php endif; endforeach; ?>
    </div>
  </td>
  <td style="text-align: right;" class="last" valign="top">
    <a href="index.php?r=lxShEditUser&username=<?php echo urlencode($item['username']); ?>" class="window {title: 'Edit Account'}">
    <img src="images/edit.png" border="0" alt="Edit Account" title="Edit Account" class="function"></a>

    <?php if( $item['status'] == 'suspended' ): ?>
    <img src="images/disabled.png" border="0" alt="Activate Account" title="Activate Account" class="function click" onclick="statusSelected('<?php echo $item['username']; ?>', 'activate')">
    <?php else: ?>
    <img src="images/enabled.png" border="0" alt="Suspend Account" title="Suspend Account" class="function click" onclick="statusSelected('<?php echo $item['username']; ?>', 'suspend')">
    <?php endif; ?>

    <a href="index.php?r=lxShSearchLinks&field=username&search=<?php echo $item['username']; ?>">
    <img src="images/go.png" border="0" alt="View Links" title="View Links" class="function"></a>
    <a href="index.php?r=lxShSearchComments&field=username&search=<?php echo $item['username']; ?>">
    <img src="images/comments.png" border="0" alt="View Comments" title="View Comments" class="function"></a>
    <a href="index.php?r=lxShMailUser&username[]=<?php echo urlencode($item['username']); ?>" class="window function {title: 'E-mail Account'}">
    <img src="images/mail.png" width="12" height="12" alt="E-mail" title="E-mail"></a>
    <img src="images/trash.png" border="0" alt="Delete" title="Delete" class="function click" onclick="deleteSelected('<?php echo $item['username']; ?>');">

    <?php if( $item['status'] == 'pending' || $item['status'] == 'unconfirmed' ): ?>
    <div id="new_<?php echo $item['username']; ?>" style="width: auto; margin-top: 5px">
    <img src="images/check.png" border="0" alt="Approve" title="Approve" class="click function" onclick="processNew('<?php echo $item['username']; ?>', 'approve')">
    <img src="images/x.png" border="0" alt="Reject" title="Reject" class="click function" onclick="processNew('<?php echo $item['username']; ?>', 'reject')">
    <br />
    <select name="email[<?php echo $item['username']; ?>]" id="email_<?php echo $item['username']; ?>" style="width: 150px;">
      <option value="">No E-mail</option>
      <option value="approval" selected="selected">Approval E-mail</option>
      <?php echo OptionTagsAdv($GLOBALS['REJECTIONS'], null, 'email_id', 'identifier', 30); ?>
    </select>
    </div>
    <?php endif; ?>
  </td>
</tr>
