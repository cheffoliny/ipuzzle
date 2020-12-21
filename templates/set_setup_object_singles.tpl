{literal}
<script>
	rpc_debug = true;

	function formSubmit() {
		var nID = $('nIDObject').value;
		//loadXMLDoc2('save', 3);
		loadXMLDoc2('save');
		
		rpc_on_exit = function() {
			var check = $('formDataOK').value;
			
			if ( check == 'yes' ) {
				window.opener.parent.location='page.php?page=object_taxes&nID='+nID+'&mobile=0';
				rpc_on_exit = function() {}	
				window.close();
			}
		}
	}
	
	function sum_one(sum) {
		var mult = document.getElementById('nQuantity').value;
		if ( mult < 1 ) mult = 1;
		document.getElementById('nSum').value = (sum*mult);
	}

	function sum_two(mult) {
		var sum = document.getElementById('nPrice').value;
		if ( (sum > 0) && (mult > 0) ) document.getElementById('nSum').value = (sum*mult);
	}
	
	function changeService(obj) {
		var opt = '';
				
		try {
			opt = obj.options[obj.selectedIndex].id;
				
			var mass = new Array( opt.split('@@@') );
			$('sName').value = mass[0][0];
				
			if ( parseInt(mass[0][1]) == 0 ) {
				$('sName').disabled = true;
			} else $('sName').disabled = false;
				
			if ( parseInt(mass[0][2]) == 0 ) {
				$('nQuantity').disabled = true;
			} else $('nQuantity').disabled = false;
		
			if ( parseInt(mass[0][3]) == 0 ) {
				$('nPrice').disabled = true;
			} else $('nPrice').disabled = false;			
		} catch(e) {
			$('sName').disabled = false;
			$('nQuantity').disabled = false;
			$('nPrice').disabled = false;
				
			obj.value = 0;
			//alert(e.description);
		}
	}	
	
	function changeFirm() {
		loadXMLDoc2('result');
	}


</script>
{/literal}
<div class="bs-example">
	<div class="popover-demo">
		<button type="button" class="btn btn-primary popover-top" data-toggle="popover" data-trigger="focus" title="Popover title" data-content="Default popover">Popover on top</button>
		<button type="button" class="btn btn-success popover-right" data-toggle="popover" data-trigger="focus" title="Popover title" data-content="Popover on right.">Popover on right</button>
		<button type="button" class="btn btn-info popover-bottom" data-toggle="popover" data-trigger="focus" title="Popover title" data-content="Popover on bottom.">Popover on bottom</button>
		<button type="button" class="btn btn-warning popover-left" data-toggle="popover" title="Popover title" data-content="Popover on left.">Popover on left</button>
	</div>
	<p><strong>Note:</strong> Click on the buttons to display the popover.</p>
</div>


<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
	<div class="modal-content pb-3">
		<div class="modal-header">
 			<h6 class="modal-title text-white" id="exampleModalLabel">{if $nID} Редакция на{else} Добавяне на{/if} задължение</h6>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body pb-5">

			<input type="hidden" id="nID" name="nID" value="{$nID}">
			<input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObj}">
			<input type="hidden" id="formDataOK" name="formDataOK" value="no">

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма адресат..."></span>
						</div>
						<select class="form-control" name="nIDFirm" id="nIDFirm" onchange="changeFirm();"></select>
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-tags fa-fw" data-fa-transform="right-22 down-10" title="Офис адресат..."></span>
						</div>
						<select class="form-control" name="nIDRegion" id="nIDRegion"></select>
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fab fa-amazon-pay fa-fw" data-fa-transform="right-22 down-10" title="Наименование на задължението..."></span>
						</div>
						<select class="form-control" name="nServices" id="nServices" onchange="changeService(this);"></select>
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fab fa-amazon-pay fa-fw" data-fa-transform="right-22 down-10" title="Наименование на задължението..."></span>
						</div>
						<input class="form-control" type="text" name="sName" id="sName" placeholder="Наименование" />
					</div>
				</div>
			</div>


			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-euro-sign fa-fw" data-fa-transform="right-22 down-10" title="Единична стойност..."></span>
						</div>
						<input class="form-control" type="text" name="nPrice" id="nPrice" onKeyPress="return formatMoney(event);" onKeyUp="sum_one(this.value);" title="Единична цена..." placeholder="Единична стойност..." />
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-balance-scale fa-fw" data-fa-transform="right-22 down-10" title="Количество..."></span>
						</div>
						<input class="form-control" type="text" name="nQuantity" id="nQuantity" onkeypress="return formatNumber(event);" onKeyUp="sum_two(this.value);" title="Количество" placeholder="Количество"/>
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-euro-sign fa-fw" data-fa-transform="right-22 down-10" title="Крайна сума..."></span>
						</div>
						<input class="form-control" type="text" name="nSum" id="nSum" title="Крайна сума" placeholder="Крайна сума" readonly />
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="far fa-calendar-check fa-fw" data-fa-transform="right-22 down-10" title="В сила от..."></span>
						</div>
						<input class="form-control" type="text" name="sPaid" id="sPaid" onkeypress="return formatDate(event, '.');" maxlength="10" title="В сила от - ДД.ММ.ГГГГ" placeholder="В сила от..." />
					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-12 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="far fa-calendar-check fa-fw" data-fa-transform="right-22 down-10" title="Документ №:"></span>
						</div>
						<input class="form-control input-transparent" type="text" name="nDocNum" id="nDocNum" title="Документ №:" placeholder="Документ №:" disabled />
					</div>
				</div>
			</div>

			<div class="row mb-2 pb-5">
				<div class="col-12 pl-4 text-danger">
					* Цените са с ДДС!
				</div>
			</div>

		</div>
	</div>

	<nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
		<div class="col-12 col-sm-12 col-lg-12">
			<div class="input-group input-group-sm text-right">
				<button class="btn btn-sm btn-block btn-primary" onClick="formSubmit();"><i class="fas fa-check"></i> Запази </button>
			</div>
		</div>
	</nav>
</form>

<script>
	loadXMLDoc2('result');
</script>