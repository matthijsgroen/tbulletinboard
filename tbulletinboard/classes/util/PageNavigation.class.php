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

	/**
	 * PageNavigation creates a bar with navigation links
	 * to other pages. The first page is 1.
	 */
	class PageNavigation {

		var $privateVars;

		function PageNavigation($nrPages, $currentPage, $uri, $width) {
			$this->privateVars = array();
			$this->privateVars['nrPages'] = $nrPages;
			$this->privateVars['currentPage'] = $currentPage;
			$this->privateVars['uri'] = $uri;
			$this->privateVars['width'] = $width;
		}

		function showPagebar($className) {
			if ($this->privateVars['nrPages'] < 2) return;
			$width = $this->privateVars['width'];
?>
<div class="pagebar <?=$className; ?>">
<?php
			printf('Pagina\'s (%s): ', $this->privateVars['nrPages']);


			if (($this->privateVars['currentPage'] - ($width+1)) >= 0) {
				$uri = sprintf($this->privateVars['uri'], 1);
				printf('<a href="%s" title="eerste pagina" class="first">&lt; Eerste</a> ... ', $uri);
			}
			$startPage = $this->privateVars['currentPage'] - $width;
			if ($startPage < 1) $startPage = 1;

			$endPage = $this->privateVars['currentPage'] + ($width+1);
			if ($endPage > $this->privateVars['nrPages']) $endPage = $this->privateVars['nrPages']+1;

			if ($startPage < $this->privateVars['currentPage']) {
				$uri = sprintf($this->privateVars['uri'], ($this->privateVars['currentPage']-1));
				printf('<a href="%s" title="vorige pagina" class="previous">&laquo;</a> ', $uri);
			}

			for ($i = $startPage; $i < $endPage; $i++) {
				if ($i == $this->privateVars['currentPage']) {
					printf('<b>[%s]</b> ', $i);
				} else {
					$uri = sprintf($this->privateVars['uri'], ($i));
					printf('<a href="%s">%s</a> ', $uri, $i);
				}
			}

			if ($endPage > ($this->privateVars['currentPage'] + 1)) {
				$uri = sprintf($this->privateVars['uri'], ($this->privateVars['currentPage']+1));
				printf('<a href="%s" title="volgende pagina" class="next">&raquo;</a> ', $uri);
			}

			$endPage = $this->privateVars['currentPage'] + ($width+1);
			if ($endPage < $this->privateVars['nrPages']) {
				$uri = sprintf($this->privateVars['uri'], ($this->privateVars['nrPages']));
				printf('... <a href="%s" title="laatste pagina" class="last">Laatste &gt;</a>', $uri);
			}
?>
</div>
<?
		}

		function quickPageBarStr($className, $image) {
			if ($this->privateVars['nrPages'] < 2) return;
			$width = $this->privateVars['width'];
			$result = sprintf('<span class="%s"><img src="%s" alt="" />(', $className, $image);

			$startPage = $this->privateVars['currentPage'];
			if ($startPage < 0) $startPage = 0;

			$endPage = $this->privateVars['currentPage'] + ($width+1);
			if ($endPage > $this->privateVars['nrPages']-1) $endPage = $this->privateVars['nrPages'];

			for ($i = $startPage; $i < $endPage; $i++) {
				if ($i == $this->privateVars['currentPage']) {
					$result .= sprintf('<b>[%s]</b> ', $i+1);
				} else {
					$uri = sprintf($this->privateVars['uri'], ($i+1));
					$result .= sprintf('<a href="%s">%s</a>', $uri, $i+1);
				}
				if ($i < ($endPage - 1)) $result .= ' ';
			}
			$endPage = $this->privateVars['currentPage'] + ($width+1);
			if ($endPage < $this->privateVars['nrPages']) {
				$uri = sprintf($this->privateVars['uri'], ($this->privateVars['nrPages']));
				$result .= sprintf(' ... <a href="%s" title="laatste pagina" class="last">Laatste pagina</a>', $uri);
			}

			$result .= ')</span>';
			return $result;
		}
	}

?>
