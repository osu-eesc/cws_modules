<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!-- *** navigation bars: '', 'google', 'link', or 'simple'*** -->
	<xsl:variable name="show_top_navigation">1</xsl:variable>
	<xsl:variable name="choose_bottom_navigation">link</xsl:variable>
	<xsl:variable name="my_nav_align">right</xsl:variable>
	<xsl:variable name="my_nav_size">-1</xsl:variable>
	<xsl:variable name="my_nav_color">#6f6f6f</xsl:variable>
	<xsl:variable name="access">2</xsl:variable>

	<!-- *** sort by date/relevance *** -->
	<xsl:variable name="show_sort_by">1</xsl:variable>

	<xsl:variable name="nav_style">
		<xsl:choose>
			<xsl:when test="($access='s') or ($access='a')">simple</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="'link'"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
    <xsl:variable name="search_url"><xsl:value-of select="/GSP/DRUPAL/search_url" disable-output-escaping="yes"/></xsl:variable>
    <xsl:variable name="drupal_where"><xsl:value-of select="/GSP/DRUPAL/where" disable-output-escaping="yes"/></xsl:variable>
	<xsl:variable name="num_results">
		<xsl:choose>
			<xsl:when test="/GSP/PARAM[(@name='num') and (@value!='')]">
				<xsl:value-of select="/GSP/PARAM[@name='num']/@value"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="10"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:template match="trigger">
		<span class="highlight"><xsl:value-of select="." /></span>
	</xsl:template>
	<xsl:template match="/">
		<table id="querystats" summary="Query Specifics">
			<tr>
				<td>
					<xsl:choose>
						<xsl:when test="not(/GSP/RES/M)">
						</xsl:when>
						<xsl:when test="/GSP/RES/M &lt; 200">
							Results <xsl:value-of select="/GSP/RES/@SN" />-<xsl:value-of select="/GSP/RES/@EN" /> of <xsl:value-of select="/GSP/RES/M" />.
						</xsl:when>
						<xsl:otherwise>
							Results <xsl:value-of select="/GSP/RES/@SN" />-<xsl:value-of select="/GSP/RES/@EN" /> of about <xsl:value-of select="/GSP/RES/M" />.
						</xsl:otherwise>
					</xsl:choose>
					Search took <xsl:value-of select="/GSP/TM" /> seconds.
				</td>
			</tr>
		</table>
		<dl id="results">
			<xsl:if test="count(/GSP/RES/R) &lt; 1">
				<p>No results were found for <xsl:text disable-output-escaping="yes">&lt;span class=&quot;bold&quot;&gt;</xsl:text>
					<xsl:for-each select="/GSP/PARAM[@name='q']/@value">
						<xsl:value-of select="."/>
					</xsl:for-each><xsl:text disable-output-escaping="yes">&lt;/span&gt;</xsl:text>
				</p>
				<p>Suggestions:</p>
				<ul>
					<li>Check your spelling.</li>
					<li>Try similar keywords.</li>
					<li>Try more general keywords.</li>
				</ul>
			</xsl:if>
			<xsl:for-each select="/GSP/RES/R">
				<dt class="osu-search-result-title"><xsl:text disable-output-escaping="yes">&lt;a href=&quot;</xsl:text><xsl:value-of select="U" /><xsl:text disable-output-escaping="yes">&quot;&gt;</xsl:text><xsl:value-of select="T" disable-output-escaping="yes" /><xsl:text disable-output-escaping="yes">&lt;/a&gt;</xsl:text></dt>
				<dd><xsl:value-of select="S" disable-output-escaping="yes" /></dd>
				<dd class="osu-search-result-url"><xsl:value-of select="UE" disable-output-escaping="yes" /> - <xsl:value-of select="HAS/C/@SZ"/></dd>
			</xsl:for-each>


			<xsl:call-template name="google_navigation">
				<xsl:with-param name="prev" select="/GSP/RES/NB/PU"/>
				<xsl:with-param name="next" select="/GSP/RES/NB/NU"/>
				<xsl:with-param name="view_begin" select="/GSP/RES/@SN"/>
				<xsl:with-param name="view_end" select="/GSP/RES/@EN"/>
				<xsl:with-param name="guess" select="/GSP/RES/M"/>
				<xsl:with-param name="navigation_style" select="$nav_style"/>
			</xsl:call-template>
		</dl>
	</xsl:template>
	<xsl:template name="google_navigation">
		<xsl:param name="prev"/>
		<xsl:param name="next"/>
		<xsl:param name="view_begin"/>
		<xsl:param name="view_end"/>
		<xsl:param name="guess"/>
		<xsl:param name="navigation_style"/>

		<xsl:variable name="fontclass">
			<xsl:choose>
				<xsl:when test="$navigation_style = 'top'">s</xsl:when>
				<xsl:otherwise>b</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<!-- *** Test to see if we should even show navigation *** -->
		<xsl:if test="($prev) or ($next)">

			<!-- *** Start Google result navigation bar *** -->

			<xsl:if test="$navigation_style != 'top'">
				<xsl:text disable-output-escaping="yes">&lt;center&gt;
					&lt;div class=&quot;n&quot;&gt;</xsl:text>
			</xsl:if>

			<table border="0" cellpadding="0" width="1%" cellspacing="0">
				<tr align="center" valign="top">
					<xsl:if test="$navigation_style != 'top'">
						<td valign="bottom" nowrap="1">
							<font size="-1">
								<xsl:call-template name="nbsp"/>
							</font>
						</td>
					</xsl:if>


					<!-- *** Show previous navigation, if available *** -->
					<xsl:choose>
						<xsl:when test="$prev">
							<td nowrap="1">

								<span class="{$fontclass}">
                                    <a ctype="nav.prev" href="{$search_url}/{$view_begin -
                                        $num_results - 1}/{$drupal_where}">
										<xsl:if test="$navigation_style = 'google'">

											<img src="/nav_previous.gif" width="68" height="26"
												alt="Previous" border="0"/>
											<br/>
										</xsl:if>
										<xsl:if test="$navigation_style = 'top'">
											<xsl:text>&lt;</xsl:text>
										</xsl:if>
										<xsl:text>Previous</xsl:text>
									</a>
								</span>
								<xsl:if test="$navigation_style != 'google'">
									<xsl:call-template name="nbsp"/>
								</xsl:if>
							</td>
						</xsl:when>
						<xsl:otherwise>
							<td nowrap="1">
								<xsl:if test="$navigation_style = 'google'">
									<img src="/nav_first.png" width="18" height="26"
										alt="First" border="0"/>
									<br/>
								</xsl:if>
							</td>
						</xsl:otherwise>
					</xsl:choose>

					<xsl:if test="($navigation_style = 'google') or
						($navigation_style = 'link')">
						<!-- *** Google result set navigation *** -->
						<xsl:variable name="mod_end">
							<xsl:choose>
								<xsl:when test="$next"><xsl:value-of select="$guess"/></xsl:when>
								<xsl:otherwise><xsl:value-of select="$view_end"/></xsl:otherwise>
							</xsl:choose>
						</xsl:variable>

						<xsl:call-template name="result_nav">
							<xsl:with-param name="start" select="0"/>
							<xsl:with-param name="end" select="$mod_end"/>
							<xsl:with-param name="current_view" select="($view_begin)-1"/>
							<xsl:with-param name="navigation_style" select="$navigation_style"/>
						</xsl:call-template>
					</xsl:if>

					<!-- *** Show next navigation, if available *** -->
					<xsl:choose>
						<xsl:when test="$next">
							<td nowrap="1">
								<xsl:if test="$navigation_style != 'google'">
									<xsl:call-template name="nbsp"/>
								</xsl:if>
								<span class="{$fontclass}">
                                    <a ctype="nav.next" href="{$search_url}/{$view_begin + $num_results -1}/{$drupal_where}">
										<xsl:if test="$navigation_style = 'google'">

											<img src="/nav_next.png" width="100" height="26"

												alt="Next" border="0"/>
											<br/>
										</xsl:if>
										<xsl:text>Next</xsl:text>
										<xsl:if test="$navigation_style = 'top'">
											<xsl:text>&gt;</xsl:text>
										</xsl:if>
									</a>
								</span>
							</td>
						</xsl:when>
						<xsl:otherwise>
							<td nowrap="1">
								<xsl:if test="$navigation_style != 'google'">
									<xsl:call-template name="nbsp"/>
								</xsl:if>
								<xsl:if test="$navigation_style = 'google'">
									<img src="/nav_last.png" width="46" height="26"

										alt="Last" border="0"/>
									<br/>
								</xsl:if>
							</td>
						</xsl:otherwise>
					</xsl:choose>

					<!-- *** End Google result bar *** -->
				</tr>
			</table>

			<xsl:if test="$navigation_style != 'top'">
				<xsl:text disable-output-escaping="yes">&lt;/div&gt;
					&lt;/center&gt;</xsl:text>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	<xsl:template name="nbsp">
		<xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
	</xsl:template>
	<xsl:template name="nbsp3">
		<xsl:call-template name="nbsp"/>
		<xsl:call-template name="nbsp"/>
		<xsl:call-template name="nbsp"/>
	</xsl:template>
	<xsl:template name="nbsp4">
		<xsl:call-template name="nbsp3"/>
		<xsl:call-template name="nbsp"/>
	</xsl:template>
	<xsl:template name="quot">
		<xsl:text disable-output-escaping="yes">&amp;quot;</xsl:text>
	</xsl:template>
	<xsl:template name="copy">
		<xsl:text disable-output-escaping="yes">&amp;copy;</xsl:text>
	</xsl:template>
	<xsl:template name="result_nav">
		<xsl:param name="start" select="'0'"/>
		<xsl:param name="end"/>
		<xsl:param name="current_view"/>
		<xsl:param name="navigation_style"/>

		<!-- *** Choose how to show this result set *** -->
		<xsl:choose>
			<xsl:when test="($start)&lt;(($current_view)-(10*($num_results)))">
			</xsl:when>
			<xsl:when test="(($current_view)&gt;=($start)) and
				(($current_view)&lt;(($start)+($num_results)))">
				<td>
					<xsl:if test="$navigation_style = 'google'">
						<img src="/nav_current.gif" width="16" height="26" alt="Current"/>
						<br/>
					</xsl:if>
					<xsl:if test="$navigation_style = 'link'">
						<xsl:call-template name="nbsp"/>
					</xsl:if>
					<span class="i"><xsl:value-of
							select="(($start)div($num_results))+1"/></span>
					<xsl:if test="$navigation_style = 'link'">
						<xsl:call-template name="nbsp"/>
					</xsl:if>
				</td>
			</xsl:when>
			<xsl:otherwise>
				<td>
					<xsl:if test="$navigation_style = 'link'">
						<xsl:call-template name="nbsp"/>
					</xsl:if>
                    <a ctype="nav.page" href="{$search_url}/{$start}/{$drupal_where}">
						<xsl:if test="$navigation_style = 'google'">
							<img src="/nav_page.gif" width="16" height="26" alt="Navigation"
								border="0"/>
							<br/>
						</xsl:if>
						<xsl:value-of select="(($start)div($num_results))+1"/>
					</a>
					<xsl:if test="$navigation_style = 'link'">
						<xsl:call-template name="nbsp"/>
					</xsl:if>
				</td>
			</xsl:otherwise>
		</xsl:choose>

		<!-- *** Recursively iterate through result sets to display *** -->
		<xsl:if test="((($start)+($num_results))&lt;($end)) and
			((($start)+($num_results))&lt;(($current_view)+
			(10*($num_results))))">
			<xsl:call-template name="result_nav">
				<xsl:with-param name="start" select="$start+$num_results"/>
				<xsl:with-param name="end" select="$end"/>
				<xsl:with-param name="current_view" select="$current_view"/>
				<xsl:with-param name="navigation_style" select="$navigation_style"/>
			</xsl:call-template>
		</xsl:if>

	</xsl:template>
</xsl:stylesheet>
