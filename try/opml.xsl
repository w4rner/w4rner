<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/opml">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
			<head>
				<title><xsl:value-of select="head/title"/></title>
				<style type="text/css">
					body {
						padding: 2ex;
						font-family: arial, sans-serif;
						font-size: 10pt;
					}
					
					dl {
						margin-bottom: 2ex;
					}
					
					dl dl {
						padding-left: 4ex;
						padding-bottom: 2ex;
						margin-left: 1ex;
						border-left: 1px dashed #666;
						border-bottom: 1px dashed #666;
					}
					
					dd {
						padding: 1ex 1ex 1ex 0;
					}
					
					.folder h3 {
						background-image: url(/files/folder-open-icon.gif);
					}
					
					.folder > h3 {
						background-image: url(/files/folder-open-icon.gif);
					}
					
					.folder[open="false"] > h3 {
						background-image: url(/files/folder-icon.gif);
					}
					
					.folder[open="false"] > dl {
						display: none;
					}
					
					.folder h3 {
						cursor: default;
					}
					
					.feed, .link a, .folder h3 {
						padding-left: 20px;
						background-repeat: no-repeat;
					}
					
					.feed {
						background-image: url(/files/feed-icon.gif);
					}
					
					.link {
						padding-bottom: 0.5ex;
					}
					
					.link a {
						background-image: url(/files/link-icon.gif);
					}
				</style>
				<script type="text/javascript">
					<![CDATA[
					
					function toggle(listHeader, listTitle){
						var isOpen = listHeader.getAttribute("open");
						
						if (isOpen == "true"){
							listHeader.setAttribute("open","false");
							listHeader.childNodes[1].style.display = 'none';
							listTitle.style.backgroundImage = "url(/images/folder-icon.gif)";
						}
						else {
							listHeader.setAttribute("open","true");
							listHeader.childNodes[1].style.display = '';
							listTitle.style.backgroundImage = "url(/images/folder-open-icon.gif)";
						}
					}
					
					function openAll(list){
						var linkList = list.childNodes;
						
						for (var i in linkList){
							var listNode = linkList[i];
							
							if (listNode.nodeName){
								if (listNode.nodeName.toUpperCase() == 'DT'){
									var nodes = listNode.childNodes;
									
									for (var j in nodes){
										var node = nodes[j];
										
										if (node.nodeName){
											if (node.nodeName.toUpperCase() == 'A'){
												window.open(node.href);
												shouldBreak = true;
												break;
											}
										}
									}
								}
							}
						}
					}
					
					]]>
				</script>
			</head>
			<body>
				<h2><xsl:value-of select="head/title"/></h2>
				<h3><xsl:value-of select="head/dateCreated"/></h3>
				<dl>
					<xsl:apply-templates select="body/outline"/>
				</dl>
			</body>
		</html>
	</xsl:template>
	<xsl:template match="outline" xmlns="http://www.w3.org/1999/xhtml">
		<xsl:choose>
			<xsl:when test="@type">
				<xsl:choose>
					<xsl:when test="@xmlUrl">
						<dt class="link">
							<a href="{@htmlUrl}"><xsl:value-of select="@text"/></a>
						</dt>
						<dd>
							<a class="feed" href="{@xmlUrl}"><xsl:value-of select="@xmlUrl"/></a>
							<xsl:choose>
								<xsl:when test="@description != ''">
									<br /><br />
									<xsl:value-of select="@description"/>
								</xsl:when>
							</xsl:choose>
						</dd>
					</xsl:when>
					<xsl:otherwise>
						<dt class="link">
							<a href="{@url}"><xsl:value-of select="@text"/></a>
						</dt>
						<xsl:choose>
							<xsl:when test="@description != ''">
								<dd>
									<xsl:value-of select="@description"/>
								</dd>
							</xsl:when>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<dt class="folder" open="true">
					<h3 onclick="toggle(this.parentNode, this);" ondblclick="openAll(this.nextSibling);">
						<xsl:value-of select="@text"/>
					</h3>
					<dl>
						<xsl:apply-templates select="outline"/>
					</dl>
				</dt>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
