{literal}
<script>
	rpc_debug = true;
	
	function openNomenclatureType( id )
	{
		dialogSetSetupNomenclatureType( 'id=' + id, id );
	}

	function deleteNomenclatureType( id )
	{
		if( confirm( 'Ще бъдат изтрити и номенклатурите от този тип!\nНаистина ли желаете да премахнете записа?' ) )
		{
			$('nID').value = id;
			loadXMLDoc2( 'delete', 1 );
		}
	}
</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class="page_data">
		<tr>
			<td class="page_name">Типове Номенклатури</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openNomenclatureType( 0 );" class="btn btn-xs btn-success"><i class="fa fa-plus"></i> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>