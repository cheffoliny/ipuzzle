{literal}
	<script>
		rpc_debug = true;

		function formSearch() {
			var firm = document.getElementById('nIDFirm').value;
			var period = document.getElementById('nPeriod').value;
			
			var URL = document.location+'&firm='+firm+'&period='+period+'&search=1';
			document.location.replace(URL);
		}
	</script>
	
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-assets-report ui-assets-totals-report" onSubmit="return false;">
	<input type ="hidden" id="srch_period" name="srch_period" value="{$search.period}" />
	<input type ="hidden" id="srch_firm" name="srch_firm" value="{$search.nIDFirm}" />
	
	<div class="page_caption">Активи - Обобщена</div>
	
	<center class="ui-nomenclature-filter-wrap">
	
		<div>
			<table class="search ui-nomenclature-filter ui-assets-totals-filter" border="0" width="850">
				<tr align="center">
					<td align="right" width="130">Фирма:&nbsp;</td>
					<td align="left" width="260">
						<select id="nIDFirm" name="nIDFirm" style="width: 250px;" />
					</td>
					<td align="right" width="60">Период:&nbsp;</td>
					<td align="left" width="100">
						<select id="nPeriod" name="nPeriod" style="width: 90px;">
							<option value="3">3 Месеца</option>
							<option value="6">6 Месеца</option>
							<option value="12">12 Месеца</option>
							<option value="0">Всички</option>
						</select>
					</td>

					<td align="left">
						<button type="button" name="Button" onclick="formSearch();"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси </button>
					</td>
				</tr>
			</table>
		</div>
		
	</center>
	
	<hr />


	<div id="result" name="result" class="ui-assets-totals-result" style="height: 360px; overflow: auto;">

		<table class="search ui-assets-totals-table" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<th rowspan="2">Име на фирма</th>
				<th rowspan="2">Месец</th>
				<th colspan="4">Бракувани</th>
				<th colspan="4">Въведени</th>
				<th colspan="4">Придобити</th>
			</tr>

			<tr>
				<th colspan="3">Промяна</th>
				<th>Стойност</th>
				<th colspan="3">Промяна</th>
				<th>Стойност</th>
				<th colspan="3">Промяна</th>
				<th>Стойност</th>
			</tr>

			{foreach name=outer key=key item=item from=$assets}

				{foreach name=bla item=asset from=$item}
				<tr >
					<td class="bla" align="left" nowrap>{$asset.firm}</td>
					<td class="bla" align="left" nowrap>{$asset.date}</td>
					<td class="bla ui-asset-bar-cell ui-asset-bar-left">{if $asset.wasted<0}<span class="ui-asset-bar ui-asset-bar-negative" style="width: {$asset.wasted*-1/$max*100}px;"></span>{/if}</td>
					<td class="bla ui-asset-bar-cell ui-asset-bar-right">{if $asset.wasted>0}<span class="ui-asset-bar ui-asset-bar-positive" style="width: {$asset.wasted/$max*100}px;"></span>{/if}</td>
					<td class="bla ui-asset-value" nowrap>{$asset.wasted|string_format:"%.2f"} €</td>
					<td class="bla ui-asset-value" nowrap>{$asset.sum_wasted|string_format:"%.2f"} €</td>
					<td class="bla ui-asset-bar-cell ui-asset-bar-left">{if $asset.entered<0}<span class="ui-asset-bar ui-asset-bar-negative" style="width: {$asset.entered*-1/$max2*100}px;"></span>{/if}</td>
					<td class="bla ui-asset-bar-cell ui-asset-bar-right">{if $asset.entered>0}<span class="ui-asset-bar ui-asset-bar-positive" style="width: {$asset.entered/$max2*100}px;"></span>{/if}</td>
					<td class="bla ui-asset-value" nowrap>{$asset.entered|string_format:"%.2f"} €</td>
					<td class="bla ui-asset-value" nowrap>{$asset.sum_entered|string_format:"%.2f"} €</td>
					<td class="bla ui-asset-bar-cell ui-asset-bar-left">{if $asset.attached<0}<span class="ui-asset-bar ui-asset-bar-negative" style="width: {$asset.attached*-1/$max3*100}px;"></span>{/if}</td>
					<td class="bla ui-asset-bar-cell ui-asset-bar-right">{if $asset.attached>0}<span class="ui-asset-bar ui-asset-bar-positive" style="width: {$asset.attached/$max3*100}px;"></span>{/if}</td>					
					<td class="bla ui-asset-value" nowrap>{$asset.attached|string_format:"%.2f"} €</td>
					<td class="bla ui-asset-value" nowrap>{$asset.sum_attached|string_format:"%.2f"} €</td>
				</tr>
				
				{/foreach}
				
				<tr>
					<td colspan="11">&nbsp</td>
				</tr>

			{/foreach}

		</table>
	</div>

</form>

{literal}
<script>
	var period = $('srch_period').value;
	var firm = $('srch_firm').value;
	loadXMLDoc2('load');
	
	rpc_on_exit = function() {
		try {
			var div = document.getElementById('result');
			div.style.width = document.body.offsetWidth;
			document.getElementById('nPeriod').value = period;
			document.getElementById('nIDFirm').value = firm;
		} catch(err) {
			// nishto
		}
	}
</script>
{/literal}
