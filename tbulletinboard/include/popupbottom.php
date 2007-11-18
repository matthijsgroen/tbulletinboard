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
		THAiSies Bulletin Board 2.0&alpha;, &copy; 2003 IVinity<br />
		pagina gemaakt in <?
			list($micro1, $stamp1) = explode(" ", $pageTime);
			list($micro2, $stamp2) = explode(" ", microTime());

			print (($stamp2 - $stamp1) + ($micro2 - $micro1));

		?>s, queries: <?=$procCounter ?></div>
	</div>
</body>
</html>
