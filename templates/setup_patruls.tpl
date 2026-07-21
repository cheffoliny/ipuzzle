{literal}
	<script>
		rpc_debug = true;
		
		function editPatruls(id) {
			dialogSetupPatruls(id);
		}
		
		function delPatruls(id) {
			if ( confirm('Наистина ли желаете да премахнете патрулите от този обект?') ) {
				$('nID').value = id;
				loadXMLDoc2('delete', 1);
			}
		}
		
	</script>
{/literal}
<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Патрули - ПОЗИВНИ</td>
			<td class="buttons"> 
				{if $right_edit}<button class="search" onclick="editPatruls(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button> 
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
					<select name="nIDFirm" id="nIDFirm" class="form-control" />
				</td>
				<td align="right"><button name="Button" onclick="loadXMLDoc2('result');"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
			</tr>
	  	</table>
	</center>

	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2('load');
</script>

