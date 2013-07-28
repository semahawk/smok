<?php

define("OVERRIDE_FORCED_NAV", true);
require_once("common.php");
require_once("lib/sanitize.php");
require_once("lib/http.php");
require_once("lib/buffs.php");

$op = httpget('op');

global $session;

if ($op == "equip"){
  /* {{{ */
  $id = httpget('id');

  /* sprawdzamy w jakiej kategorii jest ony itemek */
  $sql = "SELECT cat FROM " . db_prefix("eqitems") . " WHERE id = '$id' LIMIT 1";
  $res = db_query($sql);
  $item = db_fetch_assoc($res);

  /* sprawdzamy czy jest już założony jakiś itemek z tej samej kategorii */
  $sql = "SELECT a.*, e.cat FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE a.acctid = '".$session['user']['acctid']."' AND equipped = 1 AND e.cat = $item[cat]";
  $res = db_query($sql);

  if (db_affected_rows() == 0){
    db_query("UPDATE " . db_prefix("accounts_eqitems") . " SET equipped = 1 WHERE acctid = '" . $session['user']['acctid'] . "' AND itemid = '$id'");

    if (db_affected_rows() == 0){
      echo "error! :C";
    }
  } else {
    echo "Slot zajety!";
  }
  /* }}} */
} else {
  popup_header("Ekwipunek");

  rawoutput("<center><table>" .
              "<tr>" .
                "<td style='font-size: 20px;'><a href='eqshow.php?op=showitems'>Ekwipunek</a></td>" .
                "<td>&nbsp;&nbsp;&middot;&nbsp;&nbsp;</td>" .
                "<td style='font-size: 20px;'><a href='eqshow.php?op=showstones'>Kamienie</a></td>" .
              "</tr>" .
            "</table></center><br/><br/>");

  if ($op == "" || $op == "showitems"){
    /* {{{ */
    $sql = "SELECT * FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "' AND a.equipped = 0";
    $res = db_query($sql);
    rawoutput("<center><img src='images/uscroll.GIF'></center>");
    rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
    rawoutput("<tr class='trhead'><td>Akcja</td><td>Nazwa</td><td>Atak</td><td>Obrona</td><td>Max. HP</td><td>Lesne Walki</td><td>Podroze</td><td>Cena kupna</td><td>Cena sprzedazy</td></tr>");
    $i = 0;
    while ($item = db_fetch_assoc($res)){
      rawoutput("<tr class='".($i % 2 ? "trdark" : "trlight")."'>");
      $cat = "";
      switch ($item['cat']){
        case EQ_HEAD      : $cat = "eqhead"; break;
        case EQ_SHOULDERS : $cat = "eqshoulders"; break;
        case EQ_BRACELET  : $cat = "eqbracelet"; break;
        case EQ_ARMOR     : $cat = "eqarmor"; break;
        case EQ_RING      : $cat = "eqring"; break;
        case EQ_WEAPON    : $cat = "eqweapon"; break;
        case EQ_BELT      : $cat = "eqbelt"; break;
        case EQ_PANTS     : $cat = "eqpants"; break;
        case EQ_SHOES     : $cat = "eqshoes"; break;
      }
      rawoutput("<td><a href='#' class='eqitem' rel='$item[id]:$cat:$item[name]'>Zaloz</a></td>");
      rawoutput("<td>$item[name]</td>");
      rawoutput("<td>$item[atkimpact]</td>");
      rawoutput("<td>$item[defimpact]</td>");
      rawoutput("<td>$item[hpimpact]</td>");
      rawoutput("<td>$item[ffimpact]</td>");
      rawoutput("<td>$item[timpact]</td>");
      rawoutput("<td>$item[buyprice]</td>");
      rawoutput("<td>$item[sellprice]</td>");
      rawoutput("</tr>");
      $i++;
    }
    rawoutput("</table>");
    rawoutput("<center><img src='images/lscroll.GIF'></center>");

    rawoutput("<script src='http://code.jquery.com/jquery-latest.min.js' type='text/javascript'></script>");
    rawoutput("
      <script type='text/javascript'>
        $(document).ready(function(){
          $('.eqitem').click(function(e){
            e.preventDefault();
            var a = $(this);
            var id = $(this).attr('rel').split(':')[0];
            var cat = $(this).attr('rel').split(':')[1];
            var name = $(this).attr('rel').split(':')[2];
            $.ajax({
              url: 'eqshow.php',
              type: 'get',
              data: { op: 'equip', id: id },
            }).done(function(data){
              if (data != ''){
                alert(data);
              } else {
                a.parent().parent().empty();
                window.opener.$('#' + cat).html(name);
              }
            });
          });
        });
      </script>
    ");
    /* }}} */
  } else if ($op == "showstones"){
    /* {{{ */
    $sql = "SELECT * FROM " . db_prefix("accounts_eqstones") . " AS a INNER JOIN " . db_prefix("eqstones") . " AS e ON (a.stoneid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "'";
    $res = db_query($sql);
    rawoutput("<center><img src='images/uscroll.GIF'></center>");
    rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
    rawoutput("<tr class='trhead'><td>Nazwa</td><td>O ile ulepsza</td><td>Max. poziom ulepszenia</td><td>Szansa na ulepszenie</td><td>Szansa na spalenie</td><td>Cena (jesli wystawione w handlu)</td></tr>");
    $i = 0;
    while ($stone = db_fetch_assoc($res)){
      rawoutput("<tr class='".($i % 2 ? "trdark" : "trlight")."'>");
      if ($stone['onsale'] === "1"){
        rawoutput("<td style='color: #888;'><i>$stone[name]</i></td>");
      } else {
        rawoutput("<td>$stone[name]</td>");
      }
      rawoutput("<td>$stone[implvlinc]</td>");
      rawoutput("<td>$stone[maximplvl]</td>");
      rawoutput("<td>$stone[impchance]</td>");
      rawoutput("<td>$stone[burnchance]</td>");
      if ($stone['onsale'] === "1"){
        rawoutput("<td>$stone[price]</td>");
      } else {
        rawoutput("<td>-</td>");
      }
      rawoutput("</tr>");
      $i++;
    }
    rawoutput("</table>");
    rawoutput("<center><img src='images/lscroll.GIF'></center>");
    /* }}} */
  }

  popup_footer();
}

?>
