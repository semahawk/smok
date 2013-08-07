<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");
require_once("lib/villagenav.php");
require_once("lib/commentary.php");
require_once("lib/systemmail.php");

function eq_getmoduleinfo()
{
  $info = array(
    "name" => "Ekwipunek",
    "version" => "0.1.0",
    "author" => "`GCiuf",
    "category" => "Ciuf",
    "download" => "example.com",
    "settings" => array (
      "Ekwipunek,title",
    ),
    "prefs" => array (
      "Ekwipunek,title",
    )
  );

  return $info;
}

function eq_install()
{
  $create_eqitems =
    "CREATE TABLE IF NOT EXISTS " . db_prefix("eqitems") . " (" .
      /* id */
      "id integer primary key auto_increment," .
      /* nazwa */
      "name text not null," .
      /* kategoria */
      "cat int(11) not null," .
      /* wpływ na atak */
      "atkimpact int(11) not null," .
      /* wpływ na obronę */
      "defimpact int(11) not null," .
      /* wpływ na max. ilość HP */
      "hpimpact int(11) not null," .
      /* wpływ na ilość leśnych walk */
      "ffimpact int(11) not null," .
      /* wpływ na ilość podróży */
      "timpact int(11) not null," .
      /* czy występuje w sklepie? */
      "inshop bool not null," .
      /* cena kupna */
      "buyprice int(11) not null," .
      /* cena sprzedaży */
      "sellprice int(11) not null," .
      /* czy dropi w lesie? */
      "droppable bool not null," .
      /* jeśli dropi i są spełnione n/w warunki - jaka szansa na wydropienie */
      "dropchance float not null," .
      /* jeśli dropi - min. liczba DK (czy też BK) */
      "dropmindk int(11) not null," .
      /* jeśli dropi - min. liczba repy */
      "dropminrep int(11) not null," .
      /* jeśli dropi - wymagana rasa */
      "droprace varchar(255) not null," .
      /* jeśli dropi - wymagana profesja */
      "dropprof varchar(255) not null," .
      /* na co wpływa ulepszenie */
      "implvlimpact int(11) not null" .
    ");";

  $create_eqstones =
    "CREATE TABLE IF NOT EXISTS " . db_prefix("eqstones") . " (" .
      /* id */
      "id integer primary key auto_increment," .
      /* nazwa */
      "name text not null," .
      /* o ile zwiększa poziom ulepszenia */
      "implvlinc int(11) not null," .
      /* max. poziom ulepszenia (tj. powyżej którego kamienia nie będzie można użyć) */
      "maximplvl int(11) not null," .
      /* szansa (w %) na wydropienie w lesie */
      "dropchance float not null," .
      /* szansa (w %) na powodzenie ulepszenia */
      "impchance int(3) not null," .
      /* szansa (w %) na spalenie itemku przy ulepszaniu */
      "burnchance int(3) not null" .
    ");";

  $create_accounts_eqitems =
    "CREATE TABLE IF NOT EXISTS " . db_prefix("accounts_eqitems") . " (" .
      "id integer primary key auto_increment," .
      "acctid int(11) not null," .
      "itemid integer not null," .
      // Was causing problems..
      //"foreign key(acctid) references " . db_prefix("accounts") . "(acctid)," .
      //"foreign key(itemid) references " . db_prefix("eqitems") . "(id)," .
      /* poziom ulepszenia */
      "implvl int(11) not null," .
      "equipped bool not null" .
    ");";

  $create_accounts_eqstones =
    "CREATE TABLE IF NOT EXISTS " . db_prefix("accounts_eqstones") . " (" .
      "id integer primary key auto_increment," .
      "acctid int(11) not null," .
      "stoneid integer not null," .
      "onsale bool not null," .
      "price int(11) not null" .
      // Was causing problems..
      //"foreign key(acctid) references " . db_prefix("accounts") . "(acctid)," .
      //"foreign key(stoneid) references " . db_prefix("eqstones") . "(id)" .
    ");";

  /* create the tables */
  db_query($create_eqitems);
  db_query($create_eqstones);
  db_query($create_accounts_eqitems);
  db_query($create_accounts_eqstones);

  module_addhook("charstats");
  module_addhook("battle-victory");
  module_addhook("village");

  return true;
}

function eq_uninstall()
{
  return true;
}

