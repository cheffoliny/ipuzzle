<script type="text/javascript">
{literal}
	//rpc_debug = true;	
	rpc_action_script = 'api/api_objects_traning_window.php';		
{/literal}
</script>

<form name="form1" id="form1" autocomplete=off style="margin:0;">
<table class="header" style="width:100%;" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td align="center" style="color:ffffff;font-weight:bold">{$sType}&nbsp;обекти&nbsp;за&nbsp;{$sPerson}</td>
</tr>
</table>
</form>
<iframe src ="templates/objects_traning_window_map.php?GoogleKey={$GoogleKey}&nIDPerson={$nIDPerson}&nIDOffice={$nIDOffice}&dateFrom={$dateFrom}&dateTo={$dateTo}&tType={$tType}" style="width:100%; height: 100%;" frameborder="0" scrolling="No" id="mapFrame" name="mapFrame" ></iframe>

