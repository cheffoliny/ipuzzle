<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [
	<!ENTITY nbsp "&#160;">
]>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output omit-xml-declaration="yes" indent="yes" standalone="yes"/>

	<xsl:template match="/">

		<input type="hidden"/>
		<input type="hidden" id="selectedShift" name="selectedShift"/>

		<input type="hidden" id="nResultIDObject" name="nResultIDObject">
      <xsl:attribute name="value">
        <xsl:value-of select="/response/action/form[@id='form1']/e[@id='nResultIDObject']/@value"/>
      </xsl:attribute>
		</input>

    <input type="hidden" id="nResultYear" name="nResultYear">
      <xsl:attribute name="value">
        <xsl:value-of select="/response/action/form[@id='form1']/e[@id='nResultYear']/@value"/>
      </xsl:attribute>
    </input>

    <input type="hidden" id="nResultMonth" name="nResultMonth">
      <xsl:attribute name="value">
        <xsl:value-of select="/response/action/form[@id='form1']/e[@id='nResultMonth']/@value"/>
      </xsl:attribute>
    </input>

		<script>

			<xsl:text disable-output-escaping="yes">
				<![CDATA[
				
	
				]]>
			</xsl:text>

		</script>

		<div id="divTitle" class="table-light text-center h3 lead position-relative"></div>

		<table class="ps_layout">
			<tr>
				<td style="vertical-align:top" align="center">
					<table id="tableShifts"  class="shifts">
						<tr>
							<xsl:for-each select="/response/action/form[@id='form1']/e[@id='object_shifts']/option">
								<xsl:if test="position() = 14 or position() = 27">
									<xsl:text disable-output-escaping="yes">
										<![CDATA[
											</tr><tr>
										]]>
									</xsl:text>
								</xsl:if>
								<xsl:call-template name="shiftButton">
									<xsl:with-param name="shift" select="."/>
								</xsl:call-template>
							</xsl:for-each>
							<xsl:call-template name="shiftButton">
								<xsl:with-param name="shift" select="following-sibling::*"/>
							</xsl:call-template>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<div id="container" style="height: 380px; overflow-x: auto; overflow-y: auto;" class="result_data">
						<table id="tableResult" class="result table table-sm table-dark" >
