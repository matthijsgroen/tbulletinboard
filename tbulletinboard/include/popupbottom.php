<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
?>

	<div id="copyright">
		THAiSies Bulletin Board 2.0&alpha;, &copy; 2003 IVinity<br />
		pagina gemaakt in <?
			list($micro1, $stamp1) = explode(" ", $pageTime);
			list($micro2, $stamp2) = explode(" ", microTime());

			print (($stamp2 - $stamp1) + ($micro2 - $micro1));

		?>s, queries: <?=$procCounter ?></div>
	</div>
</body>
</html>