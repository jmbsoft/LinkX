<tr id="<?php echo $item['link_id']; ?>" class="<?php echo $item['status']; ?>">
  <td valign="top" style="padding-top: 6px; width: 15px;">
    <input class="checkbox autocb" name="link_id[]" value="<?php echo $item['link_id']; ?>" type="checkbox">
  </td>
  <td valign="top" style="width: auto;">
    <div style="float: right; margin-left: 8px;"><b>Sorter:</b> <?php echo StringChop($item[$_REQUEST['order']], 20); ?></div>
    <span id="<?php echo $item['link_id']; ?>_activity"></span>
    <a href="<?php echo $item['site_url']; ?>" target="_blank"><?php echo $item['title']; ?></a>
    <?php if( $item['type'] == 'premium' ): ?>
    <img src="images/premium.png" alt="Premium" title="Premium">
    <?php elseif( $item['type'] == 'featured' ): ?>
    <img src="images/featured.png" alt="Featured" title="Featured">
    <?php endif; ?>
    <br />
    <span style="color: green;"><?php echo StringChopTooltip($item['site_url'], 120); ?></span><br />
    <?php echo $item['description']; ?><br />
    <div class="light">
    (Weight: <?php echo number_format($item['weight'], 2, $C['dec_point'], $C['thousands_sep']); ?>;
     Clicks: <?php echo number_format($item['clicks'], 0, $C['dec_point'], $C['thousands_sep']); ?>;
     Comments: <?php echo number_format($item['comments'], 0, $C['dec_point'], $C['thousands_sep']); ?>;
     Ratings: <?php echo number_format($item['ratings'], 0, $C['dec_point'], $C['thousands_sep']); ?>;
     Avg Rating: <?php echo number_format($item['rating_avg'], 2, $C['dec_point'], $C['thousands_sep']); ?>)
    </div>

    <div style="margin-top: 4px; position: relative;">
    <b>Added:</b>&nbsp;
    <?php echo date(DF_SHORT, strtotime($item['date_added'])); ?>

    <span style="left: 225px; position: absolute;"><b>Modified:</b>&nbsp;
    <?php echo !empty($item['date_modified']) && $item['date_modified'] != DATE_EMPTY ? date(DF_SHORT, strtotime($item['date_modified'])) : '-'; ?></span>

    <span style="left: 475px; position: absolute;"><b>Scanned:</b>&nbsp;
    <?php echo !empty($item['date_scanned']) && $item['date_scanned'] != DATE_EMPTY ? date(DF_SHORT, strtotime($item['date_scanned'])) : '-'; ?></span>
    </div>
    
    <div style="margin-top: 4px; position: relative;">
    <b>Expires:</b>&nbsp;
    <?php echo !empty($item['expires']) && $item['expires'] != DEF_EXPIRES ? date(DF_SHORT, strtotime($item['expires'])) : '-'; ?>

    <span style="left: 225px; position: absolute;"><b>Account:</b>&nbsp;
    <?php echo !empty($item['username']) ? $item['username'] : '-'; ?></span>

    <span style="left: 475px; position: absolute;"><b>Has Recip:</b>&nbsp;
    <?php echo $item['has_recip'] ? 'Yes' : 'No'; ?></span>
    </div>
    
    <div style="margin-top: 4px; position: relative;">
    <b>Name:</b>&nbsp;
    <?php echo $item['name'] ? $item['name'] : '-'; ?>

    <span style="left: 225px; position: absolute;"><b>E-mail:</b>&nbsp;
    <a href="mailto:<?php echo $item['email']; ?>"><?php echo StringChopTooltip($item['email'], 35); ?></a></span>

    <span style="left: 475px; position: absolute;"><b>Submit IP:</b>&nbsp;
    <?php echo $item['submit_ip']; ?></span>
    </div>
    
    <div style="margin-top: 4px;">
    <b>Categories:</b><br />
    <div style="margin-left: 20px;">
    <?php
    $category_result = $DB->Query('SELECT * FROM lx_link_cats JOIN lx_categories USING (category_id) WHERE link_id=?', array($item['link_id']));
    while( $category = $DB->NextRow($category_result) )
    {
        if( !isset($GLOBALS['_category_cache_'][$category['category_id']]) )
        {
            $parts = array();
            $category['path_parts'] = unserialize($category['path_parts']);
            
            foreach( $category['path_parts'] as $path_part )
            {
                $parts[] = htmlspecialchars($path_part['name']);
            }
            
            $GLOBALS['_category_cache_'][$category['category_id']] = join('<b>/</b>', $parts) . '<br />';
        }
        
        echo $GLOBALS['_category_cache_'][$category['category_id']];
    }
    $DB->Free($category_result);
    ?>
    </div>
    </div>
    
    <?php if( $item['recip_url'] ): ?>
    <div style="margin-top: 4px;"><b>Recip URL:</b>&nbsp; <a href="<?php echo $item['recip_url']; ?>" target="_blank"><?php echo $item['recip_url']; ?></a></div>
    <?php 
    endif;
    if( $item['keywords'] ): 
    ?>
    <div style="margin-top: 4px;"><b>Keywords:</b>&nbsp; <?php echo $item['keywords']; ?></div>
    <?php 
    endif;
    if( $item['admin_comments'] ): 
    ?>
    <div style="margin-top: 4px;"><b>Admin Comments:</b>&nbsp; <?php echo $item['admin_comments']; ?></div>
    <?php endif; 
        
    foreach( $GLOBALS['_user_fields_'] as $field ):
        if( !IsEmptyString($item[$field['name']]) ):
    ?>
    <div style="margin-top: 4px;"><b><?php echo htmlspecialchars(StringChop($field['label'], 25)); ?>:</b>&nbsp; <?php echo htmlspecialchars($item[$field['name']]); ?></div>
    <?php endif; endforeach; ?>


    
    <?php
    if( $item['is_edited'] ): 
        $edited = unserialize(base64_decode($item['edit_data']));
        ArrayHSC($edited);
    ?>
    <div style="border: 1px solid #ffe7cb; background-color: #FFFFC8; padding: 3px; margin-left: 20px; margin-top: 8px;" class="edited_span">
    <div style="float: right;">
    <img src="images/check.png" border="0" width="12" height="12" alt="Approve" title="Click to approve" class="click" onclick="return processEdit('<?php echo $item['link_id']; ?>', 'approve')">    
    <img src="images/x.png" border="0" width="12" height="12" alt="Reject" title="Click to reject" class="function click" onclick="return processEdit('<?php echo $item['link_id']; ?>', 'reject')">
    </div>
    <b style="color: #ff9112;">Edited Data</b><br />
    <?php
    foreach( $item as $name => $value )
    {
        if( $name != 'password' && isset($edited[$name]) && $value != $edited[$name] )
        {
            echo "<div class=\"fieldgroup\"><label class=\"lesspad\">" . (isset($GLOBALS['_fields_'][$name]) ? $GLOBALS['_fields_'][$name] : ucwords(str_replace('_', ' ', $name))) . ":</label> " .
                 (preg_match('~^http://~', $edited[$name]) ? '<a href="'.$edited[$name].'" target="_blank">'.$edited[$name].'</a>' : $edited[$name]) . "</div>\n";
        }
    }
    ?>
    <div class="clear"></div>
    </div>
    <?php endif; ?>
  </td>
  <td style="text-align: right; width: 160px;" class="last" valign="top">
    <a href="index.php?r=lxShEditLink&link_id=<?php echo urlencode($item['link_id']); ?>" class="window {title: 'Edit Link'}">
    <img src="images/edit.png" alt="Edit" title="Edit" class="function"></a>
    <a href="index.php?r=lxShScanLink&link_id=<?php echo urlencode($item['link_id']); ?>" class="window {title: 'Scan Link'}">
    <img src="images/search.png" alt="Scan Link" title="Scan Link" class="function"></a>
    <a href="index.php?r=lxShBlacklistLink&link_id=<?php echo urlencode($item['link_id']); ?>" class="window {title: 'Blacklist Link'}">
    <img src="images/blacklist.png" alt="Blacklist Link" title="Blacklist Link" class="function"></a>

    <?php if( !empty($item['username']) ): ?>                
    <a href="index.php?r=lxShUsers&field=username&search=<?php echo urlencode($item['username']); ?>">
    <img src="images/user.png" border="0" alt="View User" title="View User" class="function"></a>
    <?php 
    endif;
    if( $item['comments'] > 0 ):
    ?>
    <a href="index.php?r=lxShSearchComments&field=link_id&search=<?php echo urlencode($item['link_id']); ?>">
    <img src="images/comments.png" border="0" alt="View Comments" title="View Comments" class="function"></a>
    <?php
    endif;
    if( $item['status'] == 'disabled' ):
    ?>
    <img src="images/disabled.png" border="0" alt="Activate Link" title="Activate Link" class="function click" onclick="statusSelected('<?php echo $item['link_id']; ?>', 'activate')">
    <?php elseif( $item['status'] == 'active' ): ?>
    <img src="images/enabled.png" border="0" alt="Disable Link" title="Disable Link" class="function click" onclick="statusSelected('<?php echo $item['link_id']; ?>', 'disable')">
    <?php endif; ?>
    
    
    <img src="images/mail.png" width="12" height="12" alt="E-mail" title="E-mail" class="function click" onclick="mailSelected('<?php echo $item['link_id']; ?>')">
    <img src="images/trash.png" width="12" height="12" alt="Delete" title="Delete" class="function click" onclick="deleteSelected('<?php echo $item['link_id']; ?>')">
    
    <br />
    
    <?php if( $item['status'] == 'pending' || $item['status'] == 'unconfirmed' ): ?>
    <div id="new_<?php echo $item['link_id']; ?>" style="width: auto; margin-top: 5px">
    <img src="images/check.png" border="0" alt="Approve" title="Approve" class="click function" onclick="processNew('<?php echo $item['link_id']; ?>', 'approve')">
    <img src="images/x.png" border="0" alt="Reject" title="Reject" class="click function" onclick="processNew('<?php echo $item['link_id']; ?>', 'reject')">
    <br />
    <select name="email[<?php echo $item['link_id']; ?>]" id="email_<?php echo $item['link_id']; ?>" style="width: 150px;">
      <option value="">No E-mail</option>
      <option value="approval" selected="selected">Approval E-mail</option>
      <?php echo OptionTagsAdv($GLOBALS['REJECTIONS'], null, 'email_id', 'identifier', 30); ?> 
    </select>
    </div>
    <?php endif;?>
  </td>
</tr>
