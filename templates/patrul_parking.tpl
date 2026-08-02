{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		
		function onInit()
		{	
			loadXMLDoc2('load');
		}
		
		function onRegionChange() {
			$('nIDRegion').value = 0;
		}
		
		function editParking(id) {
			dialogPatrulParking(id);
		}
		
		function formSubmit() {
			loadXMLDoc2('result');
		}
		
		function delParking(id) {
			if ( confirm('Наистина ли желаете да премахнете записа?') ) {
				$('nID').value = id;
				rpc_on_exit = function(nCode) {
					if (!parseInt(nCode, 10)) loadXMLDoc2('result');
				};
				loadXMLDoc2('delete', 1);
			}
		}
		
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-monitor-report ui-patrol-parking-report" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	<input type="hidden" id="nIDRegion" name="nIDRegion" value="0" />
	<input type="hidden" id="id_firm" name="id_firm" value="1" />
	
	<header class="ui-patrol-heading">
		<h1><span class="ui-icon ui-icon-parking" aria-hidden="true"></span> Патрули – стоянки</h1>
		{if $right_edit}<button type="button" class="btn btn-primary" onclick="editParking(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>{/if}
	</header>
	
	<div class="ui-patrol-filter">
		<label for="nIDFirm">Фирма</label>
		<select class="form-control" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')"></select>
		<label for="nIDOffice">Регион</label>
		<select class="form-control" name="nIDOffice" id="nIDOffice"></select>
		<button type="button" class="btn btn-primary" onclick="formSubmit();"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button>
	</div>
	
	<div id="result" class="ui-monitor-result ui-patrol-result"
		rpc_excel_panel="off"
		rpc_resize="off"
		rpc_paging="off"></div>
</form>

<script> onInit();</script>
