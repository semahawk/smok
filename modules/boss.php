<?php

require_once("lib/villagenav.php");

function boss_getmoduleinfo()
{
  $info = array(
    "name" => "Walka z Bossem",
    "version" => "0.1.0",
    "author" => "`GCiuf",
    "category" => "Forest",
    "download" => "example.com",
    "settings" => array (
      "Walka z Bossem,title",
      "dev" => "Wersja developerska,bool|true",
    ),
    "prefs" => array (
      "Walka z bossem,title",
      "badguy_name" => "Name of the boss user would be fighting,string|[FIXME] Szkieletor",
      "badguy_weapon" => "The boss' weapon,string|[FIXME] Szkieletowate lapska"
    )
  );

  return $info;
}

function boss_install()
{
  module_addeventhook("forest", "return 100;");
  module_addhook("forest");

  return true;
}

function boss_uninstall()
{
  return true;
}

function boss_dohook($hookname, $args)
{
  global $session;

  switch ($hookname){
    case "forest":
      if ($session['user']['level'] >= 15){
        /* FIXME to chyba da się znacjonalizować */
        addnav("Fight");
        addnav("`@Walcz z bossem!`0", "runmodule.php?module=boss&op=enter");
      }
      break;
  }

  return $args;
}

function boss_runevent($type, $link)
{
  // NULL
}

function boss_run()
{
  global $session;

  $op = httpget('op');
  $here = "runmodule.php?module=boss";
  $bname = get_module_pref("badguy_name");

  page_header($bname . "!");

  switch ($op){
    case "enter":
      output("`c`GWYCZESANY `Etekst o tym jak to chcesz dowalic bossowi ale sie cykasz i nie jestes pewien`c");
      addnav("Zmierz sie z bossem", "$here&op=fight");
      addnav("Bierz tylek w troki", "$here&op=flee");
      break;
    case "fight":
      output("`c`ETutaj `GEPICKA `Ewalka z `7$bname`0`c");
      break;
    case "flee":
      output("`c`EStwierdzasz, ze jestes za `GMIEKKI `Ena bossa, ale jeszcze tutaj wrocisz.`c");
      villagenav();
      break;
  }

  if ($dev){
    addnav("DEV");
    addnav("Refresh", "$here");
    villagenav();
  }

  page_footer();
}

?>
