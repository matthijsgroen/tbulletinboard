<?php
	/**
	 *	TBB2, an highly configurable and dynamic bulletin board
	 *	Copyright (C) 2007  Matthijs Groen
	 *
	 *	This program is free software: you can redistribute it and/or modify
	 *	it under the terms of the GNU General Public License as published by
	 *	the Free Software Foundation, either version 3 of the License, or
	 *	(at your option) any later version.
	 *	
	 *	This program is distributed in the hope that it will be useful,
	 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *	GNU General Public License for more details.
	 *	
	 *	You should have received a copy of the GNU General Public License
	 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *	
	 */

?>
	<div id="copyright">
		THAiSies Bulletin Board <?=$boardVersion ?>, &copy; 2003-2007 Matthijs Groen
		<span class="divider">|</span>
		<a href="http://validator.w3.org/check/referer"><acronym title="Extensible Hypertext Markup Language">XHTML</acronym></a>
		<span class="divider">|</span>
		<a href="http://jigsaw.w3.org/css-validator/check/referer"><acronym title="Cascading style sheets">CSS</acronym></a>
		<span class="divider">|</span>
		<a href="plugininfo.php">Plug-ins</a>
		<br />
		pagina gemaakt in <?
			list($micro1, $stamp1) = explode(" ", $pageTime);
			list($micro2, $stamp2) = explode(" ", microTime());

			print (($stamp2 - $stamp1) + ($micro2 - $micro1));

		?>s, queries: <?=$GLOBALS['queriesExecuted'] ?></div>
</body>
</html>
