<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");
require_once("lib/villagenav.php");
require_once("lib/titles.php");

function donator_getmoduleinfo()
{
  $info = array(
    "name" => "Status Donatora",
    "version" => "0.1.0",
    "author" => "`GCiuf",
    "category" => "Ciuf",
    "download" => "example.com",
    "settings" => array(
      "Status Donatora,title",
      "price" => "Koszt statusu donatora (w PR),int|5000"
    ),
    "prefs" => array(
      "Reputacja,title",
      "isdonator" => "Czy uzytkownik ma status donatora?,bool|0",
      "since" => "Kiedy otrzymal status donatora (UNIX),int|0",
      "homereturn" => "Czy wykorzystal juz dzisiaj teleport do miasta rodzinnego?,bool|0",
      "hidebar" => "Czy ma schowany zielony pasek?,bool|0"
    )
  );

  return $info;
}

function donator_install()
{
  module_addhook("newday");
  module_addhook("lodge");

  return true;
}

function donator_uninstall()
{
  return true;
}

function donator_dohook($hookname, $args)
{
  global $session;

  switch ($hookname){
    case "lodge":
      if (!get_module_pref('isdonator')){
        addnav("Status Donatora (`^" . get_module_setting('price') . " Pkt`0)", "runmodule.php?module=donator");
      }
      break;
    case "newday":
      if (get_module_pref('isdonator')){
        if (time() > ((int)get_module_pref('since') + 60 * 3 /*2592000*/ /* 60 * 60 * 24 * 30 */)){
          output("`n`EMinelo 30 dni, koniec donatorstwa!`n`n");
          set_module_pref('isdonator', 0);
          set_module_pref('since', 0);
          set_module_pref('homereturn', 0);
          set_module_pref('hidebar', 0);
          rm_title("`yD`jET`yonator", $session['user']['acctid']);
        }
      }
      break;
  }

  return $args;
}

function donator_runevent($type, $link)
{
  // NULL
}

function donator_run()
{
  global $session;

  $op = httpget('op');
  $here = "runmodule.php?module=donator";
  $price = get_module_setting('price');

  page_header("Status Donatora");

  if (get_module_pref('isdonator')){
    /* na wszelki wypadek */
    output("`EJuz posiadasz status donatora!");
  } else {
    $donationsleft = $session['user']['donation'] - $session['user']['donationspent'];

    if ($donationsleft >= $price){
      output("`gKupiles status donatora!`n`n");
      output("Teraz mozesz:`n- Robic to`n- Tamto`n-Sramto`n`n");
      output("Oraz, otrzymujesz tytul donatora!");
      set_module_pref('isdonator', 1);
      set_module_pref('since', time());
      add_title("`yD`jET`yonator", "Kupno statusu donatora", $session['user']['acctid']);
      $session['user']['donationspent'] += $price;
    } else {
      output("`ENie stac cie! niahniahniah");
    }
  }

  villagenav();
  page_footer();
}

?>
