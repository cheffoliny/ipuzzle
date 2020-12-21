{literal}

	<script>
		rpc_debug = true;
	
		function onInit() {
			loadXMLDoc2('load');
			
			rpc_on_exit = function() {};
		}
		
		function test() {
			obj = $('account');
			$('sTypeAccount').value = obj.options[obj.selectedIndex].id;
						
			loadXMLDoc2('load');	
			
			rpc_on_exit = function() {};		
		}
		
		function sumResto() {
			var nSum = parseFloat($('sum').value);
			var nCashSum = parseFloat($('cash_sum').value);
			
			var nResto = nCashSum - nSum;
			
			nResto = Math.round(nResto * 100)/100;
			nResto = nResto.toFixed(2);
			
			if(nResto >= 0) {
				$('resto').value = nResto;
			} else {
				$('resto').value = '';
			}
		}
		
		/**
		* PHP sleep() в msecs :)
		*/
		function sleep(delay) {
		    var start = new Date().getTime();
		    while (new Date().getTime() < start + delay);
		}
		
		function confirmBUGGGG() {
			flag = $('flag').value;
			
			if ( flag != '-1' ) {
				//$('flag').value = '-1';

				if ( confirm('В избраните документи има такива ('+flag+'),\n кото не са с указания тип на плащане.\n\nВалидиране/Отказ?') ) {
					loadXMLDoc2('confirm', 3);
				}
			} else {
				$('sbm').disabled = true;
				$('flag').value = '-1';
				loadXMLDoc2('confirm', 3);
			}
		}
		
		
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" name="sIDs" id="sIDs" value="{$sIDs}" />
	<input type="hidden" name="sBank" id="sBank" value="{$sBank}" />
	<input type="hidden" name="sTypeAccount" id="sTypeAccount" value="" />
	<input type="hidden" name="flag" id="flag" value="-1" />

	<div class="page_caption">Групово плащане</div>
	
	<table class="input" style="margin-top:5px;">
		<tr class="even">
			<td align="right">
				Сума
			</td>
			<td>
				<input type="text" name="sum" id="sum" style="width:60px;text-align:right;" onkeypress="return formatMoney(event)" onkeyup="sumResto();"  readonly />&nbsp;лв.
			</td>
		</tr>
		<tr class="odd">
			<td align="right">
				Сметка
			</td>
			<td>
				<select name="account" id="account" onchange="test();"></select>
			</td>
		</tr>
		<tr class="even">
			<td align="right">
				Платено в брой
			</td>
			<td>
				<input type="text" id="cash_sum" name="cash_sum" style="width:60px;text-align:right;" onkeypress="return formatMoney(event)" onkeyup="sumResto();" > лв.
			</td>
		</tr>
		<tr class="odd">
			<td align="right">
				Ресто
			</td>
			<td>
				<input type="text" id="resto" name="resto" style="width:60px;text-align:right;" readonly> лв.
			</td>
		</tr>
		<tr>
			<td colspan="2" align="right">
				<br>
				<button class="search" onclick="confirmBUGGGG(); return false;" id="sbm"><img src="images/confirm.gif">Потвърди</button>
				<button onclick="parent.window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
		
	</table>
</form>

<script>
	onInit();
</script>