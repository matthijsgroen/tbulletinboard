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
	 * A Menu for almost all navigation purposes. It outputs standard HTML. But with CSS this simple structure can be formed
	 * to show Outlookbars, tabsheets, horizontal menu's, vertical menu's, toolbars, etc.
	 *@version 1.1
	 */
	class Menu {

		/**
		 * A private variabel collection. Alle menu items and groups will be stored here
		 *@var array $privateVars
		 */
		var $privateVars;

		/**
		 * The itemindex of the selected menuitem. This is a string containing the selected menu item's tag.
		 *@var string $itemIndex
		 */
		var $itemIndex;


		function Menu() {
			$this->privateVars = array();
			$this->privateVars['options'] = array();
			$this->privateVars['groups'] = array();
			$this->itemIndex = '';

			$this->addGroup('', ''); // root group
		}

		/**
		 * Adds a menu item to a specific item group.
		 *@param string $tag the tagname of the new item
		 *@param string $groupTag the tag of the group this item will be placed in. If there is no group
		 * with the given tag, the tag will not be shown! an empty string '' is the rootgroup.
		 *@param string $name this is the name of the item.
		 *@param string $url the url this item points to
		 *@param string $target the name of the target frame where the url will be opened
		 *@param string $icon an url to an icon to show for the item. If empty, no icon will be shown
		 *@param int $contains The number of item this item contains. eg. showing the user it has unread email.
		 * The item will be shown with (5) after the item.
		 *@param bool $highligh if <code>true</code> this item gets emphasized
		 *@param string $hint the hintmessage to show on the menu item
		 */
		function addItem($tag, $groupTag, $name, $url, $target, $icon, $contains, $highlight, $hint) {
			$this->privateVars['options'][] = array(
				'type' => 'item',
				'name' => $name,
				'url' => $url,
				'groupTag' => $groupTag,
				'tag' => $tag,
				'contains' => $contains,
				'target' => $target,
				'icon' => $icon,
				'highlight' => $highlight,
				'hint' => $hint
			);
		}

		function addSelect($tag, $groupTag, $onChange, $options, $icon, $selected) {
			$this->privateVars['options'][] = array(
				'type' => 'select',
				'groupTag' => $groupTag,
				'tag' => $tag,
				'icon' => $icon,
				'onchange' => $onChange,
				'options' => $options,
				'selected' => $selected
			);
		}

		function addGroup($groupTag, $name) {
			for ($i = 0; $i < count($this->privateVars['groups']); $i++) {
				if ($this->privateVars['groups'][$i]['tag'] == $groupTag) return false;
			}
			$this->privateVars['groups'][] = array(
				'name' => $name,
				'tag' => $groupTag
			);
		}

		function showMenu($cssClass) {
			print $this->getMenuStr($cssClass);
		}

		function getMenuStr($cssClass) {
			$enter = "\n";
			$tab = "\t";
			$result = "<div class=\"".$cssClass."\"><div class=\"menu\">".$enter;
			for ($g = 0; $g < count($this->privateVars['groups']); $g++) {
				$group = $this->privateVars['groups'][$g];
				if ($group['name'] != '') {
					$result .= $tab."<span class=\"menuheader\">".$group['name']."</span>".$enter;
				}
				for ($i = 0; $i < count($this->privateVars['options']); $i++) {
					$item = $this->privateVars['options'][$i];
					if ($item['type'] == 'item') {
						$imgUrl = "";
						if (strCmp($item["icon"], "") != 0) {
							$imgUrl = '<img src="'.$item["icon"].'" alt="" title="'.$item['hint'].'" border="0" />';
						}
						if ($item['groupTag'] == $group['tag']) {
							if (strCmp($item['tag'], $this->itemIndex) == 0) {
								$result .= $tab.'<span class="menuitem selected">'.$imgUrl.'<b>'.$item['name'].'</b></span>'.$enter;
							} else {
								if (strLen($item['url']) > 0) {
									$url = $item['url'];
									$result .= sprintf(
										$tab.'<span class="menuitem"><a href="%s" title="%s"%s>%s%s%s%s</a></span>'.$enter,
										$url,
										$item['hint'],
										($item['target'] != '') ? 'target="'.$item['target'].'"' : "",
										$imgUrl,
										($item["highlight"]) ? '<b class="highlight">'.$item['name'] : $item['name'],
										($item["contains"] == 0) ? "" : " (".$item["contains"].")",
										($item["highlight"]) ? '</b>' : ""
									);
								} else {
									$result .= $tab.'<span class="menuitem"><span class="disabled">'.$imgUrl.$item['name'].'</span></span>'.$enter;
								}
							}
							if ($i < count($this->privateVars['options']) -1 ) {
								if ($this->privateVars['options'][$i+1]['groupTag'] == $group['tag'])
									$result .= $tab."<span class=\"optiondivider\">|</span>".$enter;
							}
						}
					}
					if (($item['type'] == 'select') && ($item['groupTag'] == $group['tag'])) {
 						$result .= $tab.'<span class="menuselect"><select name="'.$item['tag'].'" onchange="'.$item['onchange'].'">'.$enter;
						reset($item['options']);
						while (list($value, $caption) = each($item['options'])) {
							$result .= sprintf(
								$tab.$tab.'<option value="%s"%s>%s</option>'.$enter,
								$value,
								($value == $item['selected']) ? ' selected="selected"' : '',
								$caption
							);
						}
						$result .= $tab.'</select></span>'.$enter;
						if ($i < count($this->privateVars['options']) -1 ) {
							if ($this->privateVars['options'][$i+1]['groupTag'] == $group['tag'])
								$result .= $tab.'<span class="optiondivider">|</span>'.$enter;
						}
					}
				}
			}
			$result .= '</div></div>'.$enter;
			return $result;
		}

	}

?>
