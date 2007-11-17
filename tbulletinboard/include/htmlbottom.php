<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	//require_once($TBBclassDir . "Board.class.php");
	//$TBBboardList->updateStructureCache();

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
