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

	class EmoticonList {

		var $privateVars;

		function EmoticonList() {
			$this->privateVars = array();
			$this->privateVars['emoticons'] = array();
			$this->privateVars['emoticonsParseOrder'] = array();
		}

		static function sortEmoticonCodes($emoticonA, $emoticonB) {
			$aL = strLen($emoticonA['code']);
			$bL = strLen($emoticonB['code']);
			if ($aL == $bL) return 0;
			return ($aL > $bL) ? -1 : 1;
		}

		function getEmoticonsParseOrder() {
			return $this->privateVars['emoticonsParseOrder'];
		}
		
		function clearEmoticons() {
			$this->privateVars['emoticons'] = array();
			$this->privateVars['emoticonsParseOrder'] = array();
		}

		function addEmoticon($name, $id, $imgUrl, $codes, $filename) {
			$emoticon = array();
			$emoticon['ID'] = $id;
			$emoticon['name'] = $name;
			$emoticon['imgUrl'] = $imgUrl;
			$emoticon['textCodes'] = $codes;
			$emoticon['filename'] = $filename;
			for ($i = 0; $i < count($codes); $i++) {
				$this->privateVars['emoticonsParseOrder'][] = array('imgUrl' => $emoticon['imgUrl'], 'code' => $codes[$i], 'name' => $emoticon['name']);
			}
			usort($this->privateVars['emoticonsParseOrder'], array("EmoticonList", "sortEmoticonCodes"));

			$this->privateVars['emoticons'][] = $emoticon;
		}

		function getEmoticons() {
			return $this->privateVars['emoticons'];
		}

		function getEmoticonPicker($fieldID, $formID) {
			$result = "";
			$js = sprintf('<script type="text/javascript">function addEmoticon%s(id) {'."\n".
				"\t".'var smileyCode = "";'."\n".
				"\t".'switch (id) {'."\n", $fieldID);
			
			foreach($this->privateVars['emoticons'] as $emoticon) {
				$result .= sprintf('<a href="javascript:addEmoticon%s(%s)"><img src="%s" title="%s" /></a>', 
					$fieldID, $emoticon['ID'], $emoticon['imgUrl'], $emoticon['name']);
				$js .= sprintf("\t\t".'case %s: smileyCode = "%s"; break;'."\n", $emoticon['ID'], $emoticon['textCodes'][0]);
			}
			$js .= sprintf("\t}\n\t".'var textarea = document.%s.%s;'."\n".
				"\t".'insertAtCursor(textarea, smileyCode);'."\n".
				'}'."\n</script>", $formID, $fieldID);
			
			return $result.$js;
		}

		function indexOf($id) {
			for ($i = 0; $i < count($this->privateVars['emoticons']); $i++) {
				if ($this->privateVars['emoticons'][$i]['ID'] == $id) return $i;
			}
			return false;
		}

		function getEmoticon($id) {
			$index = $this->indexOf($id);
			if ($index !== false) {
				return $this->privateVars['emoticons'][$index];
			}
			return false;
		}

		function removeEmoticon($id) {
			$index = $this->indexOf($id);
			if ($index != false) {
				array_splice($this->privateVars['emoticons'], $index, 1);
				//unset([$index]);
			}
			return false;
		}

	}

?>
