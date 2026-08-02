{literal}
	<script>
        rpc_debug = true;
        rpc_html_debug = true;
		
		function runZoneAction(action) {
			rpc_on_exit = function(errorCode) {
				rpc_on_exit = function() {};
				if (!errorCode) {
					loadXMLDoc2('result');
				}
			};
			loadXMLDoc2(action);
		}

        function editZone(id) {
            //alert(id);
            var id_object = document.getElementById('nID').value;
            dialogSetSetupSignalZone(id, id_object);
        }

        function deleteZone(id) {
            if ( confirm('Наистина ли желаете да премахнете зоната?') ) {
                $('nIDZone').value = id;
                runZoneAction('delete');
            }
        }

        function techSupport() {
            var id = $('nID').value;

            dialogTechSupport(id);
        }

        function ServiceStatus() {
			rpc_on_exit = function(errorCode) {
				rpc_on_exit = function() {};
				if (!errorCode) {
					window.location.reload(true);
				}
			};

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
        }

	</script>
{/literal}


<form name="form1" id="form1" class="ui-object-core ui-object-sod-list ui-object-zones ui-sod-list-screen" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObj}"	/>
    <input type="hidden" id="nIDZone" name="nIDZone" value="0" />
    <input type="hidden" id="bEditStatuses" name="bEditStatuses" value="{$bEditStatuses|default:true}"/>
    <input type="hidden" id="isService" name="isService" value="{$isService|default:0}"/>

    {include file="object_tabs.tpl"}

    <!-- начало на работната част -->
    <div id="result" class="ui-object-result ui-object-sod-result ui-sod-list-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>

 	<!-- край на работната част -->

    <nav class="navbar fixed-bottom ui-object-actions ui-object-sod-actions ui-sod-list-actions" id="search" aria-label="Действия със зони">
            <div class="input-group input-group-sm">
                {if $edit.object_messages_edit}
                <button type="button" class="btn btn-sm btn-success mr-1" onClick="editZone(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>
                {/if}
                <button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
            </div>
    </nav>
    <div id="NoDisplay" style="display:none"></div>
</form>


<script>
    loadXMLDoc2('result');

</script>
