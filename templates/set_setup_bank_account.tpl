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

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return update();">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="cash" name="cash" value="0">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} <span id="sCapt">банкова</span> сметка</div>
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="200">Наименование:</td>
				<td>
					<input type="text" name="sNameAccount" id="sNameAccount" class="inp250" />
				</td>
			</tr>
			
			<tbody id="sBank">
			
				<tr class="odd">
					<td width="200">Име на банката:</td>
					<td>
						<input type="text" name="sNameBank" id="sNameBank" class="inp250" />
					</td>
				</tr>
				
				<tr class="odd">
					<td width="200">IBAN:</td>
					<td>
						<input type="text" name="sIBAN" id="sIBAN" class="inp250" />
					</td>
				</tr>
				
				<tr class="odd">
					<td width="200">BIC:</td>
					<td>
						<input type="text" name="sBIC" id="sBIC" class="inp250" />
					</td>
				</tr>
			
			</tbody>
		</table>
		
		<div id="sBank2">
			<br />
			
			<fieldset>
			<legend>Фирми, за които е характерна сметката:</legend>
				<table>
					<tr>
						<td>
							<select name="firms_all" id="firms_all" style="width:200px" size="10" ondblclick="copy_option_to( 'firms_all', 'firms_current', 'right' );" multiple="multiple">
							</select>
						</td>
						<td>
							<button id=b25 name="button" title="Добави Фирма" style="width: 20px;" onClick="copy_option_to( 'firms_all', 'firms_current', 'right' ); return false;"><img src=images/mright.gif /></button></br>
							<button id=b25 name="button" title="Премахни Фирма" style="width: 20px;" onClick="copy_option_to( 'firms_all', 'firms_current', 'left' ); return false;"><img src=images/mleft.gif /></button>
						</td>
						<td>
							<select name="firms_current[]" id="firms_current" style="width:200px" size="10" ondblclick="copy_option_to( 'firms_all', 'firms_current', 'left' );" multiple="multiple">
							</select>
						</td>
					</tr>
				</table>
			</fieldset>
			
		</div>
		
		<br />
		
		<table class="input">
			<tr class="odd">
				<td width="250">
					<input type="checkbox" id="bank" name="bank" class="clear" onclick="changeType();" checked />&nbsp; Банкова сметка
				</td>
				<td style="text-align:right;">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
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