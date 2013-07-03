<?php
// addnews ready
// translator ready
// mail ready
require_once("lib/villagenav.php");

function forest($noshowmessage=false) {
	global $session,$playermount;
	tlschema("Las");
//	mass_module_prepare(array("forest", "validforestloc"));
	addnav("Leczenie");
	addnav("Chata Znachora","healer.php");
	addnav("Walcz");
	addnav("Szukaj Czego¶ Do Zabicia","forest.php?op=search");
	if ($session['user']['level']>1)
		addnav("Szwêdaj Siê","forest.php?op=search&type=slum");
	addnav("Szukaj Przygód","forest.php?op=search&type=thrill");
	if (getsetting("suicide", 0)) {
		if (getsetting("suicidedk", 10) <= $session['user']['dragonkills']) {
			addnav("*?Szukaj `\$Samobójczo`0", "forest.php?op=search&type=suicide");
		}
	}
	if ($session['user']['level']>=15  && $session['user']['seendragon']==0){
		// Only put the green dragon link if we are a location which
		// should have a forest.   Don't even ask how we got into a forest()
		// call if we shouldn't have one.   There is at least one way via
		// a superuser link, but it shouldn't happen otherwise.. We just
		// want to make sure however.
		$isforest = 0;
		$vloc = modulehook('validforestloc', array());
		foreach($vloc as $i=>$l) {
			if ($session['user']['location'] == $i) {
				$isforest = 1;
				break;
			}
		}
	//	if ($isforest || count($vloc)==0) {
	//			if ($session['user']['race'] == 'Jaszczurzyc')
	//		addnav("G?`4Szukaj Czarnoksiê¿nika","forest.php?op=dragon");
	//		else
	//		addnav("G?`@Szukaj Zielonego Smoka","forest.php?op=dragon");
	//	}
	}
	addnav("Inne");
	villagenav();
if (($session['user']['superuser'] & SU_MEGAUSER) || ($session['user']['login']=="Meandra")){
	      if (is_module_active("podwodnyswiat")&&$session['user']['location'] == 'Nautileum')
				addnav("Podwodny ¦wiat","runmodule.php?module=podwodnyswiat&op=");}
	if ($noshowmessage!=true){
	if ($session['user']['location'] == 'Nautileum')
  {
		output("`c`#`bRafa`b`0`c");
		output("Rafa koralowa, dom setek morskich stworzeñ.`n`n");
		output("B³êkitny przestwór ciep³ego oceanu otacza ciê zewsz±d. Feeria barw i kszta³tów koralowców, szkar³upni, ryb i Trytonów budzi szacunek i podziw dla piêkna podwodnego ¶wiata. Jednak ta cudowna sceneria jest jednocze¶nie siedzib± najdzikszych bestii, które tylko czekaj± by zaatakowaæ nieostro¿nego podró¿nika. Zdaj±c sobie z tego doskonale sprawê, mocniej zaciskasz d³onie na swym trójzêbie i p³yniesz ku g³êbinom.`n");
		rawoutput("<br><br><center><img src='http://s2.lotgd.pl/obrazki/rafa.gif' ></center><br>");  
		}
    else
		{
		output("`c`7`bLas`b`0`c");
		output("Las, dom wyklêtych kreatur i wyznawców z³a wszelkiego typu.`n`n");
		output("Grube le¶ne poszycie ogranicza pole widzenia przewa¿nie do kilku kroków. ¦cie¿ki by³yby niedostrzegalne, gdyby nie Twoje wyæwiczone oczy. Poruszasz siê cicho jak delikatny wietrzyk, przedzieraj±c siê przez grub± ¶ció³kê pokrywaj±c± ziemiê i wystrzegaj±c siê nadepniêcia na ga³±zkê albo jedn± z niezliczonej liczby bielej±cych ko¶ci, przynajmniej do chwili, gdy zdradzisz sw± obecno¶æ jednej z dzikich bestii penetruj±cych puszczê.`n");
		rawoutput("<br><br><center><img src='http://s2.lotgd.pl/obrazki/las.gif' ></center><br>");
    }
		modulehook("forest-desc");
	}
	modulehook("forest", array());
	module_display_events("forest", "forest.php");
	tlschema();
}

?>
