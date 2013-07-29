<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");
require_once("lib/villagenav.php");

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
              output("$title`0, ");
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
  global $session;

  $here = "runmodule.php?module=titles";
  $op = httpget('op');

  page_header("Tytul");

  if ($op == ""){
    /* {{{ */
    if (get_module_pref('titles') === ""){
      output("`ENiestety, nie masz zadnych tytulow!");
    } else {
      output("`EDostepne tytuly:`n`n`n`n");
      $justname = str_replace($session['user']['ctitle'], "", $session['user']['name']);
      $titles = explode(':', get_module_pref('titles'));
      foreach ($titles as $title){
        output($title . " " . $justname);
        $encoded = base64_encode($title);
        addnav("", "$here&op=set&title=$encoded");
        rawoutput("<a href='$here&op=set&title=$encoded' class='button'>Ustaw</a><br><br>");
      }
      output("`n");
    }
    /* }}} */
  }
  else if ($op == "set"){
    /* {{{ */
    $title = httpget('title');
    $title = base64_decode($title);

    $justname = str_replace($session['user']['ctitle'], "", $session['user']['name']);

    db_query("UPDATE " . db_prefix("accounts") . " SET ctitle = '$title' WHERE acctid = '" . $session['user']['acctid'] . "' LIMIT 1");
    $session['user']['name'] = $title . " " . $justname;
    $session['user']['ctitle'] = $title;
    output("`@Tytul zmieniony na '$title`@'!");
    /* }}} */
  }

  villagenav();
  page_footer();
}

?>
