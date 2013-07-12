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
require_once("lib/experience.php");

function boss_getmoduleinfo()
{
  $info = array(
    "name" => "Walka z Bossem",
    "version" => "0.1.0",
    "author" => "`GCiuf",
    "category" => "Ciuf",
    "download" => "example.com",
    "settings" => array (
      "Walka z Bossem,title",
      "dev" => "Wersja developerska,bool|true",
      "pokeball_chance" => "Chance on finding the pokeball (in %),int|5",
      "pokeball_walker" => "Is the pokeball global and in a random forest?,bool|true",
      "pokeball_location" => "If it is global/random then where is it now?,string",
      "forest_multiplier" => "Exp needed/atk/def/hp for forest creatures multiplier,float|2.0",
      "boss_multiplier" => "Boss' atk/def/hp multiplier,float|2.0",
    ),
    "prefs" => array (
      "Walka z bossem,title",
      "bossnum" => "Order in which it appears,int|",
      "bossname" => "Name of the boss user would be fighting,string",
      "bossweapon" => "The boss' weapon,string",
      "bossdesc_before" => "The boss' text before beating him,string",
      "bossdesc_after" => "The boss' text after beating the crap out of him,string",
      "bosslocation" => "The boss' specific village in which it is to be seen,string",
      "has_the_pokeball" => "Whether the user has found the 'pokeball',bool|false",
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
                  "bossnum int(11) not null,\n" .
                  "bossname varchar(255) not null,\n" .
                  "bossweapon varchar(255) not null,\n" .
                  "bossdesc_before text not null,\n" .
                  "bossdesc_after text not null,\n" .
                  "bosslocation varchar(255) not null\n" .
                ");\n";

  db_query($sql_drop);
  db_query($sql_create);

  $bosses = array(
    "[FIXME] Szkieletor" => array("[FIXME] Szkieletowate lapska",
      "`EOpis przed zabiciem `GSZKIELETORA",
      "`EOpis po zabiciu `GSZKIELETORA",
      "Deus Nocturnem"),
    "[FIXME] Ciemny Elf" => array("[FIXME] Ciemny luk",
      "`EOpis przed zabiciem `GCIEMNEGO ELFA",
      "`EOpis po zabiciu `GCIEMNEGO ELFA",
      "Glorfindal"),
    "[FIXME] Kraken" => array("[FIXME] Macki",
      "`EOpis przed zabiciem `GKRAKENA",
      "`EOpis po zabiciu `GKRAKENA",
      "Nautileum")
  );

  /* TODO: to można by było wrzucić jako całość do jednego stringa i raz wykonać
   *       ale czemuś, nie wiedzieć czemu, mam syntax errory */
  $num = 0;
  foreach ($bosses as $name => $more){
    db_query("INSERT INTO `" . db_prefix("bosses") . "` (bossid, bossnum, bossname, bossweapon, bossdesc_before, bossdesc_after, bosslocation) VALUES(NULL, '$num', '$name', '$more[0]', '$more[1]', '$more[2]', '$more[3]');\n");
    $num++;
  }

  module_addeventhook("forest", "return 0;");
  module_addhook("forest");
  module_addhook("charstats");
  module_addhook("battle-victory");

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
      if ((($session['user']['level'] >= 15) &&
           ($session['user']['seendragon'] == 0) &&
           ($session['user']['location'] == get_module_pref("bosslocation") || $session['user']['dragonkills'] == 0) &&
           (get_module_pref("has_the_pokeball") == 1)) ||
            get_module_setting("dev")){
        addnav("Walcz");
        addnav("`@Walcz z bossem!`0", "runmodule.php?module=boss&op=enter");
      }
      break;
    case "charstats":
      $bossname = get_module_pref("bossname");
      $bosslocation = get_module_pref("bosslocation");
      $has_the_pokeball = get_module_pref("has_the_pokeball");
      $pokeball_count = $session['user']['dragonkills'];
      if ($has_the_pokeball){
        $pokeball_count++;
      }
      addcharstat("Informacje o bossie");
      addcharstat("Nazwa", $bossname == "" ? "Nieznana" : $bossname);
      addcharstat("Lokalizacja", $bosslocation == "" ? "Nieznana" : $bosslocation);
      addcharstat("Ilosc krysztalow", $pokeball_count);
      addcharstat("[FIXME] Pokeball znaleziony?", $has_the_pokeball == 1 ? "Tak" : "Nie");
      if (get_module_setting("dev")){
        if (get_module_setting("pokeball_walker")){
          addcharstat("[FIXME] Lokacja pokeballa", get_module_setting("pokeball_location"));
        } else {
          addcharstat("[FIXME] Lokacja pokeballa", get_module_pref("bosslocation"));
        }
        addcharstat("Potrzebny EXP", exp_for_next_level($session['user']['level'], $session['user']['dragonkills'], false));
        addcharstat("Potrzebny EXP z mnoznikiem", exp_for_next_level($session['user']['level'], $session['user']['dragonkills']));
      }
      break;
    case "battle-victory":
      $canshoot = false;
      if (get_module_pref("has_the_pokeball") == 0){
        if ($session['user']['level'] >= 15){
          if (get_module_setting("pokeball_walker")){
            /* pokeball wędrownik
             * sprawdzamy czy jesteśmy w tym mieście co i ów pokeball */
            if ($session['user']['location'] == get_module_setting("pokeball_location")){
              $canshoot = true;
            }
          } else {
            /* pokeball nie jest wędrownikiem
             * więc jest w tym samym mieście co i boss */
            if ($session['user']['location'] == get_module_pref("bosslocation")){
              $canshoot = true;
            }
          }
        }
      }
      if ($canshoot){
        if (e_rand(0, 100) <= get_module_setting("pokeball_chance")){
          output("`n`e[FIXME] `GBRAWO! `EOdnajdujesz pokeballa!`n`n");
          set_module_pref("has_the_pokeball", true);
          if (get_module_setting("pokeball_walker")){
            // Hmm, powinno działać
            $current_location = get_module_setting("pokeball_location");
            $cities = array(
              "Dendralium",
              "Deus Nocturnem",
              "Glorfindal",
              "Glukmoore",
              "Kumrum",
              "Nautileum",
              "Qexelcrag",
              "Romar"
            );
            $next_location = $cities[e_rand(0, count($cities) - 1)];
            addnews("%s`E znalazl pokeballa w `G%s`0", get_player_basename(), $current_location);
            set_module_setting("pokeball_location", $next_location);
          }
        }
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
  $bname = get_module_pref("bossname");
  $bweap = get_module_pref("bossweapon");

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
        "attack"          => 1,
        "defense"         => 1,
        "gold"            => 1,
        "goldinbank"      => 1,
        //"experience"      => 1, /* question mark */
        "specialty"       => 1,
        "hitpoints"       => 1,
        "maxhitpoints"    => 1,
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

      output("`n`n%s", get_module_pref("bossdesc_after"));

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
      /* user must find a new pokeball after beating a boss */
      set_module_pref("has_the_pokeball", false);
      /* set the new boss */
      boss_newboss();
      break;
    case "enter":
      boss_fetchboss();
      output("%s`n", get_module_pref("bossdesc_before"));
      addnav("Zmierz sie z bossem", "$here&op=fight");
      addnav("Bierz tylek w troki", "$here&op=flee");
      break;
    case "fight":
      require_once("battle.php");

      if ($victory){
        output("Brawo!");
        $session['user']['dragonkills']++;
        addnav("Kontynuuj", "$here&op=prologue1&flawless=$flawless");
      } elseif ($defeat){
        output("Niestety, %s cie pokonal", get_module_pref("bossname"));
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

/*
 * Zapisuje w sesji bossa który bądź jest w prefach, a jeśli nie, to losuje go.
 */
function boss_fetchboss()
{
  global $session;

  $name = "";
  $weapon = "";

  if (get_module_pref("bossname") == NULL){
    /* zgarniamy losowego bossa */
    $sql = "SELECT * FROM " . db_prefix("bosses") . " ORDER BY RAND() LIMIT 1;";
    $res = db_query($sql);
    $row = db_fetch_assoc($res);
    $name = $row['bossname'];
    $weapon = $row['bossweapon'];
  } else {
    /* wyciągamy bossa z prefów */
    $name = get_module_pref("bossname");
    $weapon = get_module_pref("bossweapon");
  }

  $badguy = array(
    "creaturename" => $name,
    "creaturelevel" => 18,
    "creatureweapon" => $weapon,
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
  /* also, multiply it's stats by the boss' multpiler */
  $multiplier = $session['user']['dragonkills'] == 0 ? 1 : ($session['user']['dragonkills'] * get_module_setting("boss_multiplier", "boss"));
  if (get_module_setting("dev", "boss")){
    output("`n`Ebefore: attack `G%d`E, def `G%d`E hp `G%d", $badguy['creatureattack'], $badguy['creaturedefense'], $badguy['creaturehealth']);
  }
  $badguy['creatureattack'] *= $multiplier;
  $badguy['creaturedefense'] *= $multiplier;
  $badguy['creaturehealth'] *= $multiplier;
  if (get_module_setting("dev", "boss")){
    output("`n`Eafter: attack `G%d`E, def `G%d`E hp `G%d`n`n", $badguy['creatureattack'], $badguy['creaturedefense'], $badguy['creaturehealth']);
  }
  $session['user']['badguy'] = createstring($badguy);
}

/*
 * Zapisuje w prefach losowego bossa.
 */
function boss_newboss()
{
  /* zgarniamy losowego bossa */
  $sql = "SELECT * FROM " . db_prefix("bosses") . " ORDER BY RAND() LIMIT 1;";
  $res = db_query($sql);
  $row = db_fetch_assoc($res);
  /* i zapisujemy w prefach */
  set_module_pref("bossname", $row['bossname']);
  set_module_pref("bossweapon", $row['bossweapon']);
  set_module_pref("bossdesc_before", $row['bossdesc_before']);
  set_module_pref("bossdesc_after", $row['bossdesc_after']);
  set_module_pref("bosslocation", $row['bosslocation']);
}

?>
