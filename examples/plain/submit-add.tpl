{assign var=$page_title value="Add a Link"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Add a Link
</div>
</td>
</tr>
</table>

<form method="POST" action="{$config.base_url}/submit.php">

<table align="center" width="800" cellpadding="3" cellspacing="2">
{if $errors}
<tr>
<td colspan="2" class="error">
{$errors}
</td>
</tr>
{/if}
{* Don't display the link submission form if the user is not logged in but you are requiring it for link submission *}
{if $config.user_for_links && !$account}
<tr>
<td colspan="2" class="error">
To submit a link you will need to <a href="{$config.base_url}/account.php?r=login&u={$ref_url|urlencode}" class="link">login</a> to your account.  If you do
not yet have an account, please <a href="{$config.base_url}/account.php?r=register" class="link">create one</a>.
</td>
</tr>
{else}
{if !$account}
<tr>
<td colspan="2" class="notice">
You are currently not logged in.  If you would like to have your link submissions recorded under your account so
they can be more easily managed, please <a href="{$config.base_url}/account.php?r=login&u={$ref_url|urlencode}" class="link">login</a> before you submit your link.
</td>
</tr>
{else}
<tr>
<td colspan="2" class="notice">
You are currently logged in as {$account.username|htmlspecialchars} and this link will be associated with your account for easy maintenance.
</td>
</tr>
{/if}

{* Only show if user is not logged in, otherwise this information will be taken from the account *}
{if !$account}
<tr>
<td width="150" align="right">
<b>Name</b>
</td>
<td>
<input type="text" size="40" name="name" value="{$link.name|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td>
<input type="text" size="50" name="email" value="{$link.email|htmlspecialchars}" />
</td>
</tr>
{/if}
<tr>
<td width="150" align="right">
<b>Site URL</b>
</td>
<td>
<input type="text" size="80" name="site_url" value="{$link.site_url|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Recip URL</b>
</td>
<td>
<input type="text" size="80" name="recip_url" value="{$link.recip_url|htmlspecialchars}" /><br />
<span class="small">This is the page at your site where you will be placing the link to our site</span>
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Title</b>
</td>
<td>
<input type="text" size="70" name="title" value="{$link.title|htmlspecialchars}" /><br />
<span class="small">Must contain between {$config.min_title_length} and {$config.max_title_length} characters</span>
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Description</b>
</td>
<td>
<textarea name="description" rows="6" cols="90">{$link.description|htmlspecialchars}</textarea><br />
<span class="small">Must contain between {$config.min_desc_length} and {$config.max_desc_length} characters</span>
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Keywords</b>
</td>
<td>
<input type="text" size="80" name="keywords" value="{$link.keywords|htmlspecialchars}" /><br />
<span class="small">You may submit up to {$config.max_keywords} keywords; please separate them by spaces, not commas</span>
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Category</b>
</td>
<td>
{if $categories}
<select name="category_id">
{foreach var=category from=$categories}
  {if !$category.hidden}
  <option value="{$category.category_id|htmlspecialchars}"{if $category.category_id == $link.category_id} selected="selected"{/if}{if $category.locked} disabled="disabled"{/if}>{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}</option>
  {/if}
{/foreach}
</select>
{else}
<input type="hidden" name="category_id" value="{$category.category_id|htmlspecialchars}" />
{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}
{/if}
</td>
</tr>

{* Allow user to set password if not requiring user account for submission *}
{if !$config.user_for_links && !$account}
<tr>
<td width="150" align="right">
<b>Password</b>
</td>
<td>
<input type="password" size="20" name="password" value="{$link.password|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Confirm Password</b>
</td>
<td>
<input type="password" size="20" name="confirm_password" value="{$link.confirm_password|htmlspecialchars}" />
</td>
</tr>
{/if}

{* Show the user defined fields *}
{foreach var=field from=$user_fields}
  {if $field.on_submit}
    {if $field.type == FT_CHECKBOX}
<tr>
<td width="150" align="right">
&nbsp;
</td>
<td>
{field from=$field value=$field.value}
<b><label for="{$field.name|htmlspecialchars}">{$field.label|htmlspecialchars}</label></b>
</td>
</tr>
    {else}
<tr>
<td width="150" align="right">
<b>{$field.label|htmlspecialchars}</b>
</td>
<td>
{field from=$field value=$field.value}
</td>
</tr>
    {/if}
  {/if}
{/foreach}

{if $config.link_captcha}
<tr>
<td width="150" align="right">
<b>Verification</b>
</td>
<td>
<img src="{$config.base_url}/code.php" border="0">
<input type="text" name="captcha" size="20" /><br />
<span class="small">Copy the characters from the image into the text box for verification</span>
</td>
</tr>
{/if}
<tr>
<td align="center" colspan="2">
<button type="submit">Submit Link</button>
</td>
</tr>
{/if}
</table>

<input type="hidden" name="r" value="addlink">
</form>

{include filename="global-footer.tpl"}