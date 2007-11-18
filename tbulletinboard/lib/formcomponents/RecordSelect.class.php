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
	 * Import the FormComponent superclass
	 */
	require_once($libraryClassDir."Form.class.php");

	/**
	 * Component to put a form into a form to select records
	 */
	class FormRecordSelect extends FormComponent {

		var $table;
		var $sorting;

		function FormRecordSelect(&$table, $sorting = false) {
			$this->FormComponent("Title", "Description", "Table");
			$this->table =& $table;
			$this->sorting = $sorting;
		}

		function printComponent() {
?>
		<tr>
			<td colspan="2">
<?php $this->table->showTable($this->sorting); ?>
			</td>
		</tr>
<?php
		}


		function getInput() {
			$onChangeScript = new JavaScript();
			$onChangeScript->startFunction("field".$this->form->id.$this->identifier."IsChanged");
			//if($this->onchange != "") $onChangeScript->addLine($this->onchange);
			$onChangeScript->addLine("form".$this->form->id."IsChanged();");
			$onChangeScript->endBlock();
			$this->attachScript($onChangeScript);
			
			if(is_object($this->form)) {
				$this->table->setOnChange("field".$this->form->id.$this->identifier."IsChanged();");
			}

			$this->table->showTable($this->sorting);
		}

	}


?>