function eq_dohook($hookname, $args)
{
  global $session;

  switch ($hookname){
    case "charstats":
      /* {{{ */
      $head      = "-";
      $shoulders = "-";
      $bracelet  = "-";
      $armor     = "-";
      $ring      = "-";
      $weapon    = "-";
      $belt      = "-";
      $pants     = "-";
      $shoes     = "-";

      $sql = "SELECT * FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "' AND a.equipped = 1 GROUP BY e.cat";
      $res = db_query($sql);
      while ($item = db_fetch_assoc($res)){
        addnav("", "runmodule.php?module=eq&op=unequip&id=$item[id]");
        switch ($item['cat']){
          case EQ_HEAD:
            $head = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_SHOULDERS:
            $shoulders = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_BRACELET:
            $bracelet = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_ARMOR:
            $armor = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_RING:
            $ring = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_WEAPON:
            $weapon = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_BELT:
            $belt = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_PANTS:
            $pants = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
          case EQ_SHOES:
            $shoes = $item['name'] . " <a href='#' class='eqitem' rel='$item[id]'>[Zdejmij]</a>";
            break;
        }
      }

      addcharstat('EQ <a href="eqshow.php" target="_blank" onClick=\'window.open("eqshow.php", "eqshowphp", "scrollbars=yes, resizable=yes, width=800, height=600").focus(); return false;\'>(otworz)</a>');
      addcharstat("Nakrycie glowy", "<span id='eqhead'>$head</span>");
      addcharstat("Naramienniki", "<span id='eqshoulders'>$shoulders</span>");
      addcharstat("Naszyjnik", "<span id='eqbracelet'>$bracelet</span>");
      addcharstat("Pancerz", "<span id='eqarmor'>$armor</span>");
      addcharstat("Pierscien", "<span id='eqring'>$ring</span>");
      addcharstat("Bron", "<span id='eqweapon'>$weapon</span>");
      addcharstat("Pas", "<span id='eqbelt'>$belt</span>");
      addcharstat("Spodnie", "<span id='eqpants'>$pants</span>");
      addcharstat("Obuwie", "<span id='eqshoes'>$shoes</span>");

      rawoutput("<script src='http://code.jquery.com/jquery-latest.min.js' type='text/javascript'></script>");
      rawoutput("
        <script type='text/javascript'>
          $(document).ready(function(){
            $('.eqitem').click(function(e){
              e.preventDefault();
              var a = $(this);
              $.ajax({
                url: 'runmodule.php',
                type: 'get',
                data: { module: 'eq', op: 'unequip', id: a.attr('rel') },
              }).done(function(data){
                if (data != ''){
                  alert(data);
                } else {
                  a.parent().html('-');
                }
              });
            });
          });
        </script>
      ");
      /* }}} */
      break;
    case "village":
      addnav("Itemkowe Centrum", "runmodule.php?module=eq&op=enter");
      break;
    case "battle-victory":
      $drop = e_rand(0, 99);
      $dropafterpoint = e_rand(0, 100) / 100;
      $drop = $drop + $dropafterpoint;
      $sql = "SELECT * FROM (SELECT * FROM " . db_prefix("eqitems") . " WHERE droppable = 1 AND $drop <= dropchance) t1 ORDER BY RAND() LIMIT 1";
      $res = db_query($sql);
      $item = db_fetch_assoc($res);

      /* <hem, hem, hem..> */
      /*$sql = "SELECT * FROM " . db_prefix("eqitems") . " WHERE droppable = 1 AND dropchance >= $drop AND dropchance <= $item[dropchance] ORDER BY RAND() LIMIT 1";
      $res = db_query($sql);
      $item = db_fetch_assoc($res);*/
      /* </hem, hem, hem..> */

      if (!empty($item)){
        /* set the defaults */
        if ($item['droprace'] == "" || $item['droprace'] == 0)
          $item['droprace'] = $session['user']['race'];
        /* check the DKs */
        if ($session['user']['dragonkills'] >= $item['dropmindk']){
          /* check the rep */
          if (get_module_pref('rep', 'rep', $session['user']['acctid']) >= $item['dropminrep']){
            /* check the race */
            if ($session['user']['race'] == $item['droprace']){
              db_query("INSERT INTO " . db_prefix("accounts_eqitems") . "(acctid, itemid, implvl) VALUES('" . $session['user']['acctid'] . "', '" . $item['id'] . "', 0)");
              if (db_affected_rows() == 1){
                output("`EPrzeszukujesz cialo potwora i znajdujesz `G$item[name]`E! `e(`@$drop `e<= `@$item[dropchance]`e)`n");
              }
            }
          }
        }
      }
      break;
  }

  return $args;
}

function eq_runevent($type, $link)
{
  // NULL
}

function eq_run()
{
  global $session;

  $here = 'runmodule.php?module=eq';
  $op = httpget('op');

  /* unequip jest tutaj a reszta tam, żeby nie tworzyła się cała strona która
   * potem zwracałaby się w wywołaniu ajaxowym */
  if ($op == "unequip"){
    /* {{{ */
    $id = httpget('id');
    $item = db_fetch_assoc(db_query("SELECT * FROM " . db_prefix("eqitems") . " WHERE id = '$id' LIMIT 1"));

    $session['user']['attack'] = $session['user']['attack'] - $item['atkimpact'];
    $session['user']['defense'] = $session['user']['defense'] - $item['defimpact'];
    $session['user']['maxhitpoints'] = $session['user']['maxhitpoints'] - $item['hpimpact'];

    if ($item['cat'] == EQ_WEAPON){
      $session['user']['weapon'] = "";
      $session['user']['weapondmg'] = 0;
      db_query("UPDATE " . db_prefix("accounts") . " SET weapon = '', attack = " . $session['user']['attack'] . ", weapondmg = 0, maxhitpoints = " . $session['user']['maxhitpoints'] . " WHERE acctid = " . $session['user']['acctid'] . " LIMIT 1");
    } else {
      $session['user']['armor'] = "";
      $session['user']['armordef'] = 0;
      db_query("UPDATE " . db_prefix("accounts") . " SET armor = '', defense = " . $session['user']['defense'] . ", armordef = 0, maxhitpoints = " . $session['user']['maxhitpoints'] . " WHERE acctid = " . $session['user']['acctid'] . " LIMIT 1");
    }

    db_query("UPDATE " . db_prefix("accounts_eqitems") . " SET equipped = 0 WHERE acctid = '" . $session['user']['acctid'] . "' AND itemid = '$id' AND equipped = 1 LIMIT 1");

    if (db_affected_rows() == 0){
      echo "error! :C";
    }
    /* }}} */
  } else {
    page_header("Kowal");

    if ($op == "enter"){
      /* {{{ */
      commentdisplay("`n`EWchodzisz do kowala a kowal tez baba`n", "EQ", "EQ", 25, "EQ");
      addnav("Sklep", "$here&op=shop");
      addnav("Kowal ulepszacz", "$here&op=imp");
      addnav("Handel kamieniami", "$here&op=stonetrade");
      /* }}} */
    }
    else if ($op == "shop"){
      /* {{{ */
      $sql = "SELECT * FROM " . db_prefix("eqitems") . " WHERE inshop = 1";
      $res = db_query($sql);
      $i = 0;
      output("`EKowal tudziez sprzedawca chwalacy sie jakie to oni maja itemki na sprzedaz.`n`n");
      rawoutput("<center><img src='images/uscroll.GIF'></center>");
      rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
      rawoutput("<tr class='trhead'><td>Akcja</td><td>Nazwa</td><td>Atak</td><td>Obrona</td><td>Max. HP</td><td>Lesne walki</td><td>Podroze</td><td style='font-weight: bold;'>Cena kupna</td><td>Cena sprzedazy</td></tr>");
      while ($item = db_fetch_assoc($res)){
        rawoutput("<tr class='".($i % 2 ? "trlight" : "trdark")."'>");
        addnav("", "$here&op=buyitem&id=$item[id]");
        rawoutput("<td><a href='$here&op=buyitem&id=$item[id]' class='button'>&nbsp;Kup&nbsp;</a></td>");
        rawoutput("<td>$item[name]</td>");
        rawoutput("<td>$item[atkimpact]</td>");
        rawoutput("<td>$item[defimpact]</td>");
        rawoutput("<td>$item[hpimpact]</td>");
        rawoutput("<td>$item[ffimpact]</td>");
        rawoutput("<td>$item[timpact]</td>");
        rawoutput("<td style='font-weight: bold;'>$item[buyprice]</td>");
        rawoutput("<td>$item[sellprice]</td>");
        rawoutput("</tr>");
        $i++;
      }
      rawoutput("</table>");
      rawoutput("<center><img src='images/lscroll.GIF'></center>");
      output("`n`n`EPonadto, kowal oferuje opcje kupna przedmiotu od Ciebie`n`n");
      rawoutput("<center><img src='images/uscroll.GIF'></center>");
      rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
      rawoutput("<tr class='trhead'><td>Akcja</td><td>Nazwa</td><td>Atak</td><td>Obrona</td><td>Max. HP</td><td>Lesne walki</td><td>Podroze</td><td>Cena kupna</td><td style='font-weight: bold;'>Cena sprzedazy</td></tr>");
      $sql = "SELECT * FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE a.acctid = " . $session['user']['acctid'] . " AND a.equipped = 0";
      $res = db_query($sql);
      $i = 0;
      while ($item = db_fetch_assoc($res)){
        rawoutput("<tr class='".($i % 2 ? "trlight" : "trdark")."'>");
        addnav("", "$here&op=sellitem&id=$item[id]");
        rawoutput("<td><a href='$here&op=sellitem&id=$item[id]' class='button'>&nbsp;Sprzedaj&nbsp;</a></td>");
        rawoutput("<td>$item[name]</td>");
        rawoutput("<td>$item[atkimpact]</td>");
        rawoutput("<td>$item[defimpact]</td>");
        rawoutput("<td>$item[hpimpact]</td>");
        rawoutput("<td>$item[ffimpact]</td>");
        rawoutput("<td>$item[timpact]</td>");
        rawoutput("<td>$item[buyprice]</td>");
        rawoutput("<td style='font-weight: bold;'>$item[sellprice]</td>");
        rawoutput("</tr>");
        $i++;
      }
      rawoutput("</table>");
      rawoutput("<center><img src='images/lscroll.GIF'></center>");
      addnav("Powrot", "$here&op=enter");
      /* }}} */
    }
    else if ($op == "buyitem"){
      /* {{{ */
      $id = httpget('id');
      $sql = "SELECT buyprice FROM " . db_prefix("eqitems") . " WHERE id = '$id' LIMIT 1";
      $res = db_query($sql);
      /* na wszelki wypadek */
      if (db_affected_rows() == 0){
        output("`c`b`4Nie znaleziono przedmiotu o podanym ID!`b`c");
      } else {
        $item = db_fetch_assoc($res);
        if ($session['user']['gold'] < $item['buyprice'] && (!($session['user']['superuser'] & SU_MEGAUSER))){
          output("`EKowal smieje sie: '`GNie stac Cie!`E'");
          addnav("Powrot do sklepu", "$here&op=shop");
        } else {
          db_query("INSERT INTO " . db_prefix("accounts_eqitems") . "(acctid, itemid, implvl) VALUES('" . $session['user']['acctid'] . "', '" . $id . "', 0)");
          /* MEGAUSER nie traci piniążków */
          if (!($session['user']['superuser'] & SU_MEGAUSER))
            $session['user']['gold'] -= $item['buyprice'];
          output("`EKowal gratuluje Ci zakupu!");
          addnav("Powrot do sklepu", "$here&op=shop");
        }
      }
      addnav("Powrot", "$here&op=enter");
      /* }}} */
    }
    else if ($op == "sellitem"){
      /* {{{ */
      $id = httpget('id');
      /* dla pewności */
      $sql = "SELECT a.*, e.sellprice FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE itemid = '$id' AND acctid = '" . $session['user']['acctid'] . "'";
      $res = db_query($sql);
      if (db_affected_rows() == 0){
        output("`c`b`4Nie znaleziono przedmiotu o podanym ID!`b`c");
      } else {
        $item = db_fetch_assoc($res);
        /* usuwamy powiązanie itemka z userem */
        db_query("DELETE FROM " . db_prefix("accounts_eqitems") . " WHERE itemid = '$id' AND acctid = '" . $session['user']['acctid'] . "' LIMIT 1");
        /* usuwamy też i z ogólnej bazy itemków, jeśli item był ulepszany */
        if ($item['implvl'] > 0){
          db_query("DELETE FROM " . db_prefix("eqitems") . " WHERE id = '$id' LIMIT 1");
        }
        $session['user']['gold'] += $item['sellprice'];
        output("`EKowal gratuluje Ci sprzedazy!");
        addnav("Powrot do sklepu", "$here&op=shop");
      }
      addnav("Powrot", "$here&op=enter");
      /* }}} */
    }
    else if ($op == "stonetrade"){
      /* {{{ */
      $sortby = httpget('sortby');
      if ($sortby == ""){
        $sortby = "price";
      }
      /* tutaj, itemki do kupienia */
      /* -- 'acesid' => ACcounts_EqStones.ID */
      $sql = "SELECT a.*, a.id as acesid, e.*, u.name as username FROM " . db_prefix("accounts_eqstones") . " AS a INNER JOIN " . db_prefix("eqstones") . " AS e ON (a.stoneid = e.id) INNER JOIN " . db_prefix("accounts") . " AS u ON (a.acctid = u.acctid) WHERE onsale = 1 ORDER BY $sortby";
      $res = db_query($sql);
      $i = 0;
      output("`ELista kamieni wystawionych przez innych uzytkownikow`n`n");
      rawoutput("<center><img src='images/uscroll.GIF'></center>");
      rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
      addnav("", "$here&op=stonetrade&sortby=username");
      addnav("", "$here&op=stonetrade&sortby=name");
      addnav("", "$here&op=stonetrade&sortby=implvlinc");
      addnav("", "$here&op=stonetrade&sortby=maximplvl");
      addnav("", "$here&op=stonetrade&sortby=impchance");
      addnav("", "$here&op=stonetrade&sortby=burnchance");
      addnav("", "$here&op=stonetrade&sortby=price");
      rawoutput("
        <tr class='trhead'>
          <td>Akcja</td>
          <td><a href='$here&op=stonetrade&sortby=username'>Sprzedawca</a></td>
          <td><a href='$here&op=stonetrade&sortby=name'>Nazwa</a></td>
          <td><a href='$here&op=stonetrade&sortby=implvlinc'>O ile ulepsza</a></td>
          <td><a href='$here&op=stonetrade&sortby=maximplvl'>Max. poziom ulepszenia</a></td>
          <td><a href='$here&op=stonetrade&sortby=impchance'>Szansa na ulepszenie</a></td>
          <td><a href='$here&op=stonetrade&sortby=burnchance'>Szansa na spalenie</a></td>
          <td style='font-weight: bold;'><a href='$here&op=stonetrade&sortby=price'>Cena</a></td>
        </tr>");
      while ($stone = db_fetch_assoc($res)){
        rawoutput("<tr class='".($i % 2 ? "trlight" : "trdark")."'>");
        addnav("", "$here&op=buystone&id=$stone[acesid]");
        if ($stone['acctid'] === $session['user']['acctid']){
          rawoutput("<td><a href='$here&op=buystone&id=$stone[acesid]' class='button'>&nbsp;Anuluj&nbsp;</a></td>");
        } else {
          rawoutput("<td><a href='$here&op=buystone&id=$stone[acesid]' class='button'>&nbsp;Kup&nbsp;</a></td>");
        }
        rawoutput("<td>");
        output("$stone[username]");
        rawoutput("</td>");
        rawoutput("<td>$stone[name]</td>");
        rawoutput("<td>$stone[implvlinc]</td>");
        rawoutput("<td>$stone[maximplvl]</td>");
        rawoutput("<td>$stone[impchance]</td>");
        rawoutput("<td>$stone[burnchance]</td>");
        rawoutput("<td style='font-weight: bold;'>$stone[price]</td>");
        rawoutput("</tr>");
        $i++;
      }
      rawoutput("</table>");
      rawoutput("<center><img src='images/lscroll.GIF'></center>");

      /* zanim pokażemy itemki do wystawienia, musimy sprawdzić czy aby nie
       * osiągnął limitu */
      $limit_exceeded = 0;
      $s = db_fetch_assoc(db_query("SELECT count(*) as sum FROM " . db_prefix("accounts_eqstones") . " WHERE acctid = '" . $session['user']['acctid'] . "' AND onsale = 1"));
      /* TODO: jak będzie ten status donatora, to tutaj trza będzie pomiąchać */
      if ($s['sum'] >= 5){
        $limit_exceeded = 1;
      }

      /* tutaj, itemki do wystawienia */
      $sql = "SELECT * FROM " . db_prefix("accounts_eqstones") . " AS a INNER JOIN " . db_prefix("eqstones") . " AS e ON (a.stoneid = e.id) WHERE acctid = '" . $session['user']['acctid'] . "' AND onsale = 0";
      $res = db_query($sql);
      $i = 0;
      output("`n`n`n`ELista kamieni do wystawienia`n`n");
      if ($limit_exceeded){
        output("`4Niestety, wyczerpales swoj limit wystawionych kamieni`n`n");
      }
      rawoutput("<center><img src='images/uscroll.GIF'></center>");
      rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
      rawoutput("<tr class='trhead'><td>Akcja</td><td>&nbsp;</td><td>Nazwa</td><td>O ile ulepsza</td><td>Max. poziom ulepszenia</td><td>Szansa na ulepszenie</td><td>Szansa na spalenie</td></tr>");
      while ($stone = db_fetch_assoc($res)){
        rawoutput("<tr class='".($i % 2 ? "trlight" : "trdark")."'>");
        if ($limit_exceeded){
          rawoutput("<td style='text-align: center;'>-</td><td>&nbsp;</td>");
        } else {
          addnav("", "$here&op=setstoneonsale&id=$stone[id]");
          rawoutput("<form method='post' action='$here&op=setstoneonsale&id=$stone[id]'>");
          rawoutput("<td><input type='submit' name='submit' value='Wystaw'></td>");
          rawoutput("<td><input type='text' name='price' placeholder='Cena'></td>");
          rawoutput("</form>");
        }
        rawoutput("<td>$stone[name]</td>");
        rawoutput("<td>$stone[implvlinc]</td>");
        rawoutput("<td>$stone[maximplvl]</td>");
        rawoutput("<td>$stone[impchance]</td>");
        rawoutput("<td>$stone[burnchance]</td>");
        rawoutput("</tr>");
        $i++;
      }
      rawoutput("</table>");
      rawoutput("<center><img src='images/lscroll.GIF'></center>");

      addnav("Odswiez", "$here&op=stonetrade");
      addnav("Powrot", "$here&op=enter");
      /* }}} */
    }
    else if ($op == "buystone"){
      /* {{{ */
      $id = httpget('id');

      /* te wszystkie LIMIT 1 są tutaj zepewne niepotrzebne, bo $id to jest ten
       * cały PRIMARY KEY, które będzie unikalne, jedno, blablabla, ale ze względów
       * historycznych i sentymentalnych, zostawiam to, nie zaszkodzi :3
       */

      $stone = db_fetch_assoc(db_query("SELECT a.*, s.name, u.gold as usergold, u.name as username FROM " . db_prefix("accounts_eqstones") . " AS a INNER JOIN " . db_prefix("accounts") . " AS u ON (a.acctid = u.acctid) INNER JOIN " . db_prefix("eqstones") . " AS s ON (a.stoneid = s.id) WHERE a.id = '$id' AND a.onsale = 1 LIMIT 1"));
      if (db_affected_rows() == 0){
        output("`EError :C");
      } else {
        if ($session['user']['acctid'] === $stone['acctid']){
          db_query("UPDATE " . db_prefix("accounts_eqstones") . " SET onsale = 0, price = 0 WHERE id = '$id' AND onsale = 1 LIMIT 1");
          output("`EPomyslnie anulowales wystawienie kamienia");
        } else {
          if ($session['user']['gold'] < $stone['price']){
            output("`ENie stac cie!");
          } else {
            systemmail($stone['acctid'], "`G$stone[name]`E sprzedany!", $session['user']['name']. " `E kupil Twoj `G$stone[name]`E za $stone[price]`E!");
            db_query("UPDATE " . db_prefix("accounts") . " SET gold = '" . (int)((int)$stone['usergold'] + (int)$stone['price']) . "' WHERE acctid = '" . $stone['acctid'] . "' LIMIT 1");
            db_query("UPDATE " . db_prefix("accounts_eqstones") . " SET acctid = '" . $session['user']['acctid'] . "', onsale = 0, price = 0 WHERE id = '$id' LIMIT 1");
            output("`EBrawo, kupiles kamienia");
            $session['user']['gold'] -= $stone['price'];
          }
        }
      }
      addnav("Powrot do handlu kamieniami", "$here&op=stonetrade");
      addnav("Powrot", "$here&op=enter");
      /* }}} */
    }
    else if ($op == "setstoneonsale"){
      /* {{{ */
      $id = httpget('id');
      $price = httppost('price');

      /* Nie wiem czy faktycznie jest taka potrzeba, ale możnaby było tutaj
       * wstawić jakieś zabezpieczenie, które sprawdza, czy nie został
       * przypadkiem przekroczony limit wystawionych kamieni */

      db_query("UPDATE " . db_prefix("accounts_eqstones") . " SET onsale = 1, price = '$price' WHERE stoneid = '$id' AND acctid = '" . $session['user']['acctid'] . "' AND onsale = 0 LIMIT 1");

      if (db_affected_rows() == 0){
        output("Wystapil blad :C");
      } else {
        output("`EPomyslnie wystawiles przedmiot o ID '$id' za $price zlota.");
      }

      addnav("Powrot do handlu kamieniami", "$here&op=stonetrade");
      addnav("Powrot", "$here&op=enter");
      /* }}} */
    }
    else if ($op == "doimp"){
      /* {{{ */
      $sql = "SELECT * FROM " . db_prefix("eqstones") . " WHERE id = '" . $_POST['stone'] . "' LIMIT 1";
      $res = db_query($sql);
      $stone = db_fetch_assoc($res);
      if (db_affected_rows() == 0){
        output("`n`n`c`b`4Nie znaleziono kamienia o podanym ID!`b`c");
        addnav("Powrot", "$here&op=enter");
      } else {
        $sql = "SELECT a.implvl, e.* FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE e.id = '" . $_POST['item'] . "' LIMIT 1";
        $res = db_query($sql);
        $item = db_fetch_assoc($res);
        if (db_affected_rows() == 0){
          output("`n`n`c`b`4Nie znaleziono przedmiotu o podanym ID!`b`c");
          addnav("Powrot", "$here&op=enter");
        } else {
          /* sprawdzamy czy faktycznie można użyć kamienia na tym itemku */
          if ($item['implvl'] > $stone['maximplvl']){
            output("`n`n`c`b`4Blad!`b`n`n`4Nie mozna uzyc kamienia '$stone[name]' na przedmiocie '$item[name]'!`nPrzedmiot ma za wyskoki poziom!`c");
            addnav("Powrot", "$here&op=enter");
          } else {
            $rand = e_rand(0, 100);
            if ($rand <= $stone['impchance']){
              output("`@Udalo sie!`n");
              $newatkimpact = $item['atkimpact'];
              $newdefimpact = $item['defimpact'];
              $newhpimpact  = $item['hpimpact'];
              $newffimpact  = $item['ffimpact'];
              $newtimpact   = $item['timpact'];
              if ($item['implvlimpact'] & EQ_ATK)
                $newatkimpact += $stone['implvlinc'];
              if ($item['implvlimpact'] & EQ_DEF)
                $newdefimpact += $stone['implvlinc'];
              if ($item['implvlimpact'] & EQ_HP)
                $newhpimpact  += $stone['implvlinc'];
              if ($item['implvlimpact'] & EQ_FF)
                $newffimpact  += $stone['implvlinc'];
              if ($item['implvlimpact'] & EQ_T)
                $newtimpact   += $stone['implvlinc'];
              /* pozbywamy się tego +cosia */
              $item['name'] = preg_replace('/\s+\+(\d+)/', '', $item['name']);
              $newname      = $item['name'] . " +" . ($item['implvl'] + 1);
              /* usuwamy z bazy ten itemek co user mial przed chwilka
               * ale ale, jesli poziom ulepszenia jest wiekszy od zero */
              if ($item['implvl'] > 0){
                db_query("DELETE FROM " . db_prefix("eqitems") . " WHERE id = '" . $item['id'] . "' LIMIT 1");
              }
              /* zapisujemy nowy itemek w bazie */
              db_query("INSERT INTO " . db_prefix("eqitems") . "(name,cat,atkimpact,defimpact,hpimpact,ffimpact,timpact,inshop,buyprice,sellprice,droppable,dropchance,dropmindk,dropminrep,droprace,dropprof,implvlimpact) values('$newname','$item[cat]','$newatkimpact','$newdefimpact','$newhpimpact','$newffimpact','$newtimpact',0,'$item[buyprice]','$item[sellprice]',0,0,0,0,0,0,'$item[implvlimpact]')");
              /* db_insert_id() robi.. o to: Get the ID generated in the last query */
              db_query("INSERT INTO " . db_prefix("accounts_eqitems") . "(acctid, itemid, implvl) values('".$session['user']['acctid']."', '" . db_insert_id() . "', '" . ($item['implvl'] + 1) . "')");
              /* teraz jeszcze trzeba usunac ten poprzedni itemek */
              db_query("DELETE FROM " . db_prefix("accounts_eqitems") . " WHERE itemid = '" . $item['id'] . "' LIMIT 1");
              /* oooraz kamień którym się itemek ulepszało */
              /* megauser do testów */
              if (!($session['user']['superuser'] & SU_MEGAUSER)){
                db_query("DELETE FROM " . db_prefix("accounts_eqstones") . " WHERE stoneid = '" . $stone['id'] . "' AND acctid = '" . $session['user']['acctid'] . "' LIMIT 1");
              } else {
                output("`ePan jestes Megauser, wiec kamien nie zniknal :P");
              }
            } else if ($rand < 100 - $stone['burnchance']){
              output("`7Bums, nic sie nie stalo`n");
              /* megauser do testów */
              if ($session['user']['superuser'] & SU_MEGAUSER){
                output("`eA kamien nie zniknal, panie Megauser :P");
              } else {
                /* usuwamy kamień z plecaczka */
                db_query("DELETE FROM " . db_prefix("accounts_eqstones") . " WHERE stoneid = '" . $stone['id'] . "' AND acctid = '" . $session['user']['acctid'] . "' LIMIT 1");
              }
            } else {
              output("`4Niestety, item sie spalil`n");
              /* megauser do testów */
              if ($session['user']['superuser'] & SU_MEGAUSER){
                output("`eAlbo i nie, panie Megauser :P");
              } else {
                /* item znika z bazy tylko jeśli poziom ulepszenia jest większy od
                 * zera */
                if ($item['implvl'] > 0){
                  db_query("DELETE FROM " . db_prefix("eqitems") . " WHERE id = '" . $item['id'] . "' LIMIT 1");
                }
                /* a z plecaczka użytkownika znika zawsze */
                db_query("DELETE FROM " . db_prefix("accounts_eqitems") . " WHERE itemid = '" . $item['id'] . "' AND acctid = '" . $session['user']['acctid'] . "' LIMIT 1");
                /* usuwamy kamień z plecaczka */
                db_query("DELETE FROM " . db_prefix("accounts_eqstones") . " WHERE stoneid = '" . $stone['id'] . "' AND acctid = '" . $session['user']['acctid'] . "' LIMIT 1");
              }
            }
            addnav("Powrot do kowala ulepszacza", "$here&op=imp");
            addnav("Powrot", "$here&op=enter");
          }
        }
      }
      /* }}} */
    }
    else if ($op == "imp"){
      /* {{{ */
      $sql = "SELECT * FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "' AND a.equipped = 0 ORDER BY a.implvl";
      $res = db_query($sql);

      output("`EKowal mowi `GWybierz item:`E`n`n");
      addnav("", "$here&op=doimp");
      rawoutput("<form action='$here&op=doimp' method='post'>");
      rawoutput("<select id='item' name='item'>");
      while ($row = db_fetch_assoc($res)){
        rawoutput("<option value='$row[itemid]' rel='
          {
            \"name\": \"$row[name]\",
            \"atkimpact\": \"$row[atkimpact]\",
            \"defimpact\": \"$row[defimpact]\",
            \"hpimpact\": \"$row[hpimpact]\",
            \"ffimpact\": \"$row[ffimpact]\",
            \"timpact\": \"$row[timpact]\",
            \"implvlimpact\": \"$row[implvlimpact]\",
            \"implvl\": \"$row[implvl]\"
          }'>$row[name]</option>");
      }
      rawoutput("</select>");

      output("`n`n`n`EKowal mowi `GWybierz kamien`E`n`n");
      rawoutput("<select id='stone' name='stone'>");
      rawoutput("<option value='-1' rel='{ \"name\": \"-\", \"implvlinc\": 0, \"impchance\": 0, \"burnchance\": 0, \"maximplvl\": 0 }'>---</option>");
      rawoutput("</select><br><br><br>");

      /* diiiiiiiiiiiiiiiirty */
      $sql = "SELECT * FROM " . db_prefix("accounts_eqstones") . " AS a INNER JOIN " . db_prefix("eqstones") . " AS e ON (a.stoneid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "' AND onsale = 0";
      $res = db_query($sql);
      rawoutput("<select id='allstones' style='display: none;'>");
      while ($row = db_fetch_assoc($res)){
        rawoutput("<option value='$row[stoneid]' rel='
          {
            \"name\": \"$row[name]\",
            \"impchance\": \"$row[impchance]\",
            \"burnchance\": \"$row[burnchance]\",
            \"implvlinc\": \"$row[implvlinc]\",
            \"maximplvl\": \"$row[maximplvl]\"
          }'>$row[name]</option>");
      }
      rawoutput("</select>");
      /* diiiiiiiiiiiiiiiiiiiiiirty ends here */
      /* hopefully ... */

      output("`eProdukt finalny:`n");
      rawoutput("<center><img src='images/uscroll.GIF'></center>");
      rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
      rawoutput("<tr class='trhead'><td>Nazwa</td><td>Wplyw na atak</td><td>Wplyw na obrone</td><td>Wplyw na max. HP</td><td>Wplyw na LW</td><td>Wplyw na podroze</td><td style='font-weight: bold;'>Szansa na powodzenie</td><td style='font-weight: bold;'>Szansa na spalenie</td></tr>");
      rawoutput("<tr class='trlight'><td id='name'>-</td><td id='atkimpact'>-</td><td id='defimpact'>-</td><td id='hpimpact'>-</td><td id='ffimpact'>-</td><td id='timpact'>-</td><td id='impchance' style='font-weight: bold;'>-</td><td id='burnchance' style='font-weight: bold;'>-</td></tr>");
      rawoutput("</table>");
      rawoutput("<center><img src='images/lscroll.GIF'></center>");
      rawoutput("<br><br><input type='submit' name='submit' value='Ulepsz!'>");
      rawoutput("</form>");

      rawoutput("<script src='http://code.jquery.com/jquery-latest.min.js' type='text/javascript'></script>");
      rawoutput
      (
        "<script type='text/javascript'>
          $(document).ready(function(){
            function setStones()
            {
              var item = $.parseJSON($('option:selected', $('#item')).attr('rel'));
              $('#stone').empty();
              $('#allstones').each(function(){
                $('#allstones').children('option').each(function(){
                  if ($.parseJSON($(this).attr('rel')).maximplvl >= item.implvl){
                    $(this).clone().appendTo('#stone');
                  }
                });
              });
            }

            setStones();
            var item = $.parseJSON($('option:selected', $('#item')).attr('rel'));
            var stone = $.parseJSON($('option:selected', $('#stone')).attr('rel'));
            $('#name').html(item.name);
            $('#atkimpact').html(item.atkimpact);
            if (item.implvlimpact & " . EQ_ATK . "){
              $('#atkimpact').html(parseInt(item.atkimpact) + parseInt(stone.implvlinc));
            } else {
              $('#atkimpact').html(item.atkimpact);
            }
            if (item.implvlimpact & " . EQ_DEF . "){
              $('#defimpact').html(parseInt(item.defimpact) + parseInt(stone.implvlinc));
            } else {
              $('#defimpact').html(item.defimpact);
            }
            if (item.implvlimpact & " . EQ_HP . "){
              $('#hpimpact').html(parseInt(item.hpimpact) + parseInt(stone.implvlinc));
            } else {
              $('#hpimpact').html(item.hpimpact);
            }
            if (item.implvlimpact & " . EQ_FF . "){
              $('#ffimpact').html(parseInt(item.ffimpact) + parseInt(stone.implvlinc));
            } else {
              $('#ffimpact').html(item.ffimpact);
            }
            if (item.implvlimpact & " . EQ_T . "){
              $('#timpact').html(parseInt(item.timpact) + parseInt(stone.implvlinc));
            } else {
              $('#timpact').html(item.timpact);
            }
            $('#impchance').html(stone.impchance);
            $('#burnchance').html(stone.burnchance);

            $('#item').change(function(){
              setStones();
              var item = $.parseJSON($('option:selected', this).attr('rel'));
              var stone = $.parseJSON($('option:selected', $('#stone')).attr('rel'));
              $('#name').html(item.name);
              $('#atkimpact').html(item.atkimpact);
              if (item.implvlimpact & " . EQ_ATK . "){
                $('#atkimpact').html(parseInt(item.atkimpact) + parseInt(stone.implvlinc));
              } else {
                $('#atkimpact').html(item.atkimpact);
              }
              if (item.implvlimpact & " . EQ_DEF . "){
                $('#defimpact').html(parseInt(item.defimpact) + parseInt(stone.implvlinc));
              } else {
                $('#defimpact').html(item.defimpact);
              }
              if (item.implvlimpact & " . EQ_HP . "){
                $('#hpimpact').html(parseInt(item.hpimpact) + parseInt(stone.implvlinc));
              } else {
                $('#hpimpact').html(item.hpimpact);
              }
              if (item.implvlimpact & " . EQ_FF . "){
                $('#ffimpact').html(parseInt(item.ffimpact) + parseInt(stone.implvlinc));
              } else {
                $('#ffimpact').html(item.ffimpact);
              }
              if (item.implvlimpact & " . EQ_T . "){
                $('#timpact').html(parseInt(item.timpact) + parseInt(stone.implvlinc));
              } else {
                $('#timpact').html(item.timpact);
              }
              $('#impchance').html(stone.impchance);
              $('#burnchance').html(stone.burnchance);
            });

            $('#stone').change(function(){
              var item = $.parseJSON($('option:selected', $('#item')).attr('rel'));
              var stone = $.parseJSON($('option:selected', this).attr('rel'));
              if (item.implvlimpact & " . EQ_ATK . "){
                $('#atkimpact').html(parseInt(item.atkimpact) + parseInt(stone.implvlinc));
              } else {
                $('#atkimpact').html(item.atkimpact);
              }
              if (item.implvlimpact & " . EQ_DEF . "){
                $('#defimpact').html(parseInt(item.defimpact) + parseInt(stone.implvlinc));
              } else {
                $('#defimpact').html(item.defimpact);
              }
              if (item.implvlimpact & " . EQ_HP . "){
                $('#hpimpact').html(parseInt(item.hpimpact) + parseInt(stone.implvlinc));
              } else {
                $('#hpimpact').html(item.hpimpact);
              }
              if (item.implvlimpact & " . EQ_FF . "){
                $('#ffimpact').html(parseInt(item.ffimpact) + parseInt(stone.implvlinc));
              } else {
                $('#ffimpact').html(item.ffimpact);
              }
              if (item.implvlimpact & " . EQ_T . "){
                $('#timpact').html(parseInt(item.timpact) + parseInt(stone.implvlinc));
              } else {
                $('#timpact').html(item.timpact);
              }
              $('#impchance').html(stone.impchance);
              $('#burnchance').html(stone.burnchance);
            });
          });
        </script>"
      );

      addnav("Powrot", "$here&op=enter");
      /* }}} */
    }

    villagenav();
    page_footer();
  }
}

?>
