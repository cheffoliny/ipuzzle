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
<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />

	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Активи-АТРИБУТИ</td>
			<td class="buttons"> 
				<button class="search" onclick="openAttribute();"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button> 
				
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2('result');
</script>
