{literal}
	<script>
	
		rpc_debug = true
		
		function getResult()
		{
			var oResultIFrame 	= document.getElementById( "summary_object_finances_regions" );
			var oResultPayment	= document.getElementById( "summary_object_finances_payment" );
			
			if( oResultIFrame && oResultPayment )
			{
				oResultIFrame.src = "page.php?page=summary_object_finances_regions";
				oResultPayment.src = "page.php?page=summary_object_finances_payment";
			}
		}
		
		function getDiagram()
		{
			var sParams = "";
			
			sParams += "id_firm=" + $("nIDFirm").value;
			sParams += "&interval=" + $("nInterval").value;
			
			dialogSummaryFinancesRegionsStat( sParams );
			
			return true;
		}
	
	</script>
{/literal}

<form class="w-100 h-100" name="form1" id="form1" onsubmit="return false;">
	<ul class="nav nav-tabs nav-intelli">
		<li class="nav-item text-center" title="Обекти">
			<a class="nav-link active" href="#">Обобщена справка</a>
		</li>
	</ul>

	<div>
		<div class="row justify-content-start pl-3 py-2 table-secondary">
			<div class="col-3">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						{*Администрация:&nbsp;*}
						<span class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма на административно обслужване"></span>
					</div>
					<select class="form-control" id="nIDFirm" name="nIDFirm" ></select>
				</div>
			</div>
			<div class="col-3">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<i class="fa fa-barcode fa-fw"  data-fa-transform="right-22 down-10" title="Търсене по част от номер"></i>
					</div>
					<select class="form-control" id="nInterval" name="nInterval">
						<option value="3">3 месеца</option>
						<option value="6" selected>6 месеца</option>
						<option value="12">12 месеца</option>
					</select>
				</div>
			</div>
			<div class="col-mx-auto pl-0">
				<div class="btn-group input-group-sm">
						<button class="btn btn-sm btn-primary" name="Button" onclick="getResult();"><i class="fas fa-search"></i> Резултат</button>
						{*<button class="btn btn-sm btn-primary" name="Button" onclick="getDiagram();"><img src="images/glyphicons/stock.png" style="width: 24px; height: 24px;">Диаграми</button>*}
				</div>
			</div>
		</div>
	</div>

	<div id="accordion" class="w-100 h-100">

		<div class="nav nav-tabs navbar-dark bg-faded mb-1" id="headingOne">
			<h5 class="mb-0">
				<button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
					<i class="fa fa-expand fa-lg mr-2 ml-2"></i>...
				</button>
			</h5>
		</div>
		<div id="collapseOne" class="w-100 h-50" aria-labelledby="headingOne" data-parent="#accordion">
			<iframe class="w-100 h-100" frameborder="0" id="summary_object_finances_payment" frameborder=0 src='page.php?page=summary_object_finances_payment'></iframe>
		</div>
		<div class="nav nav-tabs navbar-dark bg-faded mb-1" id="headingTwo">
			<h5 class="mb-0">
				<button class="btn btn-link collapsed float-left" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
					<i class="fa fa-expand fa-lg mr-2 ml-2"></i>...
				</button>
			</h5>
		</div>
		<div id="collapseTwo" class="w-100 h-100 collapse show" aria-labelledby="headingTwo" data-parent="#accordion">
			<iframe class="h-100 w-100" frameborder="0" id="summary_object_finances_regions" src='page.php?page=summary_object_finances_regions'></iframe>
		</div>

	</div>

</form>

{literal}
	<script>
		function onResize()
		{
			var oIFrameTable = document.getElementById( "IFrameTable" );
			
			if( oIFrameTable )
			{
				var myWidth = 0;
				var myHeight = 0;
				
				if( typeof( window.innerWidth ) == 'number' )
				{
					//Non-IE
					myWidth = window.innerWidth;
					myHeight = window.innerHeight;
				}
				else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
				{
					//IE 6+ in 'standards compliant mode'
					myWidth = document.documentElement.clientWidth;
					myHeight = document.documentElement.clientHeight;
				}
				else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
				{
					//IE 4 compatible
					myWidth = document.body.clientWidth;
					myHeight = document.body.clientHeight;
				}
				
				oIFrameTable.height = myHeight - 95;
				oIFrameTable.width = myWidth;
			}
		}
		
		onResize();
		window.onresize = onResize;
		
		loadXMLDoc2( "loadFilter" );
	</script>
{/literal}