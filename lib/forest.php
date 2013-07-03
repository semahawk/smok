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
	addnav("Szukaj Czego� Do Zabicia","forest.php?op=search");
	if ($session['user']['level']>1)
		addnav("Szw�daj Si�","forest.php?op=search&type=slum");
	addnav("Szukaj Przyg�d","forest.php?op=search&type=thrill");
	if (getsetting("suicide", 0)) {
		if (getsetting("suicidedk", 10) <= $session['user']['dragonkills']) {
			addnav("*?Szukaj `\$Samob�jczo`0", "forest.php?op=search&type=suicide");
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
	//		addnav("G?`4Szukaj Czarnoksi�nika","forest.php?op=dragon");
	//		else
	//		addnav("G?`@Szukaj Zielonego Smoka","forest.php?op=dragon");
	//	}
	}
	addnav("Inne");
	villagenav();
if (($session['user']['superuser'] & SU_MEGAUSER) || ($session['user']['login']=="Meandra")){
	      if (is_module_active("podwodnyswiat")&&$session['user']['location'] == 'Nautileum')
				addnav("Podwodny �wiat","runmodule.php?module=podwodnyswiat&op=");}
	if ($noshowmessage!=true){
	if ($session['user']['location'] == 'Nautileum')
  {
		output("`c`#`bRafa`b`0`c");
		output("Rafa koralowa, dom setek morskich stworze�.`n`n");
		output("B��kitny przestw�r ciep�ego oceanu otacza ci� zewsz�d. Feeria barw i kszta�t�w koralowc�w, szkar�upni, ryb i Tryton�w budzi szacunek i podziw dla pi�kna podwodnego �wiata. Jednak ta cudowna sceneria jest jednocze�nie siedzib� najdzikszych bestii, kt�re tylko czekaj� by zaatakowa� nieostro�nego podr�nika. Zdaj�c sobie z tego doskonale spraw�, mocniej zaciskasz d�onie na swym tr�jz�bie i p�yniesz ku g��binom.`n");
		rawoutput("<br><br><center><img src='http://s2.lotgd.pl/obrazki/rafa.gif' ></center><br>");  
		}
    else
		{
		output("`c`7`bLas`b`0`c");
		output("Las, dom wykl�tych kreatur i wyznawc�w z�a wszelkiego typu.`n`n");
		output("Grube le�ne poszycie ogranicza pole widzenia przewa�nie do kilku krok�w. �cie�ki by�yby niedostrzegalne, gdyby nie Twoje wy�wiczone oczy. Poruszasz si� cicho jak delikatny wietrzyk, przedzieraj�c si� przez grub� �ci�k� pokrywaj�c� ziemi� i wystrzegaj�c si� nadepni�cia na ga��zk� albo jedn� z niezliczonej liczby bielej�cych ko�ci, przynajmniej do chwili, gdy zdradzisz sw� obecno�� jednej z dzikich bestii penetruj�cych puszcz�.`n");
		rawoutput("<br><br><center><img src='http://s2.lotgd.pl/obrazki/las.gif' ></center><br>");
    }
		modulehook("forest-desc");
	}
	modulehook("forest", array());
	module_display_events("forest", "forest.php");
	tlschema();
}

?>
