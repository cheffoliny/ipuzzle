{literal}
<script>
	rpc_debug = true;
		
	function close_form() {
		if( ! window.opener || window.opener.closed )
		{
			window.close();
			return;
		}
			window.opener.loadXMLDoc('result');
			window.close();
		}
	function submit_form()
	{
		loadXMLDoc2('updateAssetInfo',2);		
	}
	function loadNomenclatures()
	{
		loadXMLDoc2('setNomenclatureByGroup');
	}
	function cangeAttributesFset()
	{
		
		loadXMLDoc2('setAttributesFieldByNomenc');
	}
	
	function ChangeAmortization() {
		
		id = $('nID').value;
		dialogChangeAmortization(id);
	}
	
	rpc_on_exit = function( nCode )	{
		if( !parseInt( nCode ) ) {
			
			var a =$("insertID");
			var b =$("insertName");
			
			var assId = $('nId');
			

			if( ! window.opener || window.opener.closed )
				return;
				
				if(assId.value == 0){
					//if(opener.document.getElementById('nIDAssetSource')){
					opener.document.getElementById('nIDAssetSource').value = a.value;
					//}
					//if(opener.document.getElementById('sAssetSource')){
					opener.document.getElementById('sAssetSource').value = b.value;
					//}
				}
				
			//alert(opener.name);
			if(opener.document.getElementById('nIDAssetSourse')){
			//alert(opener.document.getElementById('nIDAssetSource').value);// = a.value;
			opener.document.getElementById('nIDAssetSource').value = a.value;
			}
			if(opener.document.getElementById('sAssetSourse')){
			opener.document.getElementById('sAssetSource').value = b.value;
			}
		}
	}
</script>
<style>
	.input200
	{
		width:200px;
	}
	.input100
	{
		width:120px;
	}
	.flow
	{
		ovrflow:auto;
	}
	ltd
	{
		text-align:right;
	}
	
	
</style>
{/literal}
<form id="form1" onsubmit="return false;">
<dlcalendar click_element_id="img_date_from" input_element_id="invoice_date" tool_tip="Изберете дата"></dlcalendar>
  <input type="hidden" id="iframe_id" name=frame_id/>	
  <input type="hidden" id="nID" name="nID" value="{$nID|default:0}">
  <input type="hidden" id="insertID" name="insertID" />
  <input type="hidden" id="insertName" name="insertName" />
  <input type="hidden" id="first_time" name="first_time">
  <div class="page_caption">{if $nID}Редактиране{else}Добавяне{/if} на актив</div>
  	<table  cellspacing="0" cellpadding="0" width="100%"  border="0" id="filter" >
  		<tr>
  			<td>
  				{include file=asset_info_tabs.tpl}
  				<br>
  			</td>
  		</tr>
  	</table>
  	
	<table class="input" border="0"  style="width:600px;float:left;">
		<tr>
			<td colspan="4">
						
			</td>
		</tr>
		<tr class="even">
			<td style="width:120px;text-align:right;">
				Инвентарен номер:
			</td>
			<td align="left">
				<input type="text" name="id" id="id" style="width:50px;" disabled />
			</td>
			<td style="text-align:right;width:60px;">
				Име :
			</td>
			<td>
				<input type="text" id="name" name="name" style="width:350px;"/>
			</td>
		</tr>
		<tr class="odd">
			<td style="text-align:right;">
				&nbsp;
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				&nbsp;
			<td>
		</tr>
	</table>
	<fieldset style="float:right;margin-bottom:20px;margin-top:0px;margin-right:10px;width:370px;height:170px;" >
		<legend>Допълнителни атрибути </legend>
	
			<div id="me" style="height:160px;width:100%;overflow:auto;">

				
			</div>
	</fieldset>
	<table class="input" style="width:600px;" >
		<tr class="even">
			<td style="text-align:right;">
				Група :
			</td>
			<td >
				<select id="id_group" name="id_group"  onchange="loadNomenclatures();"></select>
			</td>
			
			<td style="text-align:right;">
				Остатъчен срок:
			</td>
			<td>
				<input type="text" id="rest_term" name="rest_term" class="input100" disabled="disabled" />
			</td>	
		</tr>
		<tr class="odd">
			<td style="text-align:right;">
				Номенклатура:
				
			</td>
			<td >
				<select id="id_nomenclature" name="id_nomenclature" onchange="cangeAttributesFset();"></select>
			</td>
			<td style="text-align:right;">
				Цена на придобиване:
			</td>
			<td style="">
				<input type="text" name="aquire_price" id="aquire_price" class="input100" />
			</td>			
		</tr>
		<tr class="even">
			<td style="text-align:right;">
				Статус:
			</td>
			<td style="">
				<input type="text" id="status" name="status" class="input200" disabled="disabled"/>
					
				
			</td>
			
			<td style="text-align:right;">
				Остатъчна стойност:
			</td>
			<td>
				<input type="text" id="rest_price" name="rest_price" class="input100" disabled="disabled"/>
			</td>
		</tr>
		<tr class="odd">
			<td style="text-align:right;">
				М.О.Л.:
			</td>
			<td style="">
				<input type="text" id="mol" name="mol" readonly="readonly" class="input200"/>
			</td>
			
			<td style="text-align:right;">
				Номер на фактура: <!--документ за придобиване-->
			</td>
			<td>
				<input type="text" name="invoice_num" id="invoice_num" class="input100" />
			</td>
		</tr>
		<tr class="even">
			<td style="text-align:right;">
				Дата&nbsp;на&nbsp;придобиване:
			</td>
			<td>
				<input type="text" name="enter_date" id="enter_date" class="input200" disabled="disabled"/>
			</td>
			<td colspan="2" align="right">
				Дата&nbsp;на&nbsp;фактура:
			
				<input type="text"  onkeypress="return formatDate(event, '.');" name="invoice_date" id="invoice_date" size="10" maxlength="10" title="ДД.ММ.ГГГГ">
				<img src="images/cal.gif" id="img_date_from">
			</td>
		</tr>
		<tr>
			<td style="text-align:right;">
				Амортизационен&nbsp;период:
			</td>
			<td>
				<input type="text" name="amort_period" id="amort_period" class="input100" />&nbsp;
				<button name="change" id="change" style="width:50px;" onclick="ChangeAmortization();">Промени</button>
			</td>
			<td><input type="text" name="tips" id="tips" style="text-align:left;" class="clear" readonly /></td>
			<td>&nbsp;</td>
		</tr>
		
	</table>
	<!--<p style="margin-top:30px;">Атрибути на актива :</p>-->
	<!--<fieldset style="float:right;margin-bottom:20px;" ><!--<style="width:40%;" >
		<legend>Допълнителни атрибути </legend>
	
			<div id="me" style="height:180px;width:100%;overflow:auto;">

				
			</div>
	</fieldset>-->
		<!--<iframe width="100%" scrolling="auto" id="attributes" name="attributes" frameborder="0">
	</iframe>-->
	<table  class="input" style="margin-top:40px;">
						<tr valign="top" class="odd">
							<td valign="top" align="right" width="900px">
								<button class="search" onclick="return submit_form();"><img src="images/confirm.gif"/>Потвърди</button>&nbsp;
							</td>
							<td valign="top" align="right" width="100px">
								<button id="b100" onclick="close_form();"><img src="images/cancel.gif" />Затвори</button>
							</td>
							
						</tr>
					</table>
</form>

<script>
	{if !$right_edit}
		
		if( form=document.getElementById('form1') )  
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
	{/if}
	{if $right_edit_change}
		$('change').disabled = false;
	{/if}
	
	loadXMLDoc2('setAssetInfo');
</script>

		