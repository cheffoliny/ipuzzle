<script>
{literal}
	rpc_debug = true;
	
	function modifyGroup(id)
	{	
		if(id){
			
			dialogSetGroup(id);
		}
		else dialogSetGroup(0);
		
	}
	function deleteGroup(id)
	{
		var a = $("nID");
		a.value = id;
		loadXMLDoc2('delete',1);
	}
{/literal}
</script>

	<form id="form1" class="ui-nomenclature-list ui-assets-list" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID"/>
		<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Активи-ГРУПИ</td>
			<td class="buttons"> 
				<button type="button" class="search" onclick="modifyGroup(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button> 
				
			</td>
		</tr>
	</table>
	<div id="result"></div>
	
	</form>
<script>

	loadXMLDoc2('result');

</script>
