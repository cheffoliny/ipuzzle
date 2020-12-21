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

		</script>

		<div id="divTitle"></div>
		<hr />
		<table class="ps_layout">
			<tr>
				<td>
					<div id="container" style="height: 150px; overflow-x: auto; overflow-y: auto;" class="result_data">
						<table id="tableResult" class="result" cellspacing="0px">
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
												<xsl:otherwise>&nbsp;</xsl:otherwise>
											</xsl:choose>
										</td>
									</xsl:for-each>
								</tr>
							</xsl:for-each>


						</table>
					</div>
				</td>
			</tr>
		</table>

		<script>
			//onInit();
		</script>
		
	</xsl:template>


</xsl:stylesheet>
