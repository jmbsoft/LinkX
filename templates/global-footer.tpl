<div id="footer-spacer"></div>
</div>
<!-- END centered-content -->
</div>
<!-- END main-content -->
</div>
<!-- END non-footer -->

<div id="footer">
<div id="footer-text">
&copy; {$t_timestamp|tdate('Y')} {$config.domain|htmlspecialchars}<br />
</div>
</div>

<script type="text/javascript">
var links = document.getElementsByTagName('a');

for( i = 0; i < links.length; i++ )
{
    if( links[i].id.match(/^\d+$/) )
    {
        links[i].onclick = click;
    }
}

function click()
{
    img = new Image();
    img.src = '{$config.base_url}/click.php?id='+this.id+'&amp;f=1';
    return true;
}
</script>

</body>
</html>