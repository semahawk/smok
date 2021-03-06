<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");
require_once("lib/titles.php");
require_once("lib/fightnav.php");
require_once("lib/villagenav.php");
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
      "dev" => "Wersja developerska,bool|1",
      "pokeball_chance" => "Chance on finding the pokeball (in %),int|5",
      "pokeball_walker" => "Is the pokeball global and in a random forest?,bool|1",
      "pokeball_location" => "If it is global/random then where is it now?,text|Nautileum",
      "forest_multiplier" => "Exp needed/atk/def/hp for forest creatures multiplier,float|2.0",
      "boss_multiplier" => "Boss' atk/def/hp multiplier,float|2.0",
    ),
    "prefs" => array (
      "Walka z bossem,title",
      "bosscurr" => "The boss' 'num' that the user will be fighting,int|-1",
      "bossname" => "Name of the boss user would be fighting,text|`LNo`vcn`Vy Po`vmi`Lot `)`0",
      "bossweapon" => "The boss' weapon,text|`i`jSe`)nn`7e Kos`)zma`jry`i`0",
      "bossdesc_before" => "The boss' text before beating him,text|`7Chcia³e¶ siê zakra¶ć do bestii, wzi±ć j± z zaskoczenia, lecz Nocny Pomiot odwróci³ siê w Twoim kierunku, sycz±c w¶ciekle i pokazuj±c dwa rzêdy ostrych, po¿ó³k³ych zêbisk. Jego cia³o pokrywa³a szarofioletowa skóra w nieco ciemniejsze, sinogranatowe cêtki, a szmaragdowe, ¶wiec±ce ¶lepia lustrowa³y Ciê gniewnie. ¶widruj±cy wzrok zdawa³ siê wgryzać w umys³. Stwór mia³ góra metr piêćdziesi±t wzrostu, zapewne w kapeluszu, którego akurat nie posiada³, ale by³ szpetny niczym najbrzydsza noc, nie mia³ ¿adnych w³osów, a z czo³a wyrasta³y mu dwa kilkucentymetrowe ró¿ki. Drapn±³ d³oni± uzbrojon± w d³ugie pazury o ziemiê i zaatakowa³ Twój umys³.`0",
      "bossdesc_after" => "The boss' text after beating the crap out of him,text|`7Walka wbrew pozorom nie by³a a¿ taka trudna. Koszmary rozwia³y siê w powietrzu tak prêdko jak przysz³y, a Nocny Pomiot wyda³ z siebie dziwny dŒwiêk bêd±cy prawdopodobnie po³±czeniem wrzasku z bulgotaniem. Pad³ na ziemiê, patrz±c na Ciebie umêczonym wzrokiem. Postanowi³e¶ skrócić mêki bestii i wbi³e¶ ostrze w gard³o poczwary. ¶wiec±ce ¶lepia przygas³y nagle, posadzka pokry³a siê jasnofioletow± ciecz± - krwi± stworzenia. Parê sekund póŒniej cia³o Pomiotu zaczê³o robić siê jakby coraz bardziej kruche i przezroczyste, a¿ w koñcu zamieni³o siê w popió³ nasi±kaj±cy krwi±.`0",
      "bosslocation" => "The boss' specific village in which it is to be seen,location|Deus Nocturnem",
      "has_the_pokeball" => "Whether the user has found the 'pokeball',bool|0",
    )
  );

  return $info;
}

