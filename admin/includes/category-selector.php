<?php
if( !defined('LINKX') ) die("Access denied");

$defaults = array('type' => 'checkbox', 'root' => false);
$_REQUEST = array_merge($defaults, $_REQUEST);

ArrayHSC($_REQUEST);

$type = $_REQUEST['type'];
$field = $_REQUEST['field'];

include_once('includes/header.php');
?>

<script language="JavaScript">
$(function()
{
    <?php if( $_REQUEST['ids'] != '' ): ?>
    expandCategoryDeep('<?php echo $_REQUEST['ids']; ?>');
    <?php endif; ?>

    $('.category-expander').bind('click', expandCategory);
    $('input:<?php echo $type; ?>').bind('click', updateSelectedCategories);
});

function expandCategory()
{
    var category_expander = this;
    var div = $(this).next('div');
    var data = $(this).data();

    if( div.attr('class') != undefined )
    {
        if( div.css('height') != '0px' )
            div.css({height: '0px', overflow: 'hidden'});
        else
            div.css({height: 'auto', overflow: 'auto'});
    }
    else
    {
        div.attr('class', 'category-tree-section {id: '+data.id+'}');
        $.ajax({type: 'POST',
                url: 'ajax.php',
                dataType: 'json',
                data: 'r=lxExpandCategory&id=' + data.id + '&type=<?php echo $type; ?>',
                error: function(request, status, error) { },
                success: function(json)
                         {
                            if( json.status == JSON_SUCCESS )
                            {
                                div.html(json.html);
                                div.children('.category-expander').bind('click', expandCategory);
                                div.children('input').bind('click', updateSelectedCategories);
                            }
                         }
                });
    }
}

function expandCategoryDeep(id)
{
    $.ajax({type: 'POST',
            url: 'ajax.php',
            dataType: 'json',
            data: 'r=lxExpandCategoryDeep&id=' + id + '&type=<?php echo $type; ?>',
            error: function(request, status, error) { },
            success: function(json)
                     {
                        if( json.status == JSON_SUCCESS )
                        {
                            $.each(json.results, function(index, item)
                                                 {
                                                     var category_expander = $('#cat-id-'+item.parent);
                                                     var div = category_expander.next('div');
                                                     var data = category_expander.data();

                                                     if( div.attr('class') != undefined )
                                                     {
                                                         if( !$.browser.msie )
                                                            div.css({height: 'auto', overflow: 'visible'});
                                                         else
                                                            div.css({height: 'auto'}).show();
                                                     }
                                                     else
                                                     {
                                                         if( item.html )
                                                         {
                                                             div.attr('class', 'category-tree-section {id: '+data.id+'}');
                                                             div.html(item.html);
                                                             div.children('.category-expander').bind('click', expandCategory);
                                                             div.children('input:<?php echo $type; ?>').bind('click', updateSelectedCategories);
                                                         }
                                                     }
                                                 });


                            $.each(id.split(','), function(index, id) { $('#cb-'+id).attr('checked', 'checked'); updateSelectedCategories(); });
                        }
                     }
            });
}

function updateSelectedCategories()
{
    var selected = new Array();
    $('input:checked').each(function() { selected.push(this.value); });

    if( window.opener.updateSelected )
    {
        window.opener.updateSelected(selected.join(','), '#<?php echo $field; ?>');
    }
}
</script>

<div style="margin: 10px">

    <form action="ajax.php" name="search" id="search" method="post">

    <div class="centered margin-top" style="font-weight: bold">
      <input type="text" name="search" value="" onkeypress="return Search.onenter(event)" /> &nbsp;
      <input type="hidden" name="order" value="name">
      <input type="hidden" name="direction" value="ASC">
      <button type="button" onclick="Search.search(true)">Search</button>
    </div>

    <input type="hidden" name="r" value="lxShSearchCategoriesSelector">
    <input type="hidden" name="field" value="name">
    <input type="hidden" name="page" id="page" value="1">
    <input type="hidden" name="per_page" id="per_page" value="20">
    </form>

    <div style="padding: 0px 2px 5px 2px;">
      <div style="float: left; display: none;" id="_matches_">Matches <b id="_start_">?</b> - <b id="_end_">?</b> of <b id="_total_">?</b></div>
      <div id="_pagelinks_" style="float: right; line-height: 0px; padding: 2px 0px 0px 0px;">
      </div>
      <div class="clear"></div>
    </div>

    <table class="list" cellspacing="0">
      <tr id="_activity_" style="display: none;">
        <td colspan="1" class="last centered">
          <img src="images/activity.gif" border="0" width="16" height="16" alt="Working...">
        </td>
      </tr>
      <tr id="_none_" style="display: none;">
        <td colspan="1" class="last alert">
          No categories matched
        </td>
      </tr>
      <tbody id="_tbody_">
      </tbody>
    </table>

    <br />

<div id="category-tree">
  <input name="cb" type="<?php echo $type; ?>" class="<?php echo $type; ?>" value="0" id="cb-0"  <?php if( !$_REQUEST['root'] ) echo ' disabled="disabled"'; ?> />
  <img src="images/folder.png" border="0"> <span class="category-expander {id: 0}" id="cat-id-0">Root</span>
  <div class="category-tree-section {id: 0}">

  <?php
  $result = $DB->Query('SELECT * FROM `lx_categories` WHERE `parent_id`=0 ORDER BY `name`');
  while( $category = $DB->NextRow($result) ):
      ArrayHSC($category);
  ?>
  <input name="cb" type="<?php echo $type; ?>" class="<?php echo $type; ?>" value="<?php echo $category['category_id']; ?>" id="cb-<?php echo $category['category_id']; ?>" />
  <img src="images/folder.png" border="0"> <span class="category-<?php echo $category['subcategories'] > 0 ? 'expander' : 'name' ?> {id: <?php echo $category['category_id']; ?>}" id="cat-id-<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></span>
  <div></div>
  <?php
  endwhile;
  $DB->Free($result);
  ?>

  </div>
</div>

</div>

</body>
</html>


