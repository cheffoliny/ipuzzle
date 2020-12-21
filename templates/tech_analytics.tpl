<script type="text/javascript">
    
	rpc_debug = true;
 	rpc_form = "form2";
	rpc_result_area = "result";
	
	
 
	
	
 {if $bViewMap}
{literal}

		InitSuggestForm = function()
		{
			$('nIDOffice').value = $('nOffice').value;
			
			for( var i = 0; i < suggest_elements.length; i++ )
			{
				switch( suggest_elements[i]['id'] )
				{
					case 'nObjectNum':
					case 'sObjectName':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
					break;
				}
			}
		}
		
		function onSuggestObject( aParams )
		{
			var aStuff = aParams.KEY.split( ';' );
			var mapFrame =  $('mapFrame').contentWindow;
			
			$('nIDObject').value = 	aStuff[0];
			$('nObjectNum').value = aStuff[1];
			$('sObjectName').value = aStuff[2];
			$('nIDOffice').value = $('nOffice').value;
			
			initFields();
			
			rpc_on_exit= function() {
				
				rpc_on_exit = function() {}
						
				if( $('lat').value != 0 && $('lon').value != 0 )
				{
					mapFrame.map.setCenter(new mapFrame.GLatLng($('lat').value, $('lon').value ),18);
					mapFrame.ObjectMarker.setLatLng(new mapFrame.GLatLng($('lat').value, $('lon').value ));
					mapFrame.ObjectMarker.show();
				} 
				else 
				{
					mapFrame.map.setCenter( mapFrame.oCenter, mapFrame.nLevel);
					mapFrame.ObjectMarker.setLatLng( mapFrame.oCenter);
					mapFrame.ObjectMarker.show();
				}

			}
			loadXMLDoc2( 'result' );
			$( 'save_button' ).disabled = false;
	
		}
		
		function onChangeObjectNum()
		{
			$('nIDObject').value = 0;
			$('sObjectName').value = "";
		}
		
		function onChangeObject()
		{
			$('nIDObject').value = 0;
			$('nObjectNum').value = "";
		}

		function initFields()
		{
			$('Address').value = '';
			$('lat').value = '';
			$('lon').value = '';
			$( 'confirmed' ).checked = "";
			$( 'save_button' ).disabled = true;
		}
 
		
		function resetAll()
		{
			var mapFrame =  $('mapFrame').contentWindow;
			
			initFields();
			$('nObjectNum').value = '';
			$('sObjectName').value = '';
			mapFrame.map.setCenter( mapFrame.oCenter, mapFrame.nLevel);
			mapFrame.ObjectMarker.setLatLng( mapFrame.oCenter);
			mapFrame.ObjectMarker.hide();
			
		}
		
		function unconfirmedObjects()
		{
			
			dialogTechAnalyticsObjects( $('nIDOffice').value );
			
		}
		
		
		//зарежда обект извикан от справката за непотвърдени обекти
		function PopUpHandler( nIDObject, nIDOffice )
		{
			$('nIDObject').value 	= nIDObject;
			$('nIDOffice').value 	= nIDOffice;

			initFields();
			
			var mapFrame =  $('mapFrame').contentWindow;
			
			rpc_on_exit= function() {
			
				rpc_on_exit = function() {}
						
				if( $('lat').value != 0 && $('lon').value != 0 )
				{
					mapFrame.map.setCenter(new mapFrame.GLatLng($('lat').value, $('lon').value ),18);
					mapFrame.ObjectMarker.setLatLng(new mapFrame.GLatLng($('lat').value, $('lon').value ));
					mapFrame.ObjectMarker.show();
				} 
				else 
				{
					mapFrame.map.setCenter( mapFrame.oCenter, mapFrame.nLevel);
					mapFrame.ObjectMarker.setLatLng( mapFrame.oCenter);
					mapFrame.ObjectMarker.show();
				}

			}
			
			loadXMLDoc2( 'result' );
			
			$( 'save_button' ).disabled = false;
			
		}
		
		
		
		function save() 
		{
			loadXMLDoc2( 'save' );
			rpc_on_exit= function() {
			
				rpc_on_exit = function() {}
				loadXMLDoc2( 'result' );
			}
		}
		
		
		
