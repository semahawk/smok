<?php

/*
 * Autor: Ciuf
 *
 */

require_once("common.php");
require_once("lib/villagenav.php");
require_once("lib/commentary.php");

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
      "eqhead" => "Nakrycie glowy,text|",
      "eqshoulders" => "Naramienniki,text|",
      "eqbracelet" => "Naszyjnik,text|",
      "eqarmor" => "Pancerz,text|",
      "eqring" => "Pierscien,text|",
      "eqweapon" => "Bron,text|",
      "eqbelt" => "Pas,text|",
      "eqpants" => "Spodnie,text|",
      "eqshoes" => "Buty,text|",
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
      "dropchance int(11) not null," .
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
      "dropchance int(3) not null," .
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
      //"foreign key(acctid) references " . db_prefix("accounts") . "(acctid)," .
      //"foreign key(itemid) references " . db_prefix("eqitems") . "(id)," .
      /* poziom ulepszenia */
      "implvl int(11) not null" .
    ");";

  $create_accounts_eqstones =
    "CREATE TABLE IF NOT EXISTS " . db_prefix("accounts_eqstones") . " (" .
      "id integer primary key auto_increment," .
      "acctid int(11) not null," .
      "stoneid integer not null" .
      //"foreign key(acctid) references " . db_prefix("accounts") . "(acctid)," .
      //"foreign key(stoneid) references " . db_prefix("eqstones") . "(id)" .
    ");";

  /* create the tables */
  db_query($create_eqitems);
  db_query($create_eqstones);
  db_query($create_accounts_eqitems);
  db_query($create_accounts_eqstones);

  module_addhook("charstats");
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
      $head      = get_module_pref('eqhead');
      $shoulders = get_module_pref('eqshoulders');
      $bracelet  = get_module_pref('eqbracelet');
      $armor     = get_module_pref('eqarmor');
      $ring      = get_module_pref('eqring');
      $weapon    = get_module_pref('eqweapon');
      $belt      = get_module_pref('eqbelt');
      $pants     = get_module_pref('eqpants');
      $shoes     = get_module_pref('eqshoes');

      /* żeby po dwa razy get_module_pref nie wywoływać */
      $head      = $head      !== "" ? $head      : "-";
      $shoulders = $shoulders !== "" ? $shoulders : "-";
      $bracelet  = $bracelet  !== "" ? $bracelet  : "-";
      $armor     = $armor     !== "" ? $armor     : "-";
      $ring      = $ring      !== "" ? $ring      : "-";
      $weapon    = $weapon    !== "" ? $weapon    : "-";
      $belt      = $belt      !== "" ? $belt      : "-";
      $pants     = $pants     !== "" ? $pants     : "-";
      $shoes     = $shoes     !== "" ? $shoes     : "-";

      addcharstat('EQ <a href="eqshow.php" target="_blank" onClick=\'window.open("eqshow.php", "eqshowphp", "scrollbars=yes,resizable=yes,width=800,height=600").focus(); return false;\'>(otworz)</a>');
      addcharstat("Nakrycie glowy", $head);
      addcharstat("Naramienniki", $shoulders);
      addcharstat("Naszyjnik", $bracelet);
      addcharstat("Pancerz", $armor);
      addcharstat("Pierscien", $ring);
      addcharstat("Bron", $weapon);
      addcharstat("Pas", $belt);
      addcharstat("Spodnie", $pants);
      addcharstat("Obuwie", $shoes);
      break;
    case "village":
      addnav("Kowal", "runmodule.php?module=eq&op=enter");
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
  $act = httpget('act');

  page_header("Kowal");

  if ($op == "enter"){
    commentdisplay("`n`EWchodzisz do kowala a kowal tez baba`n", "EQ", "EQ", 25, "EQ");
    addnav("Sklep", "$here&op=shop");
    addnav("Kowal ulepszacz", "$here&op=imp");
  }
  else if ($op == "shop"){
    addnav("Powrot", "$here&op=enter");
  }
  else if ($op == "doimp"){
    $sql = "SELECT * FROM " . db_prefix("eqstones") . " WHERE id = '" . $_POST['stone'] . "' LIMIT 1";
    $res = db_query($sql);
    $stone = db_fetch_assoc($res);
    if (db_affected_rows() == 0){
      output("`n`n`c`b`4Nie znaleziono kamienia o podanym ID!`b`c");
      addnav("Powrot", "$here&op=enter");
    } else {
      //$sql = "SELECT * FROM " . db_prefix("eqitems") . " WHERE id = '" . $_POST['item'] . "' LIMIT 1";
      $sql = "SELECT a.implvl, e.* FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE e.id = '" . $_POST['item'] . "' LIMIT 1";
      $res = db_query($sql);
      $item = db_fetch_assoc($res);
      if (db_affected_rows() == 0){
        output("`n`n`c`b`4Nie znaleziono przedmiotu o podanym ID!`b`c");
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
            db_query("DELETE FROM " . db_prefix("eqitems") . " WHERE id = '" . $item['id'] . "'");
          }
          /* zapisujemy nowy itemek w bazie */
          db_query("INSERT INTO " . db_prefix("eqitems") . "(name,cat,atkimpact,defimpact,hpimpact,ffimpact,timpact,inshop,buyprice,sellprice,droppable,dropchance,dropmindk,dropminrep,droprace,dropprof,implvlimpact) values('$newname','$item[cat]','$newatkimpact','$newdefimpact','$newhpimpact','$newffimpact','$newtimpact','$item[inshop]','$item[buyprice]','$item[sellprice]','0','0','0','0','0','0','$item[implvlimpact]')");
          /* db_insert_id() robi.. o to: Get the ID generated in the last query */
          db_query("INSERT INTO " . db_prefix("accounts_eqitems") . "(acctid, itemid, implvl) values('".$session['user']['acctid']."', '" . db_insert_id() . "', '" . ($item['implvl'] + 1) . "')");
          /* teraz jeszcze trzeba usunac ten poprzedni itemek */
          db_query("DELETE FROM " . db_prefix("accounts_eqitems") . " WHERE itemid = '" . $item['id'] . "'");
          /* oooraz kamień którym się itemek ulepszało (no, chyba żeś megauser) */
          if (!($session['user']['superuser'] & SU_MEGAUSER)){
            db_query("DELETE FROM " . db_prefix("accounts_eqstones") . " WHERE stoneid = '" . $stone['id'] . "' AND acctid = '" . $session['user']['acctid'] . "'");
          } else {
            output("`ePan jestes Admin, wiec kamien nie zniknal :P");
          }
        } else if ($rand - $stone['impchance'] < 100 - $stone['impchance'] - $stone['burnchance']){
          output("`7Bums, nic sie nie stalo`n");
        } else {
          output("`4Niestety, item sie spalil`n");
          if ($session['user']['superuser'] & SU_MEGAUSER){
            output("`eAlbo i nie, panie Adminie :P");
          } else {
            /* item znika z bazy tylko jeśli poziom ulepszenia jest większy od
             * zera */
            if ($item['implvl'] > 0){
              db_query("DELETE FROM " . db_prefix("eqitems") . " WHERE id = '" . $item['id'] . "'");
            }
            /* a z plecaczka użytkownika znika zawsze */
            db_query("DELETE FROM " . db_prefix("accounts_eqitems") . " WHERE itemid = '" . $item['id'] . "' AND acctid = '" . $session['user']['acctid'] . "'");
          }
        }
        addnav("Powrot", "$here&op=enter");
      }
    }
  }
  else if ($op == "imp"){
    $sql = "SELECT * FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "'";
    $res = db_query($sql);

    output("`EKowal mowi `GWybierz item:`E`n`n");
    addnav("", "$here&op=doimp");
    rawoutput("<form action='$here&op=doimp' method='post'>");
    rawoutput("<select id='item' name='item'>");
    rawoutput("<option value='-1' rel='{ \"name\": \"-\", \"atkimpact\": 0, \"defimpact\": 0, \"hpimpact\": 0, \"ffimpact\": 0, \"timpact\": 0, \"implvlimpact\": 0 }'>---</option>");
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

    $sql = "SELECT * FROM " . db_prefix("accounts_eqstones") . " AS a INNER JOIN " . db_prefix("eqstones") . " AS e ON (a.stoneid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "'";
    $res = db_query($sql);

    output("`n`n`n`EKowal mowi `GWybierz kamien`E`n`n");
    rawoutput("<select id='stone' name='stone'>");
    rawoutput("<option value='-1' rel='{ \"implvlinc\": 0 }'>---</option>");
    while ($row = db_fetch_assoc($res)){
      rawoutput("<option value='$row[stoneid]' rel='
        {
          \"name\": \"$row[name]\",
          \"impchance\": \"$row[impchance]\",
          \"burnchance\": \"$row[burnchance]\",
          \"implvlinc\": \"$row[implvlinc]\"
        }'>$row[name]</option>");
    }
    rawoutput("</select><br><br><br>");
    output("`eProdukt finalny:`n");
    rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
    rawoutput("<tr class='trhead'><td>Nazwa</td><td>Wplyw na atak</td><td>Wplyw na obrone</td><td>Wplyw na max. HP</td><td>Wplyw na LW</td><td>Wplyw na podroze</td><td style='font-weight: bold;'>Szansa na powodzenie</td><td style='font-weight: bold;'>Szansa na spalenie</td></tr>");
    rawoutput("<tr class='trlight'><td id='name'>-</td><td id='atkimpact'>-</td><td id='defimpact'>-</td><td id='hpimpact'>-</td><td id='ffimpact'>-</td><td id='timpact'>-</td><td id='impchance' style='font-weight: bold;'>-</td><td id='burnchance' style='font-weight: bold;'>-</td></tr>");
    rawoutput("</table>");
    rawoutput("<br><br><input type='submit' name='submit' value='Ulepsz!'>");
    rawoutput("</form>");

    rawoutput("<script src='http://code.jquery.com/jquery-latest.min.js' type='text/javascript'></script>");
    rawoutput
    (
      "<script type='text/javascript'>
        $(document).ready(function(){
          $('#item').change(function(){
            var item = $.parseJSON($('option:selected', this).attr('rel'));
            var stone = $.parseJSON($('option:selected', '#stone').attr('rel'));
            $('#name').html(item.name);
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
          });

          $('#stone').change(function(){
            var item = $.parseJSON($('option:selected', $('#item')).attr('rel'));
            var stone = $.parseJSON($('option:selected', this).attr('rel'));
            if (item.implvlimpact & " . EQ_ATK . "){
              $('#atkimpact').html(parseInt(item.atkimpact) + parseInt(stone.implvlinc));
            } else {
              $('#atkimpact').html(parseInt(item.atkimpact));
            }
            if (item.implvlimpact & " . EQ_DEF . "){
              $('#defimpact').html(parseInt(item.defimpact) + parseInt(stone.implvlinc));
            } else {
              $('#defimpact').html(parseInt(item.defimpact));
            }
            if (item.implvlimpact & " . EQ_HP . "){
              $('#hpimpact').html(parseInt(item.hpimpact) + parseInt(stone.implvlinc));
            } else {
              $('#hpimpact').html(parseInt(item.hpimpact));
            }
            if (item.implvlimpact & " . EQ_FF . "){
              $('#ffimpact').html(parseInt(item.ffimpact) + parseInt(stone.implvlinc));
            } else {
              $('#ffimpact').html(parseInt(item.ffimpact));
            }
            if (item.implvlimpact & " . EQ_T . "){
              $('#timpact').html(parseInt(item.timpact) + parseInt(stone.implvlinc));
            } else {
              $('#timpact').html(parseInt(item.timpact));
            }
            $('#impchance').html(stone.impchance);
            $('#burnchance').html(stone.burnchance);
          });
        });
      </script>"
    );

    addnav("Powrot", "$here&op=enter");
  }

  villagenav();
  page_footer();
}

?>
