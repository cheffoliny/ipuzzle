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

	<form id="form1">
		<input type="hidden" id="nID" name="nID"/>
		<table class="page_data">
		<tr>
			<td class="page_name">Активи-ГРУПИ</td>
			<td class="buttons"> 
				<button class="search" onclick="modifyGroup(0);"><img src="images/plus.gif"> Добави </button> 
				
			</td>
		</tr>
	</table>
	<div id="result"></div>
	
	</form>
<script>

	loadXMLDoc2('result');

</script>