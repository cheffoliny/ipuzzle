{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		function editPatruls(id) {
			dialogSetupPatruls(id);
		}
		
		function delPatruls(id) {
			if ( confirm('Наистина ли желаете да премахнете патрулите от този обект?') ) {
				$('nID').value = id;
				rpc_on_exit = function(nCode) {
					if (!parseInt(nCode, 10)) loadXMLDoc2('result');
				};
				loadXMLDoc2('delete', 1);
			}
		}
		
	</script>
{/literal}
<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-patrol-list ui-patrol-callsigns-list" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	
	<header class="ui-patrol-heading ui-nomenclature-heading">
		<h1><span class="ui-icon ui-icon-radio" aria-hidden="true"></span> Патрули – позивни</h1>
		{if $right_edit}<button type="button" class="btn btn-primary" onclick="editPatruls(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>{/if}
	</header>
	
	<div class="ui-patrol-filter ui-patrol-filter-compact ui-nomenclature-filter-wrap">
		<label for="nIDFirm">Фирма</label>
		<select name="nIDFirm" id="nIDFirm" class="form-control"></select>
		<button type="button" class="btn btn-primary" onclick="loadXMLDoc2('result');"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button>
	</div>
	
	<div id="result" class="ui-patrol-result"
		rpc_excel_panel="off"
		rpc_resize="off"
		rpc_paging="off"></div>

</form>

<script>
	loadXMLDoc2('load');
</script>

