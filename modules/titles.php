<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");

function titles_getmoduleinfo()
{
  $info = array(
    "name" => "Tytuly",
    "version" => "0.1.0",
    "author" => "`GCiuf",
    "category" => "Ciuf",
    "download" => "example.com",
    "settings" => array (
      "Tytuly,title",
    ),
    "prefs" => array (
      "Tytuly,title",
      "titles" => "Zdobyte przez uzytkownika tytuly (oddzielone ':'),text|",
    )
  );

  return $info;
}

function titles_install()
{
  module_addhook("bioinfo");

  return true;
}

function titles_uninstall()
{
  return true;
}

function titles_dohook($hookname, $args)
{
  global $session;

  switch ($hookname){
    case "bioinfo":
      if (get_module_pref('titles', 'titles', $args['acctid']) !== ""){
        $titles = explode(':', get_module_pref('titles', 'titles', $args['acctid']));
        if (!empty($titles)){
          output("`^Tytuly: ");
          $i = 0;
          foreach ($titles as $title){
            $i++;
            if ($i < count($titles))
              output("$title, ");
            else
              output("$title");
          }
          output("`n");
        }
      }
      break;
  }

  return $args;
}

function titles_runevent($type, $link)
{
  // NULL
}

function titles_run()
{
  // NULL
}

?>
