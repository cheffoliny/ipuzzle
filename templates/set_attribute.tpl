{literal}

	<script>
	rpc_debug=true;
//	window.onload=function(){
//		document.getElementById('number').style.display='block';
//		alert(document.getElementById('name').value);
//	}
	 function save()
	 {
	 	loadXMLDoc2('save',3);
	 }
	
	 function onInit() {
	 
	 			loadXMLDoc2('result');
	 }
	 
	function disableDiapazon(obj)
	{
		var invDiv= $('visibility');
		var elements = invDiv.getElementsByTagName('table');
		
		for(i=0; i < elements.length; i++)
		{
			if(elements[i].id == obj.value)
			{
				elements[i].style.display = "block";
			}
			else {
				elements[i].style.display='none';
				elements[i].disabled="disabled";
			}
			
		}
//		var a= document.getElementById("type_values");
//		var b= document.getElementById("type");
//		//alert(b.value);
//		
//		if(b.value == "text") {
//			a.disabled="disabled";
//		}
//		else a.disabled=false;
//		a.value = "";
//		if(b.value=="number"){
//			addDiapazonRow();
//		}
		disableMeasure();
	}

	function disableMeasure()
	{
		var type =$("type");
		var unit =$("id_measure");
		if(type.value!="number"){
			unit.disabled = true;
		}
		else unit.disabled = false;
	}
	function addDiapazonRow()
	{
		var tableRow = document.getElementsByTagName('table')[0].insertRow(2);
		tableRow.className="odd";
		var cell1 = tableRow.insertCell(0);
		//cell1.setAttribute('style','text-align:right;');
		var cell2 = tableRow.insertCell(1);
		cell1.id='diapazon';
		cell1.align="right";
		cell1.innerHTML="Диапазон : ";
		cell2.innerHTML='от:<input type="text" class="input20" style="margin-right:20px;" name="from">       до:<input type="text" class="input20" name="to">';
		
	}	
	
	</script>
	<style>
	.large{
		width:200px;
	}
	.input20
	{
		width:20px;
	}
	td{
		/*text-align:right;*/
	}
	</style>

{/literal}


{if $nID eq 0}
	<div class="page_caption">
		
				Добавяне на нов атрибут
			
	</div>
{else}
	<div class="page_caption" onclick="alert(document.getElementById('diapazon').innerHTML);">
		
				Редакция на атрибут
			
	</div>
	
{/if}
<form id="form1">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<input type="hidden" name="inserted_values" id="inserted_values">
	<table class="input" style="margin-top:50px;">
		<tr class="even">
			<td align="right">
				Име на атрибута :
			</td>
			<td>
				<input type="text" id="name" name="name" class="large" />
			</td>
			
		</tr>
		<tr class= "odd">
			<td align="right">
				Тип на атрибута : 
			</td>
			<td>
				<select id="type" name="type"  onchange="disableDiapazon(this);loadXMLDoc2('setValuesByType')"   >
				
				</select>
			</td>
		</tr>
	</table>
<div id="visibility" style="height:30px;">
	
	
	
</div>
	<table class="input">
		<tr class="odd">
			<td align="right">
				Задължителен:
			</td>
			<td>
				<input type="checkbox" id="is_required" name="is_required" class="clear"/>
			</td>
		</tr>
		<tr class = "even">
			<td id = "first" align="right">
				Мерна единица :
			</td>
			<td id = "second">
				<select id="id_measure" name="id_measure"></select>
			</td>
		</tr>
	</table>

	<table class="input" style="margin-top:50px;">
		<tr>
			<td style="width:150px">
				&nbsp;
			</td>
			<td>
				<button class="search" onclick="save();">Запази</button>
				<button onclick="window.close();" >Затвори</button>
			</td>
		</tr>
	</table>

</form>

{literal}

	<script>
		onInit();

	</script>

{/literal}