<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");
require_once("lib/titles.php");
require_once("lib/fightnav.php");
require_once("lib/villagenav.php");
require_once("lib/titles.php");
require_once("lib/http.php");
require_once("lib/buffs.php");
require_once("lib/taunt.php");
require_once("lib/names.php");

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
      "badguy_name" => "Name of the boss user would be fighting,string",
      "badguy_weapon" => "The boss' weapon,string",
      "badguy_desc" => "The boss' text after beating him,string",
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

  $sql_create = "CREATE TABLE IF NOT EXISTS " . db_prefix("bosses") . "(\n" .
                  "bossid int(11) primary key auto_increment,\n" .
                  "bossname varchar(255) not null,\n" .
                  "bossweapon varchar(255) not null,\n" .
                  "bossdesc text not null\n" .
                ");\n";

  db_query($sql_drop);
  db_query($sql_create);

  $bosses = array(
    "[FIXME] Szkieletor" => array("[FIXME] Szkieletowate lapska",
      "`EOpis po zabiciu `GSZKIELETORA"),
    "[FIXME] Mumia" => array("[FIXME] Mumiowate lapska",
      "`EOpis po zabiciu `GMUMII"),
    "[FIXME] Wonsz" => array("[FIXME] Zemby",
      "`EOpis po zabiciu `GWENSZA")
  );

  /* TODO: to można by było wrzucić jako całość do jednego stringa i raz wykonać
   *       ale czemuś, nie wiedzieć czemu, mam syntax errory */
  foreach ($bosses as $name => $more){
    db_query("INSERT INTO `" . db_prefix("bosses") . "` (bossid, bossname, bossweapon, bossdesc) VALUES(NULL, '$name', '$more[0]', '$more[1]');\n");
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
      if ($session['user']['level'] >= 15 && $session['user']['seendragon'] == 0){
        addnav("Walcz");
        addnav("`@Walcz z bossem!`0", "runmodule.php?module=boss&op=enter");
      }
      break;
  }

  return $args;
}

