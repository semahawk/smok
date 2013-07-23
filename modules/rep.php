<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");

function rep_getmoduleinfo()
{
  $info = array(
    "name" => "Reputacja",
    "version" => "0.1.0",
    "author" => "`GCiuf",
    "category" => "Ciuf",
    "download" => "example.com",
    "settings" => array (
      "Reputacja,title",
      "rep_for_boss_kill" => "Ilosc repy za ubicie bossa,int|5",
    ),
    "prefs" => array (
      "Reputacja,title",
      "rep" => "Ilosc reputacji uzytkownika,int|0",
    )
  );

  return $info;
}

function rep_install()
{
  module_addhook("charstats");

  return true;
}

function rep_uninstall()
{
  return true;
}

function rep_dohook($hookname, $args)
{
  global $session;

  switch ($hookname){
    case "charstats":
      addcharstat("Reputacja");
      addcharstat("Twoja", get_module_pref('rep'));
      $city = get_module_pref('homecity', 'cities');
      $sql = "select mf.value as city, sum(ms.value) as sum from module_userprefs as mf inner join module_userprefs as ms on (mf.userid = ms.userid and ms.setting = 'rep' and ms.value <> 0) where mf.setting = 'homecity' and mf.value = '$city' group by city order by sum desc";
      $res = db_query($sql);
      $row = db_fetch_assoc($res);
      addcharstat("Miasta rodzinnego", $row['sum']);
      break;
  }

  return $args;
}

function rep_runevent($type, $link)
{
  // NULL
}

function rep_run()
{
  // NULL
}

?>
