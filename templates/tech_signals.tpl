{literal}
	<script>
		var timerID = 0;		// Инициализираме таймера
		var hex = 255;  		// Инициализирам цвета	
		var tmpObj = '';

		function fadetext(obj) { 
			if ( tmpObj != '' && tmpObj != obj ) {
				hex = 255;

				try {
					tmpObj.style.color = "rgb(255,255,255)";
					tmpObj = obj;
				} catch(e) {
					alert(e.description);
				}
			}

			if ( tmpObj == '' ) {
				tmpObj = obj;
			}
					
			if ( hex > 0 ) { 	// Цвета не е черен
				hex -= 11; 		// Потъмнявам
				
				try {
					obj.style.color = "rgb(" + hex + "," + hex + "," + hex + ")";
					 
					if ( tmpObj != '' ) {
						setTimeout("fadetext(tmpObj)", 20); 
					}
				} catch(e) {
					//alert(e.description);
				}
				
			} else {
				hex = 255; 		// Връщаме цвета до начално положение
				tmpObj = '';
			}
		}

		function updateNow() {
			var id = document.getElementById('nID');
			var status = document.getElementById('status');
			var flag = document.getElementById('flag');
			
			obj = parseInt(document.getElementById('nObject').value);
			
			if ( obj < 0 ) return 0;
			
			tbody = document.getElementById("tbl_result");
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
								/*
								try {
									var len = key.length;
								} catch (er) {
									return 0;
								}
								*/	
								var i = 0;
								flag.value = 0;
								//alert(i);					
								for ( var i = 0; i < key.length; i++ ) {
									// kym tablicata
									//alert(key[i]);	
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
									cell2.name 		= id_obj[i];
									cell2.id 		= 'cell2'+i;
									cell3.name 		= id_obj[i];									
									cell2.onclick = function() {
										dialogObjectArchiv( parseInt(this.name) );
									};
									cell3.onclick = function() {
										dialogObjectArchiv( parseInt(this.name) );
									};									
									cell2.style.cursor = 'pointer';
									cell2.style.color = '#2562BE';
									cell2.style.fontWeight = 'bold';
									cell3.style.cursor = 'pointer';
									cell3.style.color = '#2562BE';
									cell3.style.fontWeight = 'bold';
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

			obj = parseInt(document.getElementById('nObject').value);

			if ( ((status.value == 'start') || (once == 'once')) && (obj >= 0) ) {

				if ( parseInt(flag.value) > 0 ) {
					if ( parseInt(flag.value) > 4 ) {
						flag.value = 0;
						updateNow();
					}
				} else { //alert(flag.value);
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

<form action="" name="form1" id="form1" onSubmit="return false;" class="form-horizontal" role="form">
	<input type="hidden" id="nID"	 name="nID"		value="0"/>
	<input type="hidden" id="lastID" name="lastID"	value="0"/>
	<input type="hidden" id="status" name="status"	value="" />
	<input type="hidden" id="flag"	 name="flag"	value="0"/>

	<ul class="nav nav-tabs nav-intelli">
		<li class="nav-item text-center" title="Мониторинг"><a class="nav-link inactive" href="#">Сигнали от СОТ - МОНИТОРИНГ</a></li>
	</ul>

	<div>
		<div class="row justify-content-start pl-3 py-2 table-secondary">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-handshake fa-fw" data-fa-transform="right-22 down-10" title="Тип на контрагента..."></span>
					</div>
					<input class="form-control" type="text" name="nObject" id="nObject" value="0" />
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm">
					<button class="btn btn-sm btn-success mr-2" name="Button" onclick="stopStart();"><i class="far fa-plus"></i> Мониторинг </button>
					<button class="btn btn-sm btn-primary" type="button" name="Button" onclick="monitor('once');"><i class="far fa-plus"></i> Обнови </button>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">

				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">

				</div>
			</div>
			<div class="col-6 col-sm-8 col-lg-4 pl-3">
				<div class="input-group input-group-sm">

				</div>
			</div>
		</div>
	</div>
	<div id="result"></div>
	<table class="table table-sm table-striped table-dark" id="okoto" >
		<tr class="bg-primary intelliheader" id="main">
			<th style="width: 130px;" >час</th>
			<th style="width: 70px;" >номер</th>
			<th >обект</th>
			<th >сигнал</th>
			<th style="width: 80px;">тип</th>
			<th style="width: 30px;">%</th>
		</tr>
		<tbody id="tbl_result">	</tbody>
	</table>

</form>

<script> 
	//onInit();
</script>