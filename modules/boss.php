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
  /* dla pewności */
  /* przy reinstallowaniu za każdym razem dodawał rekordów, których było w końcu
   * po parę zestawów */
  $sql_drop = "DROP TABLE IF EXISTS " . db_prefix("bosses");

  $sql_create = "CREATE TABLE IF NOT EXISTS " . db_prefix("bosses") . "(" .
                  "bossid int(11) primary key auto_increment, " .
                  "bossname varchar(255) not null, " .
                  "bossweapon varchar(255) not null " .
                ");\n";

  db_query($sql_drop);
  db_query($sql_create);

  $bosses = array(
    "[FIXME] Szkieletor" => "[FIXME] Szkieletowate lapska",
    "[FIXME] Mumia" => "[FIXME] Mumiowate lapska",
    "[FIXME] Wonsz" => "[FIXME] Zemby"
  );

  /* TODO: to można by było wrzucić jako całość do jednego stringa i raz wykonać
   *       ale czemuś, nie wiedzieć czemu, mam syntax errory */
  foreach ($bosses as $name => $weapon){
    db_query("INSERT INTO `" . db_prefix("bosses") . "` (bossid, bossname, bossweapon) VALUES(NULL, '$name', '$weapon');\n");
  }

  module_addeventhook("forest", "return 100;");
  module_addhook("forest");

  return true;
}

function boss_uninstall()
{
  $sql = "DROP TABLE IF EXISTS " . db_prefix("bosses") . ";";
  db_query($sql);

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
  $bweap = get_module_pref("badguy_weapon");

  page_header("[FIXME] Boss!");

  switch ($op){
    case "enter":
      /* zgarniamy losowego bossa */
      $sql = "SELECT * FROM " . db_prefix("bosses") . " ORDER BY RAND() LIMIT 1;";
      $res = db_query($sql);
      $row = db_fetch_assoc($res);
      /* zapisać go w ustawieniach */
      set_module_pref("badguy_name", $row['bossname']);
      set_module_pref("badguy_weapon", $row['bossweapon']);
      output("`c`GWYCZESANY `Etekst o tym jak to chcesz dowalic `GBOSSOWI `Eale sie cykasz i nie jestes pewien`c", get_module_pref("badguy_name"), get_module_pref("badguy_weapon"));
      addnav("Zmierz sie z bossem", "$here&op=fight");
      addnav("Bierz tylek w troki", "$here&op=flee");
      break;
    case "fight":
      output("`c`ETutaj `GEPICKA `Ewalka z `G$bname`E, ktory ma `G$bweap`0`c");
      /* DEV */
      villagenav();
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
