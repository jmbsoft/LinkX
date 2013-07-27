{assign var=$page_title value="Report a Link"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Report A Link
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
<form method="POST" action="{$config.base_url}/report.php">

<table align="center" width="900" cellpadding="3" cellspacing="2">
{if $errors}
<tr>
<td colspan="2" class="error">
{$errors}
</td>
</tr>
{/if}
<tr>
<td colspan="2">
If this link is no longer working or has some type of problem, please let us know by filling out the form below.
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Site URL:</b>
</td>
<td>
<a href="{$link.site_url|htmlspecialchars}" id="{$link.link_id|htmlspecialchars}" target="_blank" class="link">{$link.site_url|htmlspecialchars}</a>
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Site Title:</b>
</td>
<td>
{$link.title|htmlspecialchars}
</td>
</tr>
<tr>
<td colspan="2" style="padding-left: 80px">
<br />
<b>Please describe why you are reporting this link:</b><br />
<textarea name="message" rows="5" cols="120">{$report|htmlspecialchars}</textarea>
</td>
</tr>
{if $config.report_captcha}
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
<td colspan="2" align="center">
<button type="submit">Submit Report</button>
</td>
</tr>
</table>

<input type="hidden" name="id" value="{$link.link_id|htmlspecialchars}">
<input type="hidden" name="r" value="report">
</form>
</div>

{include filename="global-footer.tpl"}