function boss_install()
{
  module_addeventhook("forest", "return 0;");
  module_addhook("forest");
  module_addhook("charstats");
  module_addhook("battle-victory");

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
        if ($session['user']['seendragon'] == 0){
          if ($session['user']['location'] == get_module_pref("bosslocation")){
            if (get_module_pref("has_the_pokeball") == 1){
              addnav("Walcz");
              addnav("`@Walcz z bossem!`0", "runmodule.php?module=boss&op=enter");
            }
          }
        }
      }
      break;
    case "charstats":
      $bossname = get_module_pref("bossname");
      $bosslocation = get_module_pref("bosslocation");
      $has_the_pokeball = get_module_pref("has_the_pokeball");
      $pokeball_count = $session['user']['dragonkills'];
      if ($has_the_pokeball == 1){
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
            /* pokeball wêdrownik
             * sprawdzamy czy jeste¶my w tym mie¶cie co i ów pokeball */
            if ($session['user']['location'] == get_module_setting("pokeball_location")){
              $canshoot = true;
            }
          } else {
            /* pokeball nie jest wêdrownikiem
             * wiêc jest w tym samym mie¶cie co i boss */
            if ($session['user']['location'] == get_module_pref("bosslocation")){
              $canshoot = true;
            }
          }
        }
      }
      if ($canshoot){
        if (e_rand(0, 100) <= get_module_setting("pokeball_chance")){
          output("`n`e[FIXME] `GBRAWO! `EOdnajdujesz pokeballa!`n`n");
          set_module_pref("has_the_pokeball", 1);
          if (get_module_setting("pokeball_walker")){
            // <s>Hmm, powinno dzia³ać</s>
            //         WRONG!
            //         Nie dzia³a, lol
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
        "location"        => 1,
        "weapon"          => 1,
        "armor"           => 1,
        "weaponvalue"     => 1,
        "armorvalue"      => 1,
        "weapondmg"       => 1,
        "armordef"        => 1,
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
      //$session['user']['gold'] = getsetting("newplayerstartgold", 50);

      //$newtitle = get_dk_title($session['user']['dragonkills'], $session['user']['sex']);

      //$restartgold = $session['user']['gold'] +
        //getsetting("newplayerstartgold", 50) * $session['user']['dragonkills'];
      //$restartgems = 0;
      //if ($restartgold > getsetting("maxrestartgold", 300)) {
        //$restartgold = getsetting("maxrestartgold", 300);
        //$restartgems = ($session['user']['dragonkills'] -
            //(getsetting("maxrestartgold", 300) /
             //getsetting("newplayerstartgold", 50)) - 1);
        //if ($restartgems > getsetting("maxrestartgems", 10)) {
          //$restartgems = getsetting("maxrestartgems", 10);
        //}
      //}
      //$session['user']['gold'] = $restartgold;
      //$session['user']['gems'] += $restartgems;

      if ($flawless) {
        //$session['user']['gold'] += 150 * get_module_pref("boss_multiplier");
        $session['user']['gems'] += 1;
      }

      //$session['user']['maxhitpoints'] = 10 + $hpgain['dkpoints'] + $hpgain['extra'];
      $session['user']['hitpoints'] = $session['user']['maxhitpoints'];

      // Sanity check
      if ($session['user']['maxhitpoints'] < 1) {
        // Yes, this is a freaking hack.
        die("ACK!! Somehow this user would end up perma-dead.. Not allowing DK to proceed!  Notify admin and figure out why this would happen so that it can be fixed before DK can continue.");
        exit();
      }

      // Set the new title.
      //$newname = change_player_title($newtitle);
      $session['user']['title'] = "";
      //$session['user']['name'] = $newname;

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
      addnews("`#%s`# has earned `&%d`# reputation points for having slain the %s!",$regname,get_module_setting('rep_for_boss_kill', 'rep') * $session['user']['dragonkills'], get_module_pref('bossname'));
      //output("`n`n`^You are now known as `&%s`^!!", $session['user']['name']);
      output("`n`n`&Because you have slain the %s, you start with some extras.  You also keep additional permanent hitpoints you've earned.`n", get_module_pref('bossname'));
      $session['user']['charm'] += 5;
      output("`^You gain FIVE charm points for having defeated the boss!`n");
      debuglog("slew the dragon and starts with {$session['user']['gold']} gold and {$session['user']['gems']} gems");

      // Moved this hear to make some things easier.
      modulehook("dragonkill", array());
      invalidatedatacache("list.php-warsonline");
      /* user must find a new pokeball after beating a boss */
      set_module_pref("has_the_pokeball", 0);
      /* give him the reputation */
      set_module_pref('rep', get_module_pref('rep', 'rep') + get_module_setting('rep_for_boss_kill', 'rep') * $session['user']['dragonkills'], 'rep');
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
        addnews("%s `\$zostal".($session['user']['sex'] ? "a" : "")." pokonan".($session['user']['sex'] ? "a" : "y")." przez %s`\$!", $session['user']['name'], get_module_pref("bossname"));
        addnav("Do `\$Krainy Cienia`0", "shades.php");
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
 * Zapisuje w sesji bossa który b±dŒ jest w prefach, a je¶li nie, to nastêpnego 
 * co jest w kolejce.
 */
function boss_fetchboss()
{
  global $session;

  $name = "";
  $weapon = "";

  if (get_module_pref("bossname") == NULL){
    /* zgarniamy aktualnego bossa */
    $curr = get_module_pref("bosscurr");
    $sql = "SELECT * FROM " . db_prefix("bosses") . " WHERE bossid = '$curr' LIMIT 1;";
    $res = db_query($sql);
    $row = db_fetch_assoc($res);
    $name = $row['bossname'];
    $weapon = $row['bossweapon'];
  } else {
    /* wyci±gamy bossa z prefów */
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
 * Zapisuje w prefach nastepnego bossa.
 */
function boss_newboss()
{
  /* zgarniamy nastepnego w kolejce bossa */
  $nextnum = get_module_pref("bosscurr") + 1;
  $sql = "SELECT * FROM " . db_prefix("bosses") . " WHERE bossid >= '$nextnum' ORDER BY bossid ASC LIMIT 1;";
  $res = db_query($sql);
  $row = db_fetch_assoc($res);
  if (db_affected_rows() == 0){
    /* lecimy od pocz±tku z kolejk±
     * chocia¿, nigdy nie powinni¶my siê tutaj dostać.. */
    $sql = "SELECT * FROM " . db_prefix("bosses") . " ORDER BY bossid ASC LIMIT 1;";
    $res = db_query($sql);
    $row = db_fetch_assoc($res);
  }
  /* i zapisujemy w prefach */
  set_module_pref("bosscurr", $row['bossid']);
  set_module_pref("bossname", $row['bossname']);
  set_module_pref("bossweapon", $row['bossweapon']);
  set_module_pref("bossdesc_before", $row['bossdesc_before']);
  set_module_pref("bossdesc_after", $row['bossdesc_after']);
  set_module_pref("bosslocation", $row['bosslocation']);
}

?>
