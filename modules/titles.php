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
    "prefs" => array (
      "Tytuly,title",
      "titles" => "Tytuly zdobyte przez uzytkownika,text|",
    )
  );

  return $info;
}

function titles_install()
{
  module_addhook("bioinfo");
  module_addhook("bioend");

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
      $titles = unserialize(get_module_pref('titles', 'titles', $args['acctid']));
      if (!empty($titles)){
        output("`^Tytuly: `0");
        $i = 0;
        foreach ($titles as $title => $reason){
          $i++;
          $enctitle = base64_encode($title);
          $encreason = base64_encode($reason);
          if ($session['user']['superuser'] & SU_MEGAUSER){
            addnav("", "runmodule.php?module=titles&op=rm&id={$args['acctid']}&t=$enctitle");
            addnav("", "runmodule.php?module=titles&op=edit&id={$args['acctid']}&t=$enctitle&r=$encreason");
            rawoutput("<span class='colMdGrey'>[</span><a href='runmodule.php?module=titles&op=rm&id={$args['acctid']}&t=$enctitle' onClick='return confirm(\"Na pewno chcesz zabrac ten tytul (\\\"$title\\\")?\");'><span class='colLtRed'>x</span></a><span class='colMdGrey'>]</span>");
            rawoutput("<span class='colMdGrey'>[</span><a href='runmodule.php?module=titles&op=edit&id={$args['acctid']}&t=$enctitle&r=$encreason'><span class='colDkGreen'>e</span></a><span class='colMdGrey'>]</span>");
          }
          if ($i < count($titles)){
            rawoutput("<a title='$reason'>");
            output_notl("$title`0, ");
            rawoutput("</a>");
          } else {
            rawoutput("<a title='$reason'>");
            output_notl("$title");
            rawoutput("</a>");
          }
        }
        output("`n");
      }
      break;
    case "bioend":
      if ($session['user']['superuser'] & SU_MEGAUSER){
        addnav("Ciuf Titles");
        addnav("Dodaj tytul", "runmodule.php?module=titles&op=add&id={$args['acctid']}");
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
    $titles = unserialize(get_module_pref('titles', 'titles', $session['user']['acctid']));
    if (empty($titles) || $titles == false){
      output("`ENiestety, nie masz zadnych tytulow!");
    } else {
      output("`EDostepne tytuly:`0`n`n`n`n");
      $justname = str_replace($session['user']['ctitle'], "", $session['user']['name']);
      foreach ($titles as $title => $reason){
        output("`0" . $title . "`0 " . $justname);
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
    $title = base64_decode($title) . "`&";

    $justname = str_replace($session['user']['ctitle'], "", $session['user']['name']);

    db_query("UPDATE " . db_prefix("accounts") . " SET ctitle = '$title' WHERE acctid = '" . $session['user']['acctid'] . "' LIMIT 1");
    $session['user']['name'] = $title . $justname;
    $session['user']['ctitle'] = $title;
    output("`@Tytul zmieniony na '$title`@'!");
    addnav("Zmien tytul", "$here");
    /* }}} */
  }
  else if ($op == "add"){
    /* {{{ */
    if ($session['user']['superuser'] & SU_MEGAUSER){
      $id = httpget('id');

      output("`EDodajesz tytul:`n`n");
      addnav("", "$here&op=addfin");
      rawoutput("
        <form method='post' action='$here&op=addfin'>
          <input type='hidden' name='id' value='$id'>
          <input type='text' name='title' placeholder='Nazwa'>
          <input type='text' name='reason' placeholder='Powod'>
          <input type='submit' name='submit' value='Dodaj'>
        </form>");
    } else {
      // TODO: smierc hakierowi tudziez email do adminow
    }
    /* }}} */
  }
  else if ($op == "addfin"){
    /* {{{ */
    if ($session['user']['superuser'] & SU_MEGAUSER){
      $title = httppost('title');
      $reason = httppost('reason');
      $id = httppost('id');

      $titles = unserialize(get_module_pref('titles', 'titles', $id));

      if ($titles === false){
        $titles = array();
      }

      $titles[$title] = $reason;

      set_module_pref('titles', serialize($titles), 'titles', $id);

      output("Najprawdopodobniej sie udalo");
    } else {
      // TODO: smierc hakierowi tudziez email do adminow
    }
    /* }}} */
  }
  else if ($op == "rm"){
    /* {{{ */
    if ($session['user']['superuser'] & SU_MEGAUSER){
      $id = httpget('id');
      $title = base64_decode(httpget('t'));

      $titles = unserialize(get_module_pref('titles', 'titles', $id));

      if ($titles === false){
        output("`yLOL");
      } else {
        unset($titles[$title]);
        set_module_pref('titles', serialize($titles), 'titles', $id);
        output("Powinno bylo zadzialac");
      }
    } else {
      // TODO: smierc hakierowi tudziez email do adminow
    }
    /* }}} */
  }
  else if ($op == "edit"){
    /* {{{ */
    if ($session['user']['superuser'] & SU_MEGAUSER){
      $id = httpget('id');
      $title = base64_decode(httpget('t'));
      $reason = base64_decode(httpget('r'));

      output("`EZmieniasz tytul:`n`n");
      addnav("", "$here&op=editfin&id=$id");
      rawoutput("
        <form method='post' action='$here&op=editfin&id=$id'>
          <input type='hidden' name='title' value='".base64_encode($title)."'>
          <input type='hidden' name='reason' value='".base64_encode($reason)."'>
          <input type='text' name='newtitle' value='$title'>
          <input type='text' name='newreason' value='$reason'>
          <input type='submit' name='submit' value='Edytuj'>
        </form>");
    } else {
      // TODO: smierc hakierowi tudziez email do adminow
    }
    /* }}} */
  }
  else if ($op == "editfin"){
    /* {{{ */
    if ($session['user']['superuser'] & SU_MEGAUSER){
      $id = httpget('id');
      $title = base64_decode(httppost('title'));
      $reason = base64_decode(httppost('reason'));
      $newtitle = httppost('newtitle');
      $newreason = httppost('newreason');

      $titles = unserialize(get_module_pref('titles', 'titles', $id));

      if ($titles === false){
        output("`yLOL");
      } else {
        $changetitle = 1;
        $changereason = 1;

        /* PHP's truth table SUCKS */

        if (($newtitle === "" || $newtitle === null || $newtitle === false)
         || ($title === $newtitle)){
          $changetitle = 0;
        }

        if (($newreason === "" || $newreason === null || $newreason === false)
         || ($reason === $newreason)){
          $changereason = 0;
        }

        if ($changetitle){
          /* XXX change both title and reason */
          if ($changereason){
            $titles[$newtitle] = $newreason;
            unset($titles[$title]);
            output("`yZmieniono tytul i powod");
          }
          /* XXX change only title */
          else {
            $titles[$newtitle] = $titles[$title];
            unset($titles[$title]);
            output("`yZmieniono tytul");
          }
        }
        else {
          /* XXX change only reason */
          if ($changereason){
            $titles[$title] = $newreason;
            output("`yZmieniono powod");
          }
          /* XXX don't change anything */
          else {
            output("`yBez zmian");
          }
        }

        set_module_pref('titles', serialize($titles), 'titles', $id);
      }
    } else {
      // TODO: smierc hakierowi tudziez email do adminow
    }
    /* }}} */
  }

  villagenav();
  page_footer();
}

?>