<!--					<div id="container" style="overflow-x: auto; overflow-y: auto;" class="container-fluid body-content">-->
<!--						<table class="table table-sm table-bordered table-dark" id="tableResult">-->
							<tr>
								<xsl:for-each select="/response/result/fields/c">
									<xsl:variable name="pos" select="position()"/>
									<th id="{name(.)}[{name}]">
										<xsl:for-each select="@*">
											<xsl:attribute name="{name(.)}">
												<xsl:value-of select="."/>
											</xsl:attribute>
										</xsl:for-each>
										<xsl:value-of select="title"/>
									</th>
								</xsl:for-each>
							</tr>
							<xsl:for-each select="/response/result/data/r">
								<xsl:variable name="row" select="."/>
								<tr id="{$row/@id}">
									<xsl:for-each select="./@*">
										<xsl:if test="name(.) != 'id'">
											<xsl:attribute name="{name(.)}">
												<xsl:value-of select="."/>
											</xsl:attribute>
										</xsl:if>
									</xsl:for-each>
									<!--
									<td>
										<xsl:value-of select="position()"/>
									</td>
									-->
									<xsl:for-each select="/response/result/fields/c">
										<xsl:variable name="field" select="."/>
										<xsl:variable name="pos" select="position()"/>
										<td id="c[{$field/name}][{$row/@id}]">
											<xsl:for-each select="$row/c[ $pos ]/@*">
												<xsl:if test="name(.) != 'id'">
													<xsl:attribute name="{name(.)}">
														<xsl:value-of select="."/>
													</xsl:attribute>
												</xsl:if>
											</xsl:for-each>
											<xsl:if test="not( $row/c[ $pos ]/@class )">
												<xsl:attribute name="class">
													<xsl:value-of select="$field/name"/>
												</xsl:attribute>
											</xsl:if>
											<xsl:choose>
												<xsl:when test="string-length( $row/c[ $pos ] )">
													<xsl:choose>
														<xsl:when test="/response/result/fields/c[position() = $pos]/name != 'shift_hours' or $row/@id = '__TOTAL__'">
															<xsl:choose>
																<xsl:when test="/response/result/fields/c[position() = $pos]/link != ''">
																	<a href="#">
																		<xsl:attribute name="onClick">
																			<xsl:choose>
																				<xsl:when test="@id != ''">
																					<xsl:value-of select="/response/result/fields/c[position() = $pos]/link"/>('<xsl:value-of select="$row/c[ $pos ]/@id"/>')
																				</xsl:when>
																				<xsl:otherwise>
																					<xsl:value-of select="/response/result/fields/c[position() = $pos]/link"/>('<xsl:value-of select="$row/c[ $pos ]/../@id"/>')
																				</xsl:otherwise>
																			</xsl:choose>
																		</xsl:attribute>
																		<xsl:value-of select="$row/c[ $pos ]"/>
																	</a>
																</xsl:when>
																<xsl:otherwise>
																	<xsl:value-of select="$row/c[ $pos ]"/>
																</xsl:otherwise>
															</xsl:choose>
														</xsl:when>
														<xsl:otherwise>
															<xsl:value-of select="$row/c[ $pos ]/@value" disable-output-escaping="yes"/>
														</xsl:otherwise>
													</xsl:choose>
												</xsl:when>
												<xsl:otherwise>&nbsp;</xsl:otherwise>
											</xsl:choose>
										</td>
										<td style="display: none;">
											<xsl:if test="$field/name = 'shift_hours' and $row/@id != '__TOTAL__'">
												<input type="hidden" id="real_hours[{$row/@id}]" name="real_hours[{$row/@id}]" />
											</xsl:if>
											<xsl:if test="$row/@id != '__TOTAL__'">
												<input type="hidden" id="sid[{$field/name}][{$row/@id}]" value="0" />
											</xsl:if>
										</td>
									</xsl:for-each>
								</tr>
							</xsl:for-each>

							<tr>
								<xsl:for-each select="/response/result/fields/c">
									<xsl:variable name="pos" select="position()"/>
									<th id="{name(.)}[{name}]">
										<xsl:for-each select="@*">
											<xsl:attribute name="{name(.)}">
												<xsl:value-of select="."/>
											</xsl:attribute>
										</xsl:for-each>
										<xsl:value-of select="title"/>
									</th>
								</xsl:for-each>
							</tr>

						</table>
					</div>
				</td>
			</tr>
		</table>
		<div  class="row fixed-bottom px-3 py-2">
			<div class="col-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fas fa-print fa-fw" data-fa-transform="right-22 down-10" title="Фирма..."></span>
					</div>
					<select class="form-control" id="nIDPrintType" name="nIDPrintType">
						<option value="1"> за офиса </option>
						<option value="2"> за счетоводство </option>
						<option value="3"> в ексел </option>
					</select>
					<button class="btn btn-sm btn-secondary" onclick="onPrint()"><i class="fas fa-print"></i> Печат</button>
				</div>
			</div>
			<div class="col-10">
				<div class="input-group input-group-sm ml-1">
					<button class="btn btn-sm btn-info" onclick="dialogObjectPersonnelSchedule('nID={/response/action/form[@id='form1']/e[@id='nIDObject']/@value}')"><i class="far fa-users"></i> Служители</button>
					<button class="btn btn-sm btn-success mx-1" onclick="dialogObjectDuty('nID={/response/action/form[@id='form1']/e[@id='nIDObject']/@value}')"><i class="far fa-sync"></i> Смяна</button>
					<button class="btn btn-sm btn-success mx-1"  onclick="onSave()"><i class="far fa-save"></i> Запази</button>
					<button class="btn btn-sm btn-danger mx-1" onclick="cleanShifts()"><i class="far fa-eraser"></i> Почисти</button>
					<button class="btn btn-sm btn-warning mx-1" onclick="invalidate()"><i class="far fa-angle-double-left"></i></button>
					<button class="btn btn-sm btn-warning mx-1" onclick="validate()"><i class="far fa-angle-double-right"></i></button>
				</div>
			</div>
		</div>

		<script>
			onInit();
		</script>
		
	</xsl:template>

	<xsl:template name="shiftButton">
		<xsl:param name="shift" />
		<td style="display: {$shift/@visible}">
			<input type="hidden" name="shiftStake[{$shift/@id}]" value="{$shift/@custStake}"/>
			<input type="hidden" name="shiftDuration[{$shift/@id}]" value="{$shift/@shiftDuration}"/>
			<input type="hidden" name="shiftCoefDuration[{$shift/@id}]" value="{$shift/@shiftCoefDuration}"/>
			<input type="hidden" name="shiftIsLeave[{$shift/@id}]" value="{$shift/@shiftIsLeave}"/>


			<xsl:if test="@code">
				<button id="btnShift[{$shift/@id}]" name="btnShift[{$shift/@id}]" class="btnShift btn btn-sm btn-primary mx-2"  onclick="onShiftClick( event )" title="[{$shift/@code}] - {$shift/@description}&#013;Начало: {$shift/@shiftFromShort}&#013;Край: {$shift/@shiftToShort}&#013;Продължителност: {$shift/@paidDuration}&#013;Ставка: {$shift/@custStake}">
					<xsl:value-of select="@code"/>
				</button>
			</xsl:if>

			<xsl:if test="not(@code)">
				<button id="btnShift[{$shift/@id}]" name="btnShift[{$shift/@id}]" class="btnShift btn btn-sm btn-danger mx-2"  onclick="onShiftClick( event )">
					<xsl:value-of select="@code"/>
					&#160;
				</button>
			</xsl:if>


			<div id="btnShiftTooltip[{$shift/@id}]" style="display:none;">
				<table>
					<tr>
						<td colspan="2">
							<xsl:value-of select="$shift/@code"/>
							<xsl:text>&nbsp;</xsl:text>
							<xsl:value-of select="$shift/@description"/>
						</td>
					</tr>
					<tr>
						<td>начало:</td>
						<td>
							<xsl:value-of select="$shift/@shiftFromShort"/>
						</td>
					</tr>
					<tr>
						<td>край:</td>
						<td>
							<xsl:value-of select="$shift/@shiftToShort"/>
						</td>
					</tr>
					<tr>
						<td>продължителност:</td>
						<td>
							<xsl:value-of select="$shift/@paidDuration"/>
						</td>
					</tr>
					<tr>
						<td>ставка:</td>
						<td>
							<xsl:value-of select="$shift/@custStake"/>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</xsl:template>

</xsl:stylesheet>