function boss_runevent($type, $link)
{
  addnav("", "runmodule.php?module=boss&op=enter");
  redirect("runmodule.php?module=boss&op=enter");
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
    case "prologue1":
      output("`@Zwyciestwo!`n`n");
      $flawless = (int)(httpget('flawless'));
        if ($flawless) {
        output("`b`c`&~~ Niepodwazalne Zwyciestwo ~~`0`c`b`n`n");
      }

      output("`GBrawo, `Epokonales bossa");

      if ($flawless) {
        output("`n`nTekst o bonusie do niepowazalnego");
      }
      addnav("It is a new day","news.php");
      strip_all_buffs();
      $sql = "DESCRIBE " . db_prefix("accounts");
      $result = db_query($sql);

      reset($session['user']['dragonpoints']);
      $dkpoints = 0;
      while(list($key,$val) = each($session['user']['dragonpoints'])){
        if ($val == "hp") $dkpoints += 5;
      }

      restore_buff_fields();
      $hpgain = array(
          'total' => $session['user']['maxhitpoints'],
          'dkpoints' => $dkpoints,
          'extra' => $session['user']['maxhitpoints'] - $dkpoints -
              ($session['user']['level'] * 10),
          'base' => $dkpoints + ($session['user']['level'] * 10),
          );
      $hpgain = modulehook("hprecalc", $hpgain);
      calculate_buff_fields();

      $nochange = array(
        "acctid"          => 1,
        "name"            => 1,
        "sex"             => 1,
        "password"        => 1,
        "marriedto"       => 1,
        "title"           => 1,
        "race"            => 1,
        "specialty"       => 1,
        "login"           => 1,
        "dragonkills"     => 1,
        "locked"          => 1,
        "loggedin"        => 1,
        "superuser"       => 1,
        "gems"            => 1,
        "hashorse"        => 1,
        "gentime"         => 1,
        "gentimecount"    => 1,
        "lastip"          => 1,
        "uniqueid"        => 1,
        "dragonpoints"    => 1,
        "laston"          => 1,
        "prefs"           => 1,
        "lastmotd"        => 1,
        "emailaddress"    => 1,
        "emailvalidation" => 1,
        "gensize"         => 1,
        "bestdragonage"   => 1,
        "dragonage"       => 1,
        "donation"        => 1,
        "donationspent"   => 1,
        "donationconfig"  => 1,
        "bio"             => 1,
        "charm"           => 1,
        "banoverride"     => 1,
        "referer"         => 1,
        "refererawarded"  => 1,
        "ctitle"          => 1,
        "beta"            => 1,
        "clanid"          => 1,
        "clanrank"        => 1,
        "clanjoindate"    => 1);

      $nochange = modulehook("dk-preserve", $nochange);

      $session['user']['dragonage'] = $session['user']['age'];
      if ($session['user']['dragonage'] <  $session['user']['bestdragonage'] ||
          $session['user']['bestdragonage'] == 0) {
        $session['user']['bestdragonage'] = $session['user']['dragonage'];
      }
      for ($i = 0; $i < db_num_rows($result); $i++){
        $row = db_fetch_assoc($result);
        if (array_key_exists($row['Field'], $nochange) &&
            $nochange[$row['Field']]){
        } else {
          $session['user'][$row['Field']] = $row["Default"];
        }
      }
      $session['user']['gold'] = getsetting("newplayerstartgold", 50);

      $newtitle = get_dk_title($session['user']['dragonkills'], $session['user']['sex']);

      $restartgold = $session['user']['gold'] +
        getsetting("newplayerstartgold", 50) * $session['user']['dragonkills'];
      $restartgems = 0;
      if ($restartgold > getsetting("maxrestartgold", 300)) {
        $restartgold = getsetting("maxrestartgold", 300);
        $restartgems = ($session['user']['dragonkills'] -
            (getsetting("maxrestartgold", 300) /
             getsetting("newplayerstartgold", 50)) - 1);
        if ($restartgems > getsetting("maxrestartgems", 10)) {
          $restartgems = getsetting("maxrestartgems", 10);
        }
      }
      $session['user']['gold'] = $restartgold;
      $session['user']['gems'] += $restartgems;

      if ($flawless) {
        $session['user']['gold'] += 3 * getsetting("newplayerstartgold", 50);
        $session['user']['gems'] += 1;
      }

      $session['user']['maxhitpoints'] = 10 + $hpgain['dkpoints'] +
        $hpgain['extra'];
      $session['user']['hitpoints'] = $session['user']['maxhitpoints'];

      // Sanity check
      if ($session['user']['maxhitpoints'] < 1) {
        // Yes, this is a freaking hack.
        die("ACK!! Somehow this user would end up perma-dead.. Not allowing DK to proceed!  Notify admin and figure out why this would happen so that it can be fixed before DK can continue.");
        exit();
      }

      // Set the new title.
      $newname = change_player_title($newtitle);
      $session['user']['title'] = $newtitle;
      $session['user']['name'] = $newname;

      reset($session['user']['dragonpoints']);
      while (list($key,$val) = each($session['user']['dragonpoints'])){
        if ($val == "at"){
          $session['user']['attack']++;
        }
        if ($val == "de"){
          $session['user']['defense']++;
        }
      }
      $session['user']['laston'] = date("Y-m-d H:i:s", strtotime("-1 day"));
      $session['user']['slaydragon'] = 1;

      output("`n`n%s", get_module_pref("badguy_desc"));

      // allow explanative text as well.
      modulehook("dragonkilltext");

      $regname = get_player_basename();
      addnews("`#%s`# has earned the title `&%s`# for having slain the `@Green Dragon`& `^%s`# times!",$regname,$session['user']['title'],$session['user']['dragonkills']);
      output("`n`n`^You are now known as `&%s`^!!",$session['user']['name']);
      output("`n`n`&Because you have slain the dragon %s times, you start with some extras.  You also keep additional permanent hitpoints you've earned.`n",$session['user']['dragonkills']);
      $session['user']['charm'] += 5;
      output("`^You gain FIVE charm points for having defeated the dragon!`n");
      debuglog("slew the dragon and starts with {$session['user']['gold']} gold and {$session['user']['gems']} gems");

      // Moved this hear to make some things easier.
      modulehook("dragonkill", array());
      invalidatedatacache("list.php-warsonline");
      break;
    case "enter":
      /* zgarniamy losowego bossa */
      $sql = "SELECT * FROM " . db_prefix("bosses") . " ORDER BY RAND() LIMIT 1;";
      $res = db_query($sql);
      $row = db_fetch_assoc($res);
      /* zapisać go w ustawieniach */
      set_module_pref("badguy_name", $row['bossname']);
      set_module_pref("badguy_weapon", $row['bossweapon']);
      set_module_pref("badguy_desc", $row['bossdesc']);
      output("`c`GWYCZESANY `Etekst o tym jak to chcesz dowalic `GBOSSOWI `Eale sie cykasz i nie jestes pewien`c", get_module_pref("badguy_name"), get_module_pref("badguy_weapon"));
      addnav("Zmierz sie z bossem", "$here&op=fight");
      addnav("Bierz tylek w troki", "$here&op=flee");
      break;
    case "fight":
      $badguy = array(
        "creaturename" => get_module_pref("badguy_name"),
        "creaturelevel" => 18,
        "creatureweapon" => get_module_pref("badguy_weapon"),
        "creatureattack" => 45,
        "creaturedefense" => 25,
        "creaturehealth" => 300,
        "diddamage" => 0
      );
      $points = 0;
      restore_buff_fields();
      reset($session['user']['dragonpoints']);
      while(list($key, $val) = each($session['user']['dragonpoints'])){
        if ($val == "at" || $val == "de") $points++;
      }
      $points += (int)(($session['user']['maxhitpoints'] - 150) / 5);
      $points = round($points * .75, 0);
      $atkflux = e_rand(0, $points);
      $defflux = e_rand(0, $points - $atkflux);
      $hpflux = ($points - ($atkflux + $defflux)) * 5;
      debug("DEBUG: $points modification points total.`0`n");
      debug("DEBUG: +$atkflux allocated to attack.`n");
      debug("DEBUG: +$defflux allocated to defense.`n");
      debug("DEBUG: +". ($hpflux / 5) . "*5 to hitpoints.`0`n");
      calculate_buff_fields();
      $badguy['creatureattack'] += $atkflux;
      $badguy['creaturedefense'] += $defflux;
      $badguy['creaturehealth'] += $hpflux;
      $session['user']['badguy'] = createstring($badguy);

      require_once("battle.php");

      if ($victory){
        output("Brawo!");
        $session['user']['dragonkills']++;
        addnav("Kontynuuj", "$here&op=prologue1&flawless=$flawless");
      } elseif ($defeat){
        output("Niestety, %s cie pokonal", get_module_pref("badguy_name"));
        villagenav();
      } else {
        fightnav(true, false, "$here");
      }
      break;
    case "flee":
      output("`c`EStwierdzasz, ze jestes za `GMIEKKI `Ena bossa, ale jeszcze tutaj wrocisz, po nowym dniu`c");
      villagenav();
      $session['user']['seendragon'] = 1;
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
