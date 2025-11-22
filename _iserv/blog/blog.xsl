<?xml version='1.0' encoding="UTF-8"?> 
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="blog">
		<div>
		<xsl:for-each select="comment"><xsl:sort select="date" order="ascending"/>
			<p>
			<xsl:number/><xsl:text>. </xsl:text>
			<xsl:text>Posté le </xsl:text><xsl:value-of select="substring(date,7,2)" />/<xsl:value-of select="substring(date,5,2)" />/<xsl:value-of select="substring(date,1,4)" /> - <xsl:value-of select="substring(date,9,2)" />:<xsl:value-of select="substring(date,11,2)" />
			<xsl:text> par </xsl:text>
			<strong>
			<xsl:choose>
				<xsl:when test="url != ''"> 
				<a target="_blank"><xsl:attribute name="href"><xsl:value-of select="url"/></xsl:attribute><xsl:copy-of select="author"/></a>
				</xsl:when>
				<xsl:otherwise>
				<xsl:copy-of select="author"/>
				</xsl:otherwise>
			</xsl:choose>
			</strong>
					<div style="text-align:justify;padding:10px;margin-left:10px; margin-right:10px;border:dotted 1px #C0C0C0;">
					<xsl:copy-of select="text"/>
					</div>
			</p>
		</xsl:for-each>
		</div>
	</xsl:template>
</xsl:stylesheet>
