{literal}
	<script>
        rpc_debug = true;
        rpc_html_debug = true;
		
		function submit_form() {
			loadXMLDoc( 'save', 0 );
		}

        function editZone(id) {
            //alert(id);
            var id_object = document.getElementById('nID').value;
            dialogSetSetupSignalZone(id, id_object);
        }

        function deleteZone(id) {
            if ( confirm('Наистина ли желаете да премахнете зоната?') ) {
                $('nIDZone').value = id;
                loadXMLDoc2('delete', 1);
            }
        }

        function techSupport() {
            var id = $('nID').value;

            dialogTechSupport(id);
        }

        function ServiceStatus() {

            if(intval(jQuery('#isService').val()) )
            {
                //обекта е в сервизен - сваляме го
                loadXMLDoc2('closeServiceStatus');
            }
            else
            {
                //пускаме го в сервизен
                loadXMLDoc2('setServiceStatus');
            }

            rpc_on_exit = function() {
                window.location.reload( true );
            }

        }

	</script>
{/literal}


<form name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObj}"	/>
    <input type="hidden" id="nIDZone" name="nIDZone" value="0" />
    <input type="hidden" id="bEditStatuses" name="bEditStatuses" value="{$bEditStatuses|default:true}"/>
    <input type="hidden" id="isService" name="isService" value="{$isService|default:0}"/>

    {include file=object_tabs.tpl}

    <!-- начало на работната част -->
    <div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>

 	<!-- край на работната част -->

    <nav class="navbar fixed-bottom flex-row mb-0 py-0 navbar-expand-lg py-md-1" id="search">
        <div class="col-6 col-sm-8 col-lg-8" title="">
        </div>
        <div class="col-6 col-sm-4 col-lg-4">
            <div class="input-group input-group-sm ml-1">
                <button class="btn btn-sm btn-success mr-1" onClick="editZone(0);"><i class="fa fa-plus"></i> Добави</button>
                <button class="btn btn-sm btn-danger"	    onClick="parent.window.close();"><i class="far fa-window-close" ></i> Затвори </button>
            </div>
        </div>
    </nav>
    <div id="NoDisplay" style="display:none"></div>
</form>


<script>
    loadXMLDoc2('result');


    {if !$edit.object_messages_edit}{literal}
    if ( form=document.getElementById('form1') ) {
        for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
    }{/literal}
    {/if}
</script>
