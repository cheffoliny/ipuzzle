{literal}
	<script>
		rpc_debug = true;
		rpc_method='POST';
			
		InitSuggestForm = function()
		{
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
			
			$('nIDObjectToAttach').value = 	aStuff[0];
			$('nObjectNum').value = 		aStuff[1];
			$('sObjectName').value =		aStuff[2];
		}
		
		function onInit()
		{
			loadXMLDoc2( 'result' );
		}
		
		function viewObject( id )
		{
			var sParams = new String();
			
			if( id )
			{
				sParams = 'nID=' + id;
				
				dialogObjectInfo( sParams );
			}
		}
		
		function detachObject( id )
		{
			if( id != 0 )
			{
				$("nIDObject").value = id;
				
				if( confirm( "Отвързване на избрания обект от клиента?" ) )
				{
					loadXMLDoc2( "detach", 1 );
				}
			}
		}
		
		function attachObject()
		{
			rpc_on_exit = function()
			{
				if( $("nIDClient").value != 0 )
				{
					if( confirm( "Преместване на обекта от клиент " + $("sClient").value + " към текущия клиент?" ) )
					{
						rpc_on_exit = function ()
						{
							$("nIDClient").value = 0;
							$("sClient").value = "";
							
							rpc_on_exit = function() {}
						}
						
						loadXMLDoc2( "attach", 1 );
					}
					else
					{
						$("nIDClient").value = 0;
						$("sClient").value = "";
						
						rpc_on_exit = function() {}
					}
				}
				else
				{
					loadXMLDoc2( "attach", 1 );
					
					rpc_on_exit = function() {}
				}
			}
			
			loadXMLDoc2( "getClient" );
		}

        function onChangeObjectNum()
        {
            if(empty(jQuery('#nObjectNum').val())) {
                $('nIDObjectToAttach').value = 0;
                $('sObjectName').value = "";
            }

        }

        function onChangeObject()
        {
            if(empty(jQuery('#sObjectName').val())) {
                $('nIDObjectToAttach').value = 0;
                $('nObjectNum').value = "";
            }
        }
	</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">

    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="nIDObject" name="nIDObject" value="0" />
    <input type="hidden" id="nIDObjectToAttach" name="nIDObjectToAttach" value="0" />
    <input type="hidden" id="nIDClient" name="nIDClient" value="0" />
    <input type="hidden" id="sClient" name="sClient" value="" />

    {include file='client_tabs.tpl'}

    <div class="container-fluid mb-1">
        <div class="row clearfix mt-2">
            <div class="col-3 col-sm-3 col-lg-3 pl-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-home fa-fw" data-fa-transform="right-22 down-10" title="E-mail..."></span>
                    </div>
                    <input class="form-control" type="text" id="nObjectNum" name="nObjectNum" suggest="suggest" queryType="objByNum" onkeypress="formatDigits( event );" onchange="onChangeObjectNum();" maxlength="12" />
                </div>
            </div>
            <div class="col-7 col-sm-7 col-lg-7">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="fas fa-home fa-fw" data-fa-transform="right-22 down-10" title="Адрес за кореспонденция..."></span>
                    </div>
                    <input class="form-control" type="text" id="sObjectName" name="sObjectName" suggest="suggest" queryType="objByName" onchange="onChangeObject();" />
                </div>
            </div>
            <div class="col-2 col-sm-2 col-lg-2">
                <div class="input-group input-group-sm">
                    <button class="btn btn-sm btn-primary" onclick="attachObject();"><i class="fas fa-code-branch"></i> Привържи </button>
                </div>
            </div>
        </div>
    </div>

    <div id="result" rpc_resize="off"></div>

</form>

<script>
	onInit();
</script>