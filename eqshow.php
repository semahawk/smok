<?php
define("OVERRIDE_FORCED_NAV", true);
require_once("common.php");
require_once("lib/sanitize.php");
require_once("lib/http.php");
require_once("lib/buffs.php");

$op = httpget('op');

popup_header("Ekwipunek");

global $session;

rawoutput("<table>" .
            "<tr>" .
              "<td><a href='eqshow.php?op=showitems'>Ekwipunek</a></td>" .
              "<td><a href='eqshow.php?op=showstones'>Kamienie</a></td>" .
            "</tr>" .
          "</table>");

if ($op == "" || $op == "showitems"){
  $sql = "SELECT * FROM " . db_prefix("accounts_eqitems") . " AS a INNER JOIN " . db_prefix("eqitems") . " AS e ON (a.itemid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "'";
  $res = db_query($sql);
  rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
  while ($row = db_fetch_assoc($res)){
    rawoutput("<tr>");
    rawoutput("<td>$row[name]</td>");
    rawoutput("</tr>");
  }
  rawoutput("</table>");
} else if ($op == "showstones"){
  $sql = "SELECT * FROM " . db_prefix("accounts_eqstones") . " AS a INNER JOIN " . db_prefix("eqstones") . " AS e ON (a.stoneid = e.id) WHERE a.acctid = '" . $session['user']['acctid'] . "'";
  $res = db_query($sql);
  rawoutput("<table>");
  while ($row = db_fetch_assoc($res)){
    rawoutput("<tr>");
    rawoutput("<td>$row[name]</td>");
    rawoutput("</tr>");
  }
  rawoutput("</table>");
}

popup_footer();

?>
