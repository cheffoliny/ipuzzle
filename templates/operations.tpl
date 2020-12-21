{literal}
	<script>
	
		rpc_debug = true;
		rpc_excel_panel = "off";


		function viewOperation( id )
		{
			dialogOperation( id );
		}
		
		function deleteOperation( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете операцията?' ) )
			{
				$( 'nID' ).value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
		
	</script>
{/literal}



<form id="form1" name="form1" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<table class="input">

		<tr>
			<td class="page_name">
				Операции
			</td>
			
			<td align="right" class="buttons">
				<button id="b70" name="Button" onClick="viewOperation( 0 );" ><img src="images/plus.gif"> Добави </button>
			</td>			
		</tr>
	</table>
	
	<table>
		<tr>
			<td> Наименование: </td>
			<td>
				<input id="sName" name="sName" />
			</td>
			
			<td> Описание: </td>
			<td>
				<input id="sDesc" name="sDesc" />
			</td>
			<td>&nbsp; &nbsp;</td>
			
			<td align="right" >
				<button id="b70" name="Button" onClick="return loadXMLDoc2( 'result' );" ><img src="images/confirm.gif"> Търси </button>
			</td>

		</tr>						
	</table>
<hr/>
		
	<div id="result"></div>
</form>


{literal}
<script>

	loadXMLDoc2( 'result' );
	
</script>
{/literal}