<?php

require_once("lib/villagenav.php");
require_once("lib/commentary.php");
require_once("lib/fightnav.php");

function final_getmoduleinfo()
{
  $info = array(
    "name" => "Finalna walka ze Smokiem",
    "version" => "0.1.0",
    "author" => "`GCiuf",
    "category" => "Village",
    "download" => "example.com",
    "settings" => array (
      "Legendarny Smok,title",
      "dev" => "Wersja developerska,bool|true",
      "bk_needed" => "Ilosc zabitych bossow potrzebnych do wywolania walki,int|500",
    ),
    "prefs" => array (
      "Final fight - user prefs,title",
    )
  );

  return $info;
}

function final_install()
{
  return true;
}

function final_uninstall()
{
  return true;
}

function final_dohook($hookname, $args)
{
  return $args;
}

function final_runevent($type, $link)
{
  // NULL
}

function final_run()
{
  global $session;

  $op = httpget('op');
  $here = "runmodule.php?module=final";
  $dev = get_module_setting("dev");

  page_header("Legendarny Smok w natarciu!");

  switch ($op){
    case "fight":
      $badguy = array(
        "creaturename" => translate_inline("`@The Final Green Dragon`0"),
        "creaturelevel" => 180,
        "creatureweapon" => translate_inline("The Greatest Flaming Maw"),
        "creatureattack" => 450,
        "creaturedefense" => 250,
        "creaturehealth" => 3000,
        "diddamage" => 0, 'type' => 'dragon');

      //$badguy = modulehook("buffdragon", $badguy);
      $session['user']['badguy']=createstring($badguy);

      require_once("battle.php");

      if ($victory){
        output("BRAWO");
        addnav("[FIXME] Do poczekalni", "$here&op=after&res=win");
      } else {
        if ($defeat){
          output("Cienki jestes, hard-reset dla ciebie");
          addnav("[FIXME] Do poczekalni", "$here&op=after&res=lose");
        } else {
          fightnav(true, true);
        }
      }
      break;
    case "after":
      if (httpget('res') == "win"){
        output("Brawo! Udalo ci sie!`n");
      } else if (httpget('res') == "lose"){
        output("Hehe, frajer`n");
      }
      commentdisplay("`n`EZagubione duszyczki, zastanawiajace sie kiedy i skad przybyl `GSMOK`E, costam costam ciemna strona mocy:`n", "poczekalnia", "Poczekalnia", 25, "poczekalnia");
      checkday();
      addnav("Wyjscie");
      addnav("Wyloguj","login.php?op=logout");
      if ($session['user']['superuser'] & SU_INFINITE_DAYS){
        addnav("Superuser");
        addnav("/?Nowy dzien","newday.php");
      }
      break;
    default:
      addnav("Bron sie!", "$here&op=fight");
      output("`c`GEPICKI `etekst o tym jak smok cie zaatakowal i nie masz zadnych szans ucieczki i musisz walczyc.`c");
      break;
  }

  if ($dev){
    addnav("[DEV]");
    addnav("[DEV] The fight", "$here");
    addnav("[DEV] Afterall", "$here&op=after");
    addnav("[DEV] Refresh", "$here");
    villagenav();
  }
  page_footer();
}

?>
