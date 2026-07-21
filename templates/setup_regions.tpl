{literal}
	<script>
		rpc_debug = true;
		
		function newRegion( id )
		{
			var id_f = document.getElementById( 'id_firm' ).value;
			
			dialogRegion( id, id_f );
		}
		
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Номенклатури - РЕГИОНИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="newRegion( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
		<table class="search table-secondary ui-nomenclature-filter">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default form-control" name="id_firm" id="id_firm" />
				</td>
				<td align="right"><button name="Button" onclick="loadXMLDoc( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		loadXMLDoc( 'generate', 1 );
		loadXMLDoc( 'result' );
		
		function viewRegion( id )
		{
			newRegion( id );
		}

		function deleteRegion( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('id').value = id;
				loadXMLDoc( 'delete' );
			}
		}
	</script>
{/literal}
