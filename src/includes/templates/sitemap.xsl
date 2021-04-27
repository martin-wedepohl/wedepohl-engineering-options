<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:us="http://www.sitemaps.org/schemas/sitemap/0.9">
<xsl:template match="us:urlset">
<html> 
<body>
   <section style="display:grid;justify-content:center">
      <h2 style="text-align:center">Pages</h2>
      <table style="border-collapse:collapse;" border="1">
         <tr style="background-color:#cecece40">
            <th style="text-align:center;padding:.5em;">Location</th>
            <th style="text-align:center;padding:.5em;">Last Modification</th>
            <th style="text-align:center;padding:.5em;">Change Frequency</th>
            <th style="text-align:center;padding:.5em;">Priority</th>
         </tr>
         <xsl:for-each select="us:url">
         <tr>
            <td style="padding:.5em;"><xsl:value-of select="us:loc"/></td>
            <td style="text-align:center;padding:.5em;"><xsl:value-of select="us:lastmod"/></td>
            <td style="text-align:center;padding:.5em;"><xsl:value-of select="us:changefreq"/></td>
            <td style="text-align:center;padding:.5em;"><xsl:value-of select="us:priority"/></td>
         </tr>
         </xsl:for-each>
      </table>
   </section>
</body>
</html>
</xsl:template>
</xsl:stylesheet>