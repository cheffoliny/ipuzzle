{literal}
	<script>
		rpc_debug = true;
			
	function openAttribute(id)
	{
		dialogSetAttribute (id);
	}
	
	function deleteAttribute(id)
	{
		
		var a =$("nID");
		a.value = id;
		loadXMLDoc2('delete',1);
	}
		
	</script>
{/literal}
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />

	<table class="page_data">
		<tr>
			<td class="page_name">Активи-АТРИБУТИ</td>
			<td class="buttons"> 
				<button class="search" onclick="openAttribute();"><img src="images/plus.gif"> Добави </button> 
				
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2('result');
</script>