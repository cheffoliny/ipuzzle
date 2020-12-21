{literal}
	<script>
		var timerID = 0;	
	
		function updateNow() {
			var id = document.getElementById('nID');
			var lastID = document.getElementById('lastID');
			var status = document.getElementById('status');
			var flag = document.getElementById('flag');
			
			obj = parseInt(document.getElementById('nObject').value);
			
			if ( obj < 1 ) return 0;
			
			tbody = document.getElementById("tbl1"); 
			method = 'GET';
			api_tech_signal = 'api/api_tech_signals.php';
			params = 'obj=' + obj + '&last=' + lastID.value;
			
			try {
				var xmlhttp;
				
				if ( ! xmlhttp ) {
					xmlhttp = getXMLHTTP();
				}
				
				if (xmlhttp) {
				
					if (method == 'GET') {
						xmlhttp.open(method, api_tech_signal + '?' + params, true);
					} else {
						xmlhttp.open(method, api_tech_signal, true);
					}
					
					xmlhttp.onreadystatechange = function() {
						if( xmlhttp.readyState == 4 ) {
							if (xmlhttp.status == 200) {
								try {
									eval(xmlhttp.responseText);
								} catch (eee) {
									// alert(eee.description);
								}
								
								try {
									var len = key.length;
								} catch (er) {
									alert(er.description);
									return 0;
								}
									
								flag.value = 0;
															
								for ( var i = 0; i < key.length; i++ ) {
									// kym tablicata
									if ( key[i] > lastID.value ) lastID.value = key[i];
									row = document.createElement("TR");
									cell1 = document.createElement("TD"); 
									cell2 = document.createElement("TD"); 
									cell3 = document.createElement("TD"); 
									cell4 = document.createElement("TD"); 
									cell5 = document.createElement("TD"); 
									cell6 = document.createElement("TD"); 
									cell1.setAttribute("align", "center");
									cell2.setAttribute("align", "right");
									cell3.setAttribute("align", "left");
									cell4.setAttribute("align", "left");
									cell5.setAttribute("align", "center");
									cell6.setAttribute("align", "right");
									cell1.innerHTML = msgtime[i];
									cell2.innerHTML = num[i];
									cell3.innerHTML = object[i];
									cell4.innerHTML = msg[i];
									cell5.innerHTML = type[i];
									cell6.innerHTML = pass[i] + '%';
									row.appendChild(cell1); 
									row.appendChild(cell2); 
									row.appendChild(cell3); 
									row.appendChild(cell4); 
									row.appendChild(cell5); 
									row.appendChild(cell6); 
									
									try {
										tbody.insertBefore(row, tbody.firstChild);
										tbody.deleteRow(20);
									} catch (ee) {
										//alert(ee.description);
									}
								}
								
							} //else alert("Error: " + xmlhttp.statusText);
						}
					}
					
					xmlhttp.send(null);
				}	

			} catch (e) {
				//alert(e.description);
			}		
						
		}
					
		function stopStart() {
			var status = document.getElementById('status');
			var lastID = document.getElementById('lastID');
			
			if ( status.value == 'start' ) {
				status.value = 'stop';
				clearTimeout(timerID);
				timerID = 0;
			} else {
				status.value = 'start';
				lastID.value = 0;
				monitor(0);
			}
		}		
		
		function monitor(once) {
			var status = document.getElementById('status');
			var flag = document.getElementById('flag');

			//alert(flag.value);
			
			obj = parseInt(document.getElementById('nObject').value);

			if ( ((status.value == 'start') || (once == 'once')) && (obj > 0) ) {

				if ( parseInt(flag.value) > 0 ) {
					if ( parseInt(flag.value) > 4 ) {
						flag.value = 0;
						updateNow();
					}
				} else {
					if ( once == 'once' ) {
						updateNow();
						return 0;
					} else updateNow();
				}

			} else {
				clearTimeout(timerID);
				timerID = 0;
				return 0;
			}
			
			flag.value = parseInt(flag.value) + 1;
			timerID = setTimeout("monitor(0)", 3000);
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<div class="page_caption">МОНИТОРИНГ за обект с номер: {$nID}</div>
	
	<input type="hidden" id="nID" name="nID" value="0" />
	<input type="hidden" id="lastID" name="lastID" value="0" />
	<input type="hidden" id="status" name="status" value="" />
	<input type="hidden" id="flag" name="flag" value="0" />
	
	<center>
		<table class="search">
			<tr>
				<td style="width: 350px; text-align: right;" >Номер на обект:&nbsp;</td>
				<td>
					<input type="text" class="default" name="nObject" id="nObject" value="{$nID}" style="width: 75px; text-align: right;" readonly />
				</td>
				<td align="right"><button name="Button" class="search" onclick="stopStart();"><img src="images/assign.gif">Мониторинг</button></td>
				<td style="width: 300px; text-align: right;">
					<button onclick="monitor('once');"> Обнови </button> 
				</td>			
			</tr>
	  	</table>
	</center>

	<hr>
	
	<div id="result" style="width: 800px; height: 380px; overflow: auto;" >
		<table class="result" id="okoto" >
			<tr>
				<th style="width: 130px;" >час</td>
				<th style="width: 70px;" >номер</td>
				<th >обект</td>
				<th >сигнал</td>
				<th style="width: 80px;">тип</td>
				<th style="width: 30px;">%</td>
			</tr>
			<tbody id="tbl1"></tbody>
		</table>
	</div>

	<div id="search"  style="padding-top:10px;width:800px;">
		<table width="100%" cellspacing=1px>
			<tr valign="top">
				<td valign="top" align="right" width="800px">
					<button id="b100" onClick="window.close();"><img src="images/cancel.gif" />Затвори</button>
				</td>
			</tr>
		</table>
	</div>

</form>

<script> 
	//onInit();
</script>