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

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Wiki Help';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	importClass("interface.Form");
	importClass("interface.Location");
	importClass("interface.Table");
	importClass("util.TextParser");
	importClass("board.Board");
	importClass("interface.Text");
	importClass("board.ActionHandler");

	$feedback->showMessages();

	$board = $TBBboardList->getBoard($TBBconfiguration->getHelpBoardID());
	$here = $board->getLocation();
	$here->addLocation('Wiki Syntax', 'wikihelp.php');
	$here->showLocation();

	ob_start();
?>
== Tekst layout ==

||~Naam/Voorbeeld||~Layout||
||_cursief_||``_cursief_``||
||*vet*||``*vet*``||
||^super^script||``^super^script``||
||,,sub,,script||``,,sub,,script``||
||~~doorgestreept~~||``~~doorgestreept~~``||

Je kan deze tekst layouts ook combineren:

||~Layout||~Resultaat||
||``_*bold* in italics_``||_*bold* in italics_||
||``*_italics_ in bold*``||*_italics_ in bold*||
||``*~~strike~~ works too*``||*~~strike~~ works too*||
||``~~as well as _this_ way round~~``||~~as well as _this_ way round~~||

== Lijsten ==

TBB wiki syntax ondersteunt bullet en genummerde lijsten.
<code>
Hieronder staat:
  * Een lijst
  * met bullet items
    # Dit is een genummerde sublijst
    # Dit kan je doen door verder in te springen
  * En weer terug in de hooflijst

 * Dit is ook een lijst
 * Met een enkele ingesprongen spatie
 * Je ziet dat het getoont wordt
  # Op het zelfde ingesprongen niveau
  # als de bovenstaande lijst
 * Ondanks dat deze minder ver was ingesprongen
</code>

Hieronder staat:
  * Een lijst
  * met bullet items
    # Dit is een genummerde sublijst
    # Dit kan je doen door verder in te springen
  * En weer terug in de hooflijst

 * Dit is ook een lijst
 * Met een enkele ingesprongen spatie
 * Je ziet dat het getoont wordt
  # Op het zelfde ingesprongen niveau
  # als de bovenstaande lijst
 * Ondanks dat deze minder ver was ingesprongen

== Links ==

door gewoon een internet adres te typen wordt het automatisch een link.
bijvoorbeeld: http://thaisi.thaboo.com/

om een link een naam te geven kan je er haakjes omheen zetten, zoals
``[http://thaisi.thaboo.com/ Mijn homepage]`` [http://thaisi.thaboo.com/ Mijn homepage]

== Horizontale scheiding ==
``----`` (4x minteken)
----

== Inspringen ==
<code>
> deze tekst is ingesprongen.
>> dubbel ingesprongen
> enkel niveau weer
en weer normaal
</code>
> deze tekst is ingesprongen.
>> dubbel ingesprongen
> enkel niveau weer
en weer normaal

== Tabellen ==

Je kan tabellen maken met paren van vertikale balken:
<code>
|| cell een || cell twee ||
|||| over hele regel ||
|| cell vier || cell vijf ||
|| cell zes || en hier een met veel tekst ||
</code>
|| cell een || cell twee ||
|||| over hele regel ||
|| cell vier || cell vijf ||
|| cell zes || en hier een met veel tekst ||

<code>
|| regels moeten beginnen en eindigen met || dubbele vertikale balken || leeg ||
|| cellen zijn gescheiden door || dubbele vertikale balken || leeg ||
|||| Je kan meerdere kolommen beslaan door || elke cell te laten beginnen ||
|| met extra cell |||| scheidingen ||
|||||| waarschijnlijk is een voorbeeld het makkelijkste om het te zien ||
</code>
|| regels moeten beginnen en eindigen met || dubbele vertikale balken || leeg ||
|| cellen zijn gescheiden door || dubbele vertikale balken || leeg ||
|||| Je kan meerdere kolommen beslaan door || elke cell te laten beginnen ||
|| met extra cell |||| scheidingen ||
|||||| waarschijnlijk is een voorbeeld het makkelijkste om het te zien ||

=== cel opmaak ===
Je kunt bij cellen ook aangeven of de inhoud links, rechts of gecentreerd uitgelijnd moet worden.
Ook kan je aangeven of een cel gezien moet worden als een kolomkop
<code>
||~ Kolomkop ||= midden ||
||> Rechts ||< Links ||
|| even hier wat || breedte scheppen ||
</code>
||~ Kolomkop ||= midden ||
||> Rechts ||< Links ||
|| even hier wat || breedte scheppen ||


<?php
	$text = ob_get_contents();
	ob_end_clean();

	$present = new Text();
	$present->addHTMLText($textParser->parseMessageText($text, false, false, array(), true));
	$present->showText();
	
	include($TBBincludeDir.'htmlbottom.php');
?>
