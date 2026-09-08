{literal}
	<script>
		rpc_debug = true;
		
		function update()
		{
			select_all_options( 'firms_current' );
			
			loadXMLDoc2( 'save', 3 );
			return false;
		}
		
		function copy_option_to( lid, rid, direction )
		{
			clear_flag = false;
			if( direction == 'right' )
			{
				sid = lid;
				id = rid;
			}
			else
			{
				sid = rid;
				id = lid;
			}
			obj = document.getElementById( sid );
			
			if( obj.options.length && obj.selectedIndex != -1 )
			{
				while( obj.selectedIndex != -1 )
				{
					OPT = obj.options[obj.selectedIndex];
					vSEL = document.getElementById(id);
					nOPT = document.createElement( 'OPTION' );
					nOPT.value = OPT.value;
					nOPT.text = OPT.text;
					
					for( i = 0; i < vSEL.options.length; i++ )
					{
						if( vSEL.options[i].text > nOPT.text && vSEL.options[i].value != '' )break;
					}
					
					if( OPT.value == '' )
					{
						i = 0;
						clear_flag = true;
					}
					
					vSEL.add( nOPT, ( isIE ) ? i : vSEL.options[i] );
					obj.remove(obj.selectedIndex);
				}
				
				if( direction == 'right' && clear_flag )
				{
					while( vSEL.options.length > 1 )
						vSEL.remove( 1 );
					
					obj.disabled = true;
				}
				
				if( direction == 'left' && clear_flag )
				{
					vSEL.disabled = false;
				}
			}
		}		
		
		function changeType() {
			var val = $('bank').checked;
			var obj = $('sBank');
			var obj2 = $('sBank2');
			var capt = $('sCapt');
			
			if ( !val ) {
				//alert(val);
				obj.style.display = 'none';
				obj2.style.display = 'none';
				capt.innerHTML = 'касова';
				self.resizeBy(0, -270);
			} else {
				obj.style.display = 'block';
				obj2.style.display = 'block';
				capt.innerHTML = 'банкова';
				self.resizeBy(0, 270);
			}
		}
	</script>
{/literal}

<div class="content ui-nomenclature-dialog-shell">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-organization-dialog ui-bank-account-dialog" onsubmit="return update();">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="cash" name="cash" value="0">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} <span id="sCapt">банкова</span> сметка</div>
		
		<table class="input ui-nomenclature-form">
			<tr class="odd">
				<td>Наименование:</td>
				<td>
					<input type="text" name="sNameAccount" id="sNameAccount" />
				</td>
			</tr>
			
			<tbody id="sBank">
			
				<tr class="odd">
					<td>Име на банката:</td>
					<td>
						<input type="text" name="sNameBank" id="sNameBank" />
					</td>
				</tr>
				
				<tr class="odd">
					<td>IBAN:</td>
					<td>
						<input type="text" name="sIBAN" id="sIBAN" />
					</td>
				</tr>
				
				<tr class="odd">
					<td>BIC:</td>
					<td>
						<input type="text" name="sBIC" id="sBIC" />
					</td>
				</tr>
			
			</tbody>
		</table>
		
		<div id="sBank2">
			<fieldset class="ui-nomenclature-fieldset">
			<legend>Фирми, за които е характерна сметката:</legend>
				<div class="ui-bank-account-transfer">
					<select name="firms_all" id="firms_all" size="10" ondblclick="copy_option_to( 'firms_all', 'firms_current', 'right' );" multiple="multiple"></select>
					<div class="ui-bank-account-transfer-controls">
						<button type="button" class="ui-nomenclature-transfer-button" title="Добави фирма" onclick="copy_option_to( 'firms_all', 'firms_current', 'right' );"><span class="ui-icon ui-icon-right" aria-hidden="true"></span></button>
						<button type="button" class="ui-nomenclature-transfer-button" title="Премахни фирма" onclick="copy_option_to( 'firms_all', 'firms_current', 'left' );"><span class="ui-icon ui-icon-left" aria-hidden="true"></span></button>
					</div>
					<select name="firms_current[]" id="firms_current" size="10" ondblclick="copy_option_to( 'firms_all', 'firms_current', 'left' );" multiple="multiple"></select>
				</div>
			</fieldset>

			<div class="ui-bank-account-invoice-option">
				<input type="checkbox" id="is_on_invoice" name="is_on_invoice" value="1" class="ui-nomenclature-checkbox" />
				<label for="is_on_invoice">Показвай сметката във фактура</label>
			</div>
			
		</div>
		
		<table class="input ui-nomenclature-actions">
			<tr class="odd">
				<td class="ui-bank-account-type-cell">
					<input type="checkbox" id="bank" name="bank" value="1" class="clear ui-nomenclature-checkbox" onclick="changeType();" checked />
					<label for="bank">Банкова сметка</label>
				</td>
				<td class="ui-nomenclature-actions-buttons">
					<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button type="button" onclick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

{literal}
<script>
	loadXMLDoc2( 'get' );
	
	rpc_on_exit = function() {
		var cash = $('cash').value;
		
		if ( parseInt(cash) == 1 ) {
			$('bank').checked = false;
			changeType();
		}
			
		rpc_on_exit = function() {}
	}
</script>
{/literal}