{/literal}		
{/if}
 
	



    </script>

    <form name="form1" id="form1" action="page.php?page={$page}" method="POST">
	
	<table class = "page_data">
		<tr>
			<td class="page_name">АНАЛИТИК</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td style="width: 150px;" align="right">Тип на справката:&nbsp;</td>
				<td style="width: 220px;">
					<select name="nType" id="nType" style="width: 200px;" >
						<option value="objects" {if $nType eq 'objects'}selected{/if}>Наситеност на обекти</option>
						<option value="passable_time" {if $nType eq 'passable_time'}selected{/if}>Проходимост (последните 2 дена)</option>
					</select>
				</td>
				<td align="left">
					<input id="bObjects" name="bObjects" type="checkbox" value="1" class="clear" {$bObjects}>Обекти&nbsp;&nbsp;
					<input id="bZoom" name="bZoom" type="checkbox" value="1" class="clear" {$bZoom}>Мащаб&nbsp;
				</td>
			</tr>
			<tr>
				<td style="width: 150px;" align="right">Регион&nbsp;</td>
				<td style="width: 220px;">
					<select name="nOffice" id="nOffice" style="width: 200px;" >
						<option value="0">--- Всички ---</option>
						{foreach item=aOffice from=$aOffices}
							<option value="{$aOffice.id}" {if $nSelectedOffice eq $aOffice.id}selected{/if}>{$aOffice.name}</option>
						{/foreach}
					</select>
				</td>
				<td align="right"><button type="submit" name="Button"><img src="images/confirm.gif">Покажи</button></td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
{if $bViewMap}
	<div id="mapcontainer" style="width:900px; float:left;">
 
  	  <iframe src ="templates/tech_analytics_map.php?nLanMin={$nLanMin}&nLanMax={$nLanMax}&nLatMin={$nLatMin}&nLatMax={$nLatMax}&nCenterLat={$nCenterLat}&nCenterLan={$nCenterLan}&GoogleKey={$GoogleKey}&sKmzFile={$sKmzFile}" style="width:100%; height: 500px;" frameborder="0" scrolling="No" id="mapFrame" name="mapFrame" ></iframe>
	    
	    <table>
	    	<tr>
				{foreach from=$aLegend item=aItem }
					<td bgcolor="{$aItem.color}" width="35px" style="font-size:10px; text-align:right; padding:2px">{$aItem.to}</td>
				{/foreach}
	    	</tr>
	    </table>
	
 	</div>
 {/if}
 	
 </form>
 
 
 {if $bViewMap}

 <div id="sideblock" style="width:300px; " >
 <span><a href="javascript:unconfirmedObjects();">Списък обекти</a></span>
<fieldset >
<legend>Обект</legend>
<form id="form2" name="form2" onSubmit="return( false );" >
		<input type="hidden" id="nIDObject" name="nIDObject" value="0" />
		<input type="hidden" id="nIDOffice" name="nIDOffice" value="0" />
		
	<table  class="search" style="text-align:left;">
		<tr>
			<td>Номер: </td>
			<td><input type="text" id="nObjectNum" name="nObjectNum" class="inp200" suggest="suggest" queryType="ActObjByNum" queryParams="nIDOffice" onkeypress="formatDigits( event );" onchange="onChangeObjectNum();" maxlength="12" />
			</td>
		</tr>
		<tr>
			<td>Име: </td>
			<td><input type="text" id="sObjectName" name="sObjectName" class="inp200" suggest="suggest" queryType="objByName" queryParams="nIDOffice" onchange="onChangeObject();" /></td>
		</tr>
 		<tr>	
			<td> Адрес:</td>
			<td><input type="text" id="Address" name="Address" readonly="readonly" class="inp200" /></td>
		</tr>
		<tr>
			<td> Координати: </td>
			<td><input type="text" id="lat" name="lat"  readonly="readonly" class="inp200" /></td>
		</tr>
		<tr>
			 <td>&nbsp;</td>
			<td><input type="text" id="lon" name="lon"   readonly="readonly" class="inp200"/></td>
		</tr>
		<tr>
			<td> Потвърден: </td>
			<td> <input type="checkbox" id="confirmed"  disabled style="border:none" /></td>
		</tr>
		<tr > 
			<td colspan="2" align="center"> 
			<input type="button" id="save_button" value="Запиши" onclick=" save(); " disabled > 
			&nbsp;
			<input type="button" value="Изчисти" onclick="resetAll();">
			</td>
		</tr>
		
	</table>
</fieldset>
 </form>

 <div id="result"> </div>
 
 </div>
 
 {/if}

 