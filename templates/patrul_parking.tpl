{literal}
	<script>
		rpc_debug = true;
		
		
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
				loadXMLDoc2('delete', 1);
			}
		}
		
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-monitor-report ui-patrol-parking-report" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	<input type="hidden" id="nIDRegion" name="nIDRegion" value="0" />
	<input type="hidden" id="id_firm" name="id_firm" value="1" />
	
	<table class="page_data ui-monitor-heading">
		<tr>
			<td class="page_name">Патрули - СТОЯНКИ</td>
			<td class="buttons">
				{if $right_edit}<button type="button" class="search" onclick="editParking(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search ui-monitor-filter">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
				</td>
				<td align="right">Регион</td>
				<td>
					<select class="default" name="nIDOffice" id="nIDOffice" />
				</td>
				<td align="right"><button type="button" name="Button" class="search" onclick="formSubmit();"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
			</tr>
	  	</table>
	</center>

	<hr>
	
	<div id="result" class="ui-monitor-result"></div>
</form>

<script> onInit();</script>
