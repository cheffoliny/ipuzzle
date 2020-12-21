<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [
        <!ENTITY nbsp "&#160;">
        ]>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output omit-xml-declaration="yes" indent="yes" standalone="yes"/>

    <xsl:param name="rpc_prefix">my_rpc_prefix</xsl:param>
    <xsl:param name="rpc_result_area">my_rpc_result_area</xsl:param>
    <xsl:param name="rpc_resize">on</xsl:param>
    <xsl:param name="rpc_action_script">my_rpc_action_script</xsl:param>
    <xsl:param name="rpc_excel_panel">on</xsl:param>
    <xsl:param name="rpc_paging">on</xsl:param>
    <xsl:param name="rpc_edit_report">rpc_edit_report</xsl:param>
    <xsl:param name="rpc_invoice_toolbar">off</xsl:param>
    <xsl:param name="rpc_admin_invoice_toolbar">off</xsl:param>
    <xsl:param name="rpc_invoice_services_toolbar">off</xsl:param>
    <xsl:param name="rpc_autonumber">on</xsl:param>

    <xsl:template match="/">

        <input type="hidden"/>

        <script type="text/javascript" language="javascript">
            var <xsl:value-of select="$rpc_prefix"/>r_rows = new Array;

            function <xsl:value-of select="$rpc_prefix"/>setPointer(theRow,theRowNum, theAction) {
            report_rows=<xsl:value-of select="$rpc_prefix"/>r_rows;
            if ( typeof(report_rows[theRowNum]) == 'undefined' )
            report_rows[theRowNum]=false;

            if (theAction=='over') {
                theRow.setAttribute('style','background: rgba(247,247,250,0)', 0);
                } else if (theAction=='out') {
                    var row_color = theRowNum % 2 ? 'background: rgba(255,255,255,0)' : 'background: rgba(240,240,240,0)';
                    if ( !report_rows[theRowNum] ) {
                        theRow.setAttribute('style', row_color, 0);
                    } else {
                        theRow.setAttribute('style', 'background: rgba(40,5,10,0.4)', 0);
                    }
                } else if (theAction=='click') {
                    if (report_rows[theRowNum]) {
                        report_rows[theRowNum]=false;
                        <xsl:value-of select="$rpc_prefix"/>setPointer(theRow,theRowNum, 'out');
                    } else {
                        report_rows[theRowNum]=true;
                        theRow.setAttribute('style','background: rgba(245,245,220,0)', 0);
                    }

                }
            }

            function check(pref)
            {
		<xsl:text disable-output-escaping="yes">
		<![CDATA[
			oSEL = document.getElementsByTagName("INPUT");
			flag = document.getElementById(pref + 'all').checked;
			for(i=0; i < oSEL.length; i++)
				if(oSEL[i].type == 'checkbox' && oSEL[i].name.substr(0,pref.length) == pref)
					oSEL[i].checked = flag;
			return true;
		]]>
		</xsl:text>
            };

            function add_row_in(prefix, line)
            {
		<xsl:text disable-output-escaping="yes">
		<![CDATA[
			var tbl = document.getElementById(prefix + 'tbl_result');
			if (line != 0)
				var tr = tbl.insertRow(tbl.rows.length - line);
			else
				var tr = tbl.insertRow(-1);
			if ( (tbl.rows.length - 4) % 2 == 0)
				tr.style.background = 'rgba(240,240,240,0.8)';
			else
				tr.style.background = 'rgba(255,255,255,0.8)';
			for (i = 0; i < tbl.rows[2].cells.length; i++)
			{
				tr.insertCell();
				if (i > 0)
				{
					tr.cells[i].innerHTML = tbl.rows[1].cells[i].innerHTML;
					inputs = tr.cells[i].getElementsByTagName('INPUT');
					if (inputs[0]) inputs[0].disabled = false;
					selects = tr.cells[i].getElementsByTagName('SELECT');
					if (selects[0]) selects[0].disabled = false;
				}
				else
					if (line != 0)
					{
						tr.cells[i].innerHTML = (tbl.rows.length - 4) + '<input type="hidden" value="new" name="' + prefix + 'status[]"/>';
					}
					else
					{
						tr.cells[i].innerHTML = (tbl.rows.length - 2) + '<input type="hidden" value="new" name="' + prefix + 'status[]"/>';
					}
			}
		]]>
		</xsl:text>
            }

            function remove_row_in(prefix, line)
            {
		<xsl:text disable-output-escaping="yes">
		<![CDATA[
			var tbl = document.getElementById(prefix + 'tbl_result');
			var last_line = tbl.rows.length;
			if (line != 0)
			{
				inputs = tbl.rows[last_line - 2].cells[0].getElementsByTagName('INPUT');
				if (inputs[0].value == 'new') tbl.deleteRow(last_line - 2);
			}
			else
			{
				inputs = tbl.rows[last_line - 1].cells[0].getElementsByTagName('INPUT');
				if (inputs[0].value == 'new') tbl.deleteRow(last_line - 1);
			}

		]]>
		</xsl:text>
            }


            function <xsl:value-of select="$rpc_prefix"/>SortField(field_name)
            {
            var _sfield=document.getElementById('<xsl:value-of select="$rpc_prefix"/>sfield');
            var _stype=document.getElementById('<xsl:value-of select="$rpc_prefix"/>stype');
			<xsl:text disable-output-escaping="yes">
			<![CDATA[
				if (_sfield.value == field_name)
				{
					_stype.value=_stype.value==1 ? 0 : 1;
				} else {
					_sfield.value=field_name;
					_stype.value='0';
				}
			]]>
			</xsl:text>
            return <xsl:value-of select="$rpc_prefix"/>xslLoadXML();
            };

            function <xsl:value-of select="$rpc_prefix"/>xslValidatePage(current_page, last_page)
            {
		<xsl:text disable-output-escaping="yes">
		<![CDATA[
			if ( (current_page.value < 1) || (current_page.value > last_page) )
			{
				alert( 'Недопустим номер на страница!' );
				current_page.focus();
				current_page.select();
				return false;
			}
		]]>
		</xsl:text>
            };

            function <xsl:value-of select="$rpc_prefix"/>xslLoadDirectXML(action)
            {
            rpc_prefix='<xsl:value-of select="$rpc_prefix"/>';
            rpc_result_area='<xsl:value-of select="$rpc_result_area"/>';
            rpc_action_script='<xsl:value-of select="$rpc_action_script"/>';
            rpc_action = action != undefined ? action : 'result';
            return loadDirect(rpc_action);
            };

            function <xsl:value-of select="$rpc_prefix"/>xslLoadXML(action)
            {
            rpc_prefix='<xsl:value-of select="$rpc_prefix"/>';
            rpc_result_area='<xsl:value-of select="$rpc_result_area"/>';
            rpc_action_script='<xsl:value-of select="$rpc_action_script"/>';
            rpc_action = action != undefined ? action : 'result';
            return loadXMLDoc(rpc_action);
            };

            <!--function <xsl:value-of select="$rpc_prefix"/>xslResizer()-->
            <!--{-->
            <!--res_area='<xsl:value-of select="$rpc_result_area"/>';-->
            <!--pref='<xsl:value-of select="$rpc_prefix"/>';-->
            <!--var result_data=pref+"result_data";-->
            <!--var result_foother=pref+"result_foother";-->
            <!--var result_paging=pref+"paging";-->
            <!--var result_total=pref+"result_total";-->
            <!--var result_operations=pref+"result_operations";-->
            <!--<xsl:choose>-->
                <!--<xsl:when test="$rpc_resize = 'off'">-->
					<!--<xsl:text disable-output-escaping="yes">-->
					<!--<![CDATA[-->
						<!--if( res=document.getElementById(res_area) )-->
						<!--{-->
							<!--if ( _result_data=document.getElementById(result_data)  ) {-->
								<!--var _result_foother_height = document.getElementById(result_foother) ? document.getElementById(result_foother).offsetHeight : 0;-->
								<!--var _result_paging_height = document.getElementById(result_paging) ? document.getElementById(result_paging).offsetHeight : 0;-->
								<!--var _result_total_height = document.getElementById(result_total) ? document.getElementById(result_total).offsetHeight : 0;-->
								<!--var _result_operations_height = document.getElementById(result_operations) ? document.getElementById(result_operations).offsetHeight : 0;-->

								<!--_result_data_height = res.offsetHeight - _result_foother_height-_result_paging_height-_result_total_height-_result_operations_height-2;-->
								<!--_result_data.style.height = _result_data_height>30 ? _result_data_height : 30;-->
							<!--}-->
						<!--}-->
						<!--return true;-->
						<!--]]>-->
					<!--</xsl:text>-->
                <!--</xsl:when>-->
                <!--<xsl:otherwise>-->
					<!--<xsl:text disable-output-escaping="yes">-->
					<!--<![CDATA[-->
						<!--if( res=document.getElementById(res_area) )-->
						<!--{-->
							<!--if ( _result_data=document.getElementById(result_data)  ) {-->
								<!--var _result_foother_height = document.getElementById(result_foother) ? document.getElementById(result_foother).offsetHeight : 0;-->
								<!--var _result_operations_height = document.getElementById(result_operations) ? document.getElementById(result_operations).offsetHeight : 0;-->
								<!--var winHeight = isIE ? document.body.offsetHeight : window.innerHeight;-->

								<!--_result_data_height=winHeight - _result_data.offsetTop - _result_foother_height - _result_operations_height;-->
								<!--_result_data.style.height = _result_data_height>30 ? _result_data_height : 30;-->
							<!--}-->
						<!--}-->
						<!--return true;-->
						<!--]]>-->
					<!--</xsl:text>-->
                <!--</xsl:otherwise>-->
            <!--</xsl:choose>-->
            <!--}-->

            <!--<xsl:if test="$rpc_resize != 'off'">-->
                <!--if (window.addEventListener){-->
                <!--window.addEventListener("resize", <xsl:value-of select="$rpc_prefix"/>xslResizer, false);-->
                <!--} else if (window.attachEvent){-->
                <!--window.attachEvent("onresize", <xsl:value-of select="$rpc_prefix"/>xslResizer);-->
                <!--}-->
            <!--</xsl:if>-->
        </script>

        <xsl:choose>
            <xsl:when test="/response/result/data = ''">
                <div class="alert alert-danger alert-dismissable col-sm-11 transparent-half ml-4">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="fas fa-info-circle fa-2x"></i> <h5> Съобщение: </h5>
                    Няма намерени резултати по зададените критерии за търсене.
                </div>
            </xsl:when>
            <xsl:otherwise>
                <!-- paging old -->

                <!-- тотали -->
                <div class="total"><xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>totals</xsl:attribute>

                </div>

                <!-- създаване на sfield и stype -->
                <xsl:variable name="sfield"><xsl:value-of select="/response/result/paging/sfield"/></xsl:variable>
                <xsl:variable name="stype"><xsl:value-of select="/response/result/paging/stype"/></xsl:variable>
                <input type="hidden">
                    <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>sfield</xsl:attribute>
                    <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/>sfield</xsl:attribute>
                    <xsl:attribute name="value"><xsl:value-of select="$sfield"/></xsl:attribute>
                </input>
                <input type="hidden">
                    <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>stype</xsl:attribute>
                    <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/>stype</xsl:attribute>
                    <xsl:attribute name="value"><xsl:value-of select="$stype"/></xsl:attribute>
                </input>

                <!-- заглавна част -->

                <nav class="navbar fixed-bottom navbar-expand-lg navbar-dark bg-dark flex-row py-md-0">
                    <div class="col-9 col-sm-9 col-lg-9 text-white">
                        <xsl:if test="$rpc_paging = 'on'">
                            <!-- <Paging> -->
                            <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>paging</xsl:attribute>
                            <xsl:for-each select="response/result/paging">

                                <!-- брой страници -->
                                <xsl:variable name="last_page"><xsl:value-of select="page_total"/></xsl:variable>
                                <!-- текуща страница -->
                                <xsl:variable name="current_page"><xsl:value-of select="current_page"/></xsl:variable>
                                <div class="row py-1">
                                <div class="col-6 col-sm-6 col-lg-6 pt-2">
                                    <!--&nbsp; стр.-->
                                    <input type="hidden" size="3" style="width: 30px; text-align: center; background-color: transparent;" OnKeyPress="return formatDigits(event);">
                                        <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>current_page</xsl:attribute>
                                        <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/>current_page</xsl:attribute>
                                        <xsl:attribute name="value"><xsl:value-of select="current_page"/></xsl:attribute>
                                        <xsl:attribute name="OnChange">return <xsl:value-of select="$rpc_prefix"/>xslValidatePage(this,<xsl:value-of select="$last_page"/>)</xsl:attribute>
                                    </input>
                                    <!--/ <xsl:value-of select="page_total"/>-->
                                    <!--<button type="button" class="btn btn-sm" onClick="return {$rpc_prefix}xslLoadXML()" >-->
                                        <!--<i class="fas fa-hand-pointer"></i>-->
                                    <!--</button>-->
                                    <!--&nbsp;&nbsp;-->
                                резултати
                                <xsl:value-of select="(current_page - 1) * rows_per_page + 1"/>
                                -
                                <xsl:choose>
                                    <xsl:when test="((current_page - 1) * rows_per_page + rows_per_page) &lt; rows_total">
                                        <xsl:value-of select="(current_page - 1) * rows_per_page + rows_per_page"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="rows_total"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                                от
                                <xsl:value-of select="rows_total"/>
                                </div>
                                <div class="col-6 col-sm-6 col-lg-6">
                                    <xsl:choose>
                                        <xsl:when test="$current_page > 1 ">
                                            <a class="btn text-primary" onClick="{$rpc_prefix}current_page.value = 1; return {$rpc_prefix}xslLoadXML()">
                                                1
                                            </a>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <xsl:choose>
                                        <xsl:when test="$current_page > 3 ">
                                            <a class="btn text-primary" onClick="{$rpc_prefix}current_page.value = 2; return {$rpc_prefix}xslLoadXML()">
                                                2
                                            </a>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <xsl:choose>
                                        <xsl:when test="$current_page > 4 ">
                                            ...
                                        </xsl:when>
                                        <xsl:otherwise>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <xsl:choose>
                                        <xsl:when test="$current_page - 2 > 0">
                                            <a class="btn text-primary" onClick="{$rpc_prefix}current_page.value--; return {$rpc_prefix}xslLoadXML()">
                                                <xsl:value-of select="(current_page - 1)"/>
                                            </a>
                                        </xsl:when>
                                        <xsl:otherwise>

                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <a class="btn btn-outline-primary disabled">
                                        <xsl:value-of select="current_page"/>
                                    </a>
                                    <xsl:choose>
                                        <xsl:when test="$last_page - 2 > $current_page > 0">
                                            <a class="btn text-primary" onClick="{$rpc_prefix}current_page.value++; return {$rpc_prefix}xslLoadXML()">
                                                <xsl:value-of select="(current_page + 1)"/>
                                            </a>
                                        </xsl:when>
                                        <xsl:otherwise>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <xsl:choose>
                                        <xsl:when test="$last_page - 2 > $current_page > 0 ">
                                            ...
                                        </xsl:when>
                                        <xsl:otherwise>

                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <xsl:choose>
                                        <xsl:when test="$last_page - 1 > $current_page > 0 ">
                                            <a class="btn text-primary" onClick="{$rpc_prefix}current_page.value = {$last_page - 1}; return {$rpc_prefix}xslLoadXML()">
                                                <xsl:value-of select="($last_page - 1)"/>
                                            </a>
                                        </xsl:when>
                                        <xsl:otherwise>

                                        </xsl:otherwise>
                                    </xsl:choose>
                                    <xsl:choose>
                                        <xsl:when test="$current_page != $last_page">
                                            <a class="btn text-primary"  onClick="{$rpc_prefix}current_page.value = {$last_page}; return {$rpc_prefix}xslLoadXML()">
                                                <xsl:value-of select="($last_page)"/>
                                            </a>
                                        </xsl:when>
                                        <xsl:otherwise>

                                        </xsl:otherwise>
                                    </xsl:choose>

                                </div>
                                </div>
                            </xsl:for-each>

                            <!-- </Paging> -->
                        </xsl:if>
                    </div>
                    <div class="col-3 col-sm-3 col-lg-3 text-right">
                        <xsl:if test="$rpc_excel_panel = 'on'">
                            <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>result_foother</xsl:attribute>
                            <div class="btn-group btn-sm">
                                <button class="btn btn-sm btn-success">
                                    <xsl:attribute name="onClick"><xsl:value-of select="$rpc_prefix"/>xslLoadDirectXML('export_to_xls')</xsl:attribute>
                                    <i class="far fa-file-excel fa-lg"></i>&nbsp;&nbsp; EXCEL
                                </button>
                                <button class="btn btn-sm btn-danger ml-1">
                                    <xsl:attribute name="onClick"><xsl:value-of select="$rpc_prefix"/>xslLoadDirectXML('export_to_pdf')</xsl:attribute>
                                    <i class="far fa-file-pdf fa-lg"></i>&nbsp;&nbsp; PDF&nbsp;&nbsp;&nbsp;
                                </button>
                            </div>
                        </xsl:if>
                    </div>
                </nav>

                <div id="result_data"  class="container-fluid body-content" >
                    <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>result_data</xsl:attribute>
                    <table class="table table-sm table-striped table-dark">
                        <tbody>
                            <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>tbl_result</xsl:attribute>
                            <xsl:for-each select="response/result/title/r">
                                <tr class="bg-primary text-center intelliheader">
                                    <xsl:for-each select="c">
                                        <xsl:choose>
                                            <xsl:when test=". != ' '">
                                                <th>
                                                    <xsl:for-each select="./@*">
                                                        <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                                                    </xsl:for-each>
                                                    <xsl:value-of select="."/>
                                                </th>
                                            </xsl:when>
                                            <xsl:otherwise>
                                                <th>
                                                    <xsl:for-each select="./@*">
                                                        <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                                                    </xsl:for-each>
                                                    &nbsp;
                                                </th>
                                            </xsl:otherwise>
                                        </xsl:choose>
                                    </xsl:for-each>
                                </tr>
                            </xsl:for-each>
                            <xsl:for-each select="response/result/fields">
                                <tr class="bg-primary intelliheader">

                                    <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>main</xsl:attribute>

                                    <xsl:if test="$rpc_autonumber = 'on'">
                                        <th>#</th>
                                    </xsl:if>


                                    <xsl:for-each select="c">
                                        <th>
                                            <xsl:for-each select="./@*">
                                                <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                                            </xsl:for-each>

                                            <xsl:if test="@type='hidden'">
                                                <xsl:attribute name="style">display:none</xsl:attribute>
                                            </xsl:if>

                                            <xsl:attribute name="title"><xsl:value-of select="title"/></xsl:attribute>
                                            <xsl:if test="$sfield = name">
                                                <xsl:attribute name="class">active</xsl:attribute>
                                                <xsl:call-template name="sort_img">
                                                    <xsl:with-param name="type" select="$stype"/>
                                                </xsl:call-template>
                                            </xsl:if>
                                            <xsl:choose>
                                                <xsl:when test="/response/result/paging/sfield">
                                                    <a href="#">
                                                        <xsl:attribute name="onClick">return <xsl:value-of select="$rpc_prefix"/>SortField('<xsl:value-of select="name"/>')</xsl:attribute>
                                                        <xsl:value-of select="caption"/>
                                                    </a>
                                                </xsl:when>
                                                <xsl:otherwise>
                                                    <xsl:value-of select="caption"/>
                                                </xsl:otherwise>
                                            </xsl:choose>
                                        </th>
                                    </xsl:for-each>
                                </tr>
                            </xsl:for-each>

                            <!-- total -->
                            <xsl:for-each select="response/result/total">
                                <tr class="bg-seccess">
                                    <xsl:for-each select="c">
                                        <td>
                                            <xsl:value-of select="."/>
                                        </td>
                                    </xsl:for-each>
                                </tr>
                            </xsl:for-each>

                            <!-- data -->
                            <xsl:variable name="page_start">
                                <xsl:choose>
                                    <xsl:when test="/response/result/paging/current_page">
                                        <xsl:value-of select="(/response/result/paging/current_page - 1) * /response/result/paging/rows_per_page"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        0
                                    </xsl:otherwise>
                                </xsl:choose>
                            </xsl:variable>

                            <xsl:for-each select="/response/result/data/r">
                                <!-- insert empty hidden row -->
                                <xsl:if test="position() = 1 and $rpc_edit_report = 'on'">
                                    <tr style="visibility: hidden; display: none;">
                                        <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>new_row</xsl:attribute>
                                        <td>0<input type="hidden" value="new" disabled="true"><xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/>status[]</xsl:attribute></input></td>
                                        <xsl:for-each select="c">
                                            <xsl:variable name="fpos"><xsl:value-of select="position()"/></xsl:variable>
                                            <td>
                                                <xsl:if test="/response/result/fields/c[position() = $fpos]/data">
                                                    <xsl:call-template name="display_control">
                                                        <xsl:with-param name="data" select="/response/result/fields/c[position() = $fpos]"/>
                                                        <xsl:with-param name="disable" select="."/>
                                                    </xsl:call-template>
                                                </xsl:if>
                                            </td>
                                        </xsl:for-each>
                                    </tr>
                                </xsl:if>
                                <tr onmouseover="{$rpc_prefix}setPointer(this, {position()}, 'over');" onmouseout="{$rpc_prefix}setPointer(this, {position()}, 'out');" onmousedown="{$rpc_prefix}setPointer(this, {position()}, 'click');">

                                    <xsl:for-each select="./@*">
                                        <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                                    </xsl:for-each>

                                    <xsl:choose>
                                        <xsl:when test="(position() mod 2) = 0">
                                            <xsl:attribute name="style">rgba(240,240,240,0.8)</xsl:attribute>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:attribute name="style">rgba(255,255,255,0.8)</xsl:attribute>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <xsl:if test="$rpc_autonumber = 'on'">
                                        <td align="right"><xsl:value-of select="position() + $page_start"/><xsl:if test="$rpc_edit_report = 'on'"><input type="hidden" value="old"><xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/>status[<xsl:value-of select="@id"/>]</xsl:attribute></input></xsl:if></td>
                                    </xsl:if>

                                    <xsl:for-each select="c">
                                        <xsl:variable name="field_pos"><xsl:value-of select="position()"/></xsl:variable>
                                        <xsl:variable name="field_name"><xsl:value-of select="/response/result/fields/c/name[position() = $field_pos]"/></xsl:variable>
                                        <td>
                                            <xsl:if test="/response/result/fields/c[position() = $field_pos]/btn">
                                                <xsl:choose>
                                                    <xsl:when test="/response/result/fields/c[position() = $field_pos]/btn != ''">
                                                        <xsl:attribute name="width">90</xsl:attribute>
                                                    </xsl:when>
                                                    <xsl:otherwise>
                                                        <xsl:attribute name="width">25</xsl:attribute>
                                                    </xsl:otherwise>
                                                </xsl:choose>
                                            </xsl:if>
                                            <xsl:for-each select="./@*">
                                                <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                                            </xsl:for-each>

                                            <!-- data parse -->
                                            <xsl:choose>
                                                <xsl:when test="/response/result/fields/c[position() = $field_pos]/@type = 'select'">
                                                    <xsl:variable name="refID" select="/response/result/fields/c[position() = $field_pos]/@ref"/>
                                                    <select>
                                                        <xsl:variable name="id">
                                                            <xsl:value-of select="$rpc_prefix"/>
                                                            <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/name"/>
                                                            <xsl:value-of select="'['"/>
                                                            <xsl:value-of select="../@id"/>
                                                            <xsl:value-of select="']'"/>
                                                        </xsl:variable>
                                                        <xsl:variable name="value" select="."/>
                                                        <xsl:copy-of select="/response/action/form/e[@id = $refID]/@*"/>
                                                        <xsl:attribute name="id">
                                                            <xsl:value-of select="$id"/>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="name">
                                                            <xsl:value-of select="$id"/>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="value">
                                                            <xsl:value-of select="$value"/>
                                                        </xsl:attribute>
                                                        <xsl:for-each select="/response/action/form/e[@id = $refID]/option">
                                                            <option>
                                                                <xsl:if test="$value = @value">
                                                                    <xsl:attribute name="selected">selected</xsl:attribute>
                                                                </xsl:if>
                                                                <xsl:copy-of select="@*"/>
                                                                <xsl:value-of select="."/>
                                                            </option>
                                                        </xsl:for-each>
                                                    </select>
                                                </xsl:when>
                                                <xsl:when test="/response/result/fields/c[position() = $field_pos]/@type = 'text'">
                                                    <xsl:variable name="refID" select="/response/result/fields/c[position() = $field_pos]/@ref"/>
                                                    <input type="text">
                                                        <xsl:variable name="id">
                                                            <xsl:value-of select="$rpc_prefix"/>
                                                            <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/name"/>
                                                            <xsl:value-of select="'['"/>
                                                            <xsl:value-of select="../@id"/>
                                                            <xsl:value-of select="']'"/>
                                                        </xsl:variable>
                                                        <xsl:copy-of select="/response/action/form/e[@id = $refID]/@*"/>
                                                        <xsl:attribute name="id">
                                                            <xsl:value-of select="$id"/>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="name">
                                                            <xsl:value-of select="$id"/>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="value">
                                                            <xsl:value-of select="."/>
                                                        </xsl:attribute>
                                                        <xsl:copy-of select="/response/action/form/e[@id = $refID]/*"/>
                                                    </input>
                                                </xsl:when>
                                                <xsl:when test="/response/result/fields/c[position() = $field_pos]/@type = 'hidden'">
                                                    <xsl:variable name="refID" select="/response/result/fields/c[position() = $field_pos]/@ref"/>
                                                    <xsl:attribute name="style">display:none</xsl:attribute>
                                                    <xsl:variable name="id">
                                                        <xsl:value-of select="$rpc_prefix"/>
                                                        <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/name"/>
                                                        <xsl:value-of select="'['"/>
                                                        <xsl:value-of select="../@id"/>
                                                        <xsl:value-of select="']'"/>
                                                    </xsl:variable>
                                                    <input type="hidden" id="{$id}" name="{$id}" value="{.}"/>
                                                </xsl:when>
                                                <xsl:when test="/response/result/fields/c[position() = $field_pos]/@type = 'span'">
                                                    <xsl:variable name="refID" select="/response/result/fields/c[position() = $field_pos]/@ref"/>
                                                    <xsl:variable name="id">
                                                        <xsl:value-of select="$rpc_prefix"/>
                                                        <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/name"/>
                                                        <xsl:value-of select="'['"/>
                                                        <xsl:value-of select="../@id"/>
                                                        <xsl:value-of select="']'"/>
                                                    </xsl:variable>
                                                    <span>
                                                        <xsl:copy-of select="/response/action/form/e[@id = $refID]/@*"/>
                                                        <xsl:attribute name="id">
                                                            <xsl:value-of select="$id"/>
                                                        </xsl:attribute>
                                                        <xsl:attribute name="class">
                                                            <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/name"/>
                                                        </xsl:attribute>
                                                        <xsl:value-of select="."/>
                                                    </span>
                                                </xsl:when>
                                                <xsl:when test="/response/result/fields/c[position() = $field_pos]/data">
                                                    <xsl:call-template name="display_control">
                                                        <xsl:with-param name="c_id" select="@id"/>
                                                        <xsl:with-param name="r_id" select="../@id"/>
                                                        <xsl:with-param name="data" select="/response/result/fields/c[position() = $field_pos]"/>
                                                        <xsl:with-param name="value" select="."/>
                                                    </xsl:call-template>
                                                </xsl:when>
                                                <xsl:otherwise>
                                                    <xsl:choose>
                                                        <!-- визуализиране на бутон -->
                                                        <xsl:when test="/response/result/fields/c[position() = $field_pos]/btn">

                                                            <xsl:choose>
                                                                <xsl:when test="/response/result/fields/c[position() = $field_pos]/btn != ''">
                                                                    <button id="b90" class="btn btn-outline-secondary btn-sm">
                                                                        <xsl:if test="/response/result/fields/c[position() = $field_pos]/link">
                                                                            <xsl:attribute name="onclick"><xsl:value-of select="/response/result/fields/c[position() = $field_pos]/link"/>('<xsl:choose><xsl:when test="@id != ''"><xsl:value-of select="@id"/></xsl:when><xsl:otherwise><xsl:value-of select="../@id"/></xsl:otherwise></xsl:choose>'); return false;</xsl:attribute>
                                                                        </xsl:if>
                                                                        <xsl:if test="/response/result/fields/c[position()]/img">
                                                                            <xsl:choose>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'pdf')">
                                                                                    <i class="far fa-file-pdf text-danger" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'mail')">
                                                                                    <i class="far fa-envelope text-primary" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'cancel')">
                                                                                    <i class="far fa-trash-alt text-danger" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'edit')">
                                                                                    <i class="far fa-edit text-dark" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'house')">
                                                                                    <i class="fas fa-home text-primary" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'dots')">
                                                                                    <i class="fas fa-ellipsis-h text-secondary" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'minus')">
                                                                                    <i class="far fa-minus-square text-danger" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'plus')">
                                                                                    <i class="far fa-plus-square text-primary" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'search')">
                                                                                    <i class="fas fa-search text-secondary" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'cal')">
                                                                                    <i class="far fa-calendar text-primary" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'excel')">
                                                                                    <i class="far fa-file-excel text-success" aria-hidden="true"></i>&nbsp;
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'sound')">
                                                                                    <i class="fas fa-volume-up text-success" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'info')">
                                                                                    <i class="fas fa-info-circle text-info" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'delete')">
                                                                                    <i class="far fa-trash-alt text-danger" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:otherwise>
                                                                                    <img>
                                                                                        <xsl:attribute name="src">
                                                                                            <xsl:value-of
                                                                                                    select="/response/result/fields/c[position() = $field_pos]/img"/>
                                                                                        </xsl:attribute>
                                                                                    </img>
                                                                                </xsl:otherwise>
                                                                            </xsl:choose>
                                                                        </xsl:if>
                                                                        <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/btn"/></button>
                                                                </xsl:when>
                                                                <xsl:otherwise>
                                                                    <button id="b25" class="btn btn-outline-secondary btn-sm">
                                                                        <xsl:if test="/response/result/fields/c[position() = $field_pos]/link">
                                                                            <xsl:attribute name="onclick"><xsl:value-of select="/response/result/fields/c[position() = $field_pos]/link"/>('<xsl:choose><xsl:when test="@id != ''"><xsl:value-of select="@id"/></xsl:when><xsl:otherwise><xsl:value-of select="../@id"/></xsl:otherwise></xsl:choose>'); return false;</xsl:attribute>
                                                                        </xsl:if>
                                                                        <xsl:if test="/response/result/fields/c[position()]/img">
                                                                            <xsl:choose>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'pdf')">
                                                                                    <i class="far fa-file-pdf text-danger" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'mail')">
                                                                                    <i class="far fa-envelope text-primary" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'cancel')">
                                                                                    <i class="far fa-trash-alt text-danger" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'edit')">
                                                                                    <i class="far fa-edit text-dark" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'house')">
                                                                                    <i class="fas fa-home text-primary" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'dots')">
                                                                                    <i class="fas fa-ellipsis-h text-secondary" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'minus')">
                                                                                    <i class="far fa-minus-square text-danger" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'plus')">
                                                                                    <i class="far fa-plus-square text-primary" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'search')">
                                                                                    <i class="fas fa-search text-secondary" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'cal')">
                                                                                    <i class="far fa-calendar text-primary" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'excel')">
                                                                                    <i class="far fa-file-excel text-success" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'sound')">
                                                                                    <i class="fas fa-volume-up text-primary" aria-hidden="true"></i>*
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'info')">
                                                                                    <i class="fas fa-info-circle text-info" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:when test="contains(/response/result/fields/c[position() = $field_pos]/img, 'delete')">
                                                                                    <i class="far fa-trash-alt text-danger" aria-hidden="true"></i>
                                                                                </xsl:when>
                                                                                <xsl:otherwise>
                                                                                    <img>
                                                                                        <xsl:attribute name="src">
                                                                                            <xsl:value-of
                                                                                                    select="/response/result/fields/c[position() = $field_pos]/img"/>
                                                                                        </xsl:attribute>
                                                                                    </img>
                                                                                </xsl:otherwise>
                                                                            </xsl:choose>
                                                                        </xsl:if>
                                                                    </button>
                                                                </xsl:otherwise>
                                                            </xsl:choose>
                                                        </xsl:when>
                                                        <xsl:otherwise>
                                                            <xsl:choose>
                                                                <xsl:when test="/response/result/fields/c[position() = $field_pos]/img != '' and . != '0' and . !=''">
                                                                    <img>
                                                                        <xsl:attribute name="src"><xsl:value-of select="/response/result/fields/c[position() = $field_pos]/img"/></xsl:attribute>
                                                                        <xsl:if test=". != '1'">
                                                                            <xsl:attribute name="title"><xsl:value-of select="."/></xsl:attribute>
                                                                        </xsl:if>
                                                                    </img>
                                                                </xsl:when>
                                                                <xsl:otherwise>
                                                                    <xsl:choose>
                                                                        <xsl:when test="/response/result/fields/c[position() = $field_pos]/link != ''">
                                                                            <a href="#">
                                                                                <xsl:attribute name="onClick">
                                                                                    <xsl:choose>
                                                                                        <xsl:when test="@id != ''">
                                                                                            <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/link"/>('<xsl:value-of select="@id"/>')
                                                                                        </xsl:when>
                                                                                        <xsl:otherwise>
                                                                                            <xsl:value-of select="/response/result/fields/c[position() = $field_pos]/link"/>('<xsl:value-of select="../@id"/>')
                                                                                        </xsl:otherwise>
                                                                                    </xsl:choose>
                                                                                </xsl:attribute>
                                                                                <xsl:value-of select="."/>
                                                                            </a>
                                                                        </xsl:when>
                                                                        <xsl:otherwise>
                                                                            <xsl:if test=". != '0'">
                                                                                <xsl:value-of select="."/>
                                                                            </xsl:if>
                                                                        </xsl:otherwise>
                                                                    </xsl:choose>
                                                                </xsl:otherwise>
                                                            </xsl:choose>
                                                        </xsl:otherwise>
                                                    </xsl:choose>
                                                </xsl:otherwise>
                                            </xsl:choose>
                                        </td>
                                    </xsl:for-each>
                                </tr>
                            </xsl:for-each>
                            <xsl:for-each select="response/result/fields">
                                <xsl:for-each select="c">
                                    <xsl:if test="data/input/@type = 'checkbox'">
                                        <xsl:if test="data/input/@exception != 'true'">
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <xsl:attribute name="colspan"><xsl:value-of select="count(/response/result/fields/c)+1"/></xsl:attribute>
                                                    <xsl:if test="/response/result/fields/c">
                                                        <xsl:call-template name="operation">
                                                            <xsl:with-param name="text" select="title"/>
                                                        </xsl:call-template>
                                                    </xsl:if>
                                                </td>
                                            </tr>
                                        </xsl:if>
                                        <xsl:if test="$rpc_invoice_toolbar = 'on'">
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <xsl:attribute name="colspan">
                                                        <xsl:value-of select="count(/response/result/fields/c)+1"/>
                                                    </xsl:attribute>
                                                    <xsl:if test="/response/result/fields/c">
                                                        <xsl:call-template name="rpcInvoiceToolbar"/>
                                                    </xsl:if>
                                                </td>
                                            </tr>
                                        </xsl:if>
                                        <xsl:if test="$rpc_admin_invoice_toolbar = 'on'">
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <xsl:attribute name="colspan">
                                                        <xsl:value-of select="count(/response/result/fields/c)+1"/>
                                                    </xsl:attribute>
                                                    <xsl:if test="/response/result/fields/c">
                                                        <xsl:call-template name="rpcAdminInvoiceToolbar"/>
                                                    </xsl:if>
                                                </td>
                                            </tr>
                                        </xsl:if>
                                        <xsl:if test="$rpc_invoice_services_toolbar = 'on'">
                                            <tr>
                                                <td></td>
                                                <td>
                                                    <xsl:attribute name="colspan">
                                                        <xsl:value-of select="count(/response/result/fields/c)+1"/>
                                                    </xsl:attribute>
                                                    <xsl:if test="/response/result/fields/c">
                                                        <xsl:call-template name="rpcInvoiceServicesToolbar"/>
                                                    </xsl:if>
                                                </td>
                                            </tr>
                                        </xsl:if>
                                    </xsl:if>
                                </xsl:for-each>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </div>

            </xsl:otherwise>
        </xsl:choose>




        <!--<script type="text/javascript" language="javascript">-->
            <!--<xsl:value-of select="$rpc_prefix"/>xslResizer();-->
        <!--</script>-->

    </xsl:template>

    <!-- визуализиране на картинката за сортиране на поле -->
    <xsl:template name="sort_img">
        <xsl:param name="type"/>
        <xsl:choose>
            <xsl:when test="$type = '1'">
                &nbsp;<i class="fas fa-caret-up pull-right"></i>&nbsp;
            </xsl:when>
            <xsl:otherwise>
                &nbsp;<i class="fas fa-caret-down pull-right"></i>&nbsp;
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- визуализиране на контроли -->
    <xsl:template name="display_control">
        <xsl:param name="c_id"/>
        <xsl:param name="r_id"/>
        <xsl:param name="data"/>
        <xsl:param name="value"/>
        <xsl:param name="from_field"/>
        <xsl:param name="disable"/>
        <xsl:for-each select="$data/data/*">
            <xsl:choose>
                <xsl:when test="name(.) = 'select'">
                    <select>
                        <xsl:if test="$disable"><xsl:attribute name="disabled">disabled</xsl:attribute></xsl:if>
                        <xsl:for-each select="./@*">
                            <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                        </xsl:for-each>
                        <xsl:choose>
                            <xsl:when test="c_id != ''">
                                <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/><xsl:value-of select="../../name"/>[<xsl:value-of select="$c_id"/>]</xsl:attribute>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/><xsl:value-of select="../../name"/>[<xsl:value-of select="$r_id"/>]</xsl:attribute>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:attribute name="value"><xsl:value-of select="$value"/></xsl:attribute>
                        <xsl:for-each select="./option">
                            <option>
                                <xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute>
                                <xsl:if test="$value = @value">
                                    <xsl:attribute name="selected">selected</xsl:attribute>
                                </xsl:if>
                                <xsl:value-of select="."/>
                            </option>
                        </xsl:for-each>
                    </select>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:choose>
                        <xsl:when test="name(.) = 'input'">
                            <input class="edit">
                                <xsl:if test="$disable"><xsl:attribute name="disabled">disabled</xsl:attribute></xsl:if>
                                <xsl:for-each select="./@*">
                                    <xsl:attribute name="{name(.)}"><xsl:value-of select="."/></xsl:attribute>
                                </xsl:for-each>
                                <xsl:choose>
                                    <xsl:when test="c_id != ''">
                                        <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/><xsl:value-of select="../../name"/>[<xsl:value-of select="$c_id"/>]</xsl:attribute>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/><xsl:value-of select="../../name"/>[<xsl:value-of select="$r_id"/>]</xsl:attribute>
                                        <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/><xsl:value-of select="../../name"/>[<xsl:value-of select="$r_id"/>]</xsl:attribute>
                                    </xsl:otherwise>
                                </xsl:choose>
                                <xsl:if test="@type = 'checkbox'"><xsl:attribute name="class">clear</xsl:attribute></xsl:if>
                                <xsl:attribute name="type"><xsl:value-of select="@type"/></xsl:attribute>

                                <xsl:attribute name="value">
                                    <xsl:value-of select="$value"/>
                                </xsl:attribute>

                                <xsl:if test="$value = 1">
                                    <xsl:attribute name="checked">checked</xsl:attribute>
                                </xsl:if>

                                <xsl:if test="$from_field">
                                    <xsl:attribute name="onClick">check('<xsl:value-of select="$rpc_prefix"/>');</xsl:attribute>
                                    <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/>all</xsl:attribute>
                                </xsl:if>
                            </input>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="."/>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
    </xsl:template>

    <!-- визуализиране на помощен div -->
    <xsl:template name="operation">
        <xsl:param name="text"/>
        <div class="col-12 col-sm-12 col-lg-12 my-1 pl-0">
            <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>operations</xsl:attribute>
            <div class="btn-group input-group-sm">
                <div class="input-group-prepend">
                    <span class="far fa-check-square fa-fw" data-fa-transform="right-22 down-10"></span>
                </div>
                <xsl:value-of select="$text"/>
                <select>
                    <xsl:attribute name="class"><xsl:value-of select="$rpc_prefix"/>form-control</xsl:attribute>
                    <xsl:attribute name="id"><xsl:value-of select="$rpc_prefix"/>sel</xsl:attribute>
                    <xsl:attribute name="name"><xsl:value-of select="$rpc_prefix"/>sel</xsl:attribute>
                    <option value=" "> </option>
                </select>
                <button class="btn btn-sm btn-dark" id="{concat($rpc_prefix,'rpc_btn_action')}" name="{concat($rpc_prefix,'rpc_btn_action')}" ><xsl:attribute name="onClick"><xsl:value-of select="$rpc_prefix"/>just_do_it();</xsl:attribute> <i class="fa fa-check"></i> Изпълни </button>
            </div>
        </div>
    </xsl:template>

    <xsl:template name="rpcInvoiceToolbar">
        <div>
            <table cellspacing="0" cellpadding="5">
                <tr>
                    <td style="white-space:nowrap">
                        <span class="far fa-check-square fa-fw" data-fa-transform="right-22 down-10"></span>
                    </td>
                    <td>
                        <select id="{concat($rpc_prefix,'rpcInvoiceSelect')}" name="{concat($rpc_prefix,'rpcInvoiceSelect')}" style="width:230px"></select>
                    </td>
                    <td>
                        <button style="width:20px" onclick="{concat($rpc_prefix,'rpcBtnInvoiceDo()')}">
                            <img src="images/refresh_ppp.gif" width="12" height="12"/>
                        </button>
                    </td>
                    <td>
                        <xsl:variable name="dateID">
                            <xsl:value-of select="concat($rpc_prefix,'rpcInvoiceDateID')"/>
                        </xsl:variable>
                        <input id="{$dateID}" name="{$dateID}" onkeypress="return formatDate(event);" type="text" size="10" maxlength="10" title="DD/MM/YYYY"/>
                        <img src="images/cal.gif" align="absmiddle" onclick="displayCalendarFor('{$dateID}');"/>
                    </td>
                    <td>
                        <button id="{concat($rpc_prefix,'rpcBtnInvoiceAction')}" name="{concat($rpc_prefix,'rpcBtnInvoiceAction')}" style="width:120px" onclick="{concat($rpc_prefix,'rpcInvoiceAction();')}">Подготви ДП</button>
                    </td>
                </tr>
            </table>
        </div>
    </xsl:template>

    <xsl:template name="rpcAdminInvoiceToolbar">
        <div>
            <table cellspacing="0" cellpadding="5">
                <tr>
                    <td style="white-space:nowrap">
                        <i class="far fa-check-square"></i>
                    </td>
                    <td>
                        <select id="{concat($rpc_prefix,'rpcInvoiceSelect')}" name="{concat($rpc_prefix,'rpcInvoiceSelect')}" style="width:230px;font-size:11px;"></select>
                    </td>
                    <td>
                        <button id="{concat($rpc_prefix,'rpcBtnInvoiceAction')}" name="{concat($rpc_prefix,'rpcBtnInvoiceAction')}" style="width:120px" onclick="{concat($rpc_prefix,'rpcInvoiceAction();')}">изпълни</button>
                    </td>
                </tr>
            </table>
        </div>
    </xsl:template>

    <xsl:template name="rpcInvoiceServicesToolbar">
        <div>
            <table cellspacing="0" cellpadding="5">
                <tr>
                    <td style="white-space:nowrap">
                        <i class="far fa-check-square"></i>
                    </td>
                    <td>
                        <select id="{concat($rpc_prefix,'rpcInvoiceSelect')}" name="{concat($rpc_prefix,'rpcInvoiceSelect')}" style="width:230px;font-size:11px;"></select>
                    </td>
                    <td>
                        <button id="{concat($rpc_prefix,'rpcBtnInvoiceAction')}" name="{concat($rpc_prefix,'rpcBtnInvoiceAction')}" style="width:120px" onclick="{concat($rpc_prefix,'rpcInvoiceAction();')}">изпълни</button>
                    </td>
                </tr>
            </table>
        </div>
    </xsl:template>

</xsl:stylesheet>
