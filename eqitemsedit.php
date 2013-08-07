<?php

/*
 * Author: Ciuf
 * Heavily based on bossedit.php by Lonny Luberts / JT Traub
 */

require_once("common.php");
require_once("lib/http.php");

check_su_access(SU_MEGAUSER);

tlschema("eqedit");

page_header("Edytor Ekwipunku");

$op = httpget('op');
$id = httpget('id');

$editarray = array(
	"Ekwipunek,title",
  "name" => "Nazwa itemku,text|",
  "cat" => "Kategoria,enum,".EQ_HEAD.",Head,".EQ_SHOULDERS.",Shoulders,".EQ_BRACELET.",Bracelet,".EQ_ARMOR.",Armor,".EQ_RING.",Ring,".EQ_WEAPON.",Weapon,".EQ_BELT.",Belt,".EQ_PANTS.",Pants,".EQ_SHOES.",Shoes",
  "atkimpact" => "Wplyw na atak,int|",
  "defimpact" => "Wplyw na obrone,int|",
  "hpimpact" => "Wplyw na max. HP,int|",
  "ffimpact" => "Wplyw na LW,int|",
  "timpact" => "Wplyw na podroze,int|",
  "inshop" => "Czy wystepuje w sklepie?,enum,1,Tak,0,Nie",
  "buyprice" => "Cena kupna w sklepie,float|",
  "sellprice" => "Cena sprzedazy w sklepie,float|",
  "droppable" => "Czy dropi w lesie?,enum,1,Tak,0,Nie",
  "dropchance" => "Jesli dropi - jaka jest szansa (w %)?,float|",
  "dropmindk" => "Jesli dropi - minimalna ilosc DK zeby moglo dropnac,int|",
  "dropminrep" => "Jesli dropi - minimalna ilosc reputacji zeby moglo dropnac,int|",
  "droprace" => "Jesli dropi - wymagana rasa zeby moglo dropnac,int|",
  "dropprof" => "Jesli dropi - wymagana profesja zeby moglo dropnac,int|",
  "implvlimpact" => "Na co wplywa poziom ulepszenia?,bitfield,".(0xFFFFFF).",".EQ_ATK.",Atak,".EQ_DEF.",Obrona,".EQ_HP.",HP,".EQ_FF.",Walki,".EQ_T.",Podroze"
  //"implvlimpact" => "Na co wplywa poziom ulepszenia?,int|"
);

addnav("Inne");
require_once("lib/superusernav.php");
superusernav();
addnav("Funkcje");

if ($op == "save") {
  $name         = httppost('name');
  $cat          = httppost('cat');
  $atkimpact    = httppost('atkimpact');
  $defimpact    = httppost('defimpact');
  $hpimpact     = httppost('hpimpact');
  $ffimpact     = httppost('ffimpact');
  $timpact      = httppost('timpact');
  $inshop       = httppost('inshop');
  $buyprice     = httppost('buyprice');
  $sellprice    = httppost('sellprice');
  $droppable    = httppost('droppable');
  $dropchance   = httppost('dropchance');
  $dropmindk    = httppost('dropmindk');
  $dropminrep   = httppost('dropminrep');
  $droprace     = httppost('droprace');
  $dropprof     = httppost('dropprof');
  $implvlimpact = httppost('implvlimpact');
  /* v--- "new implvlimpact" */
  $newili = 0;

  /* implvlimpact is an array in which keys are the bits that are set to 1, so
   * we have to convert it to integer */
  while (list($k, $v) = each($implvlimpact)){
    if ($v) $newili += (int)$k;
  }
  $implvlimpact = $newili;

  if ((int)$id == 0) {
    $sql = "INSERT INTO " . db_prefix("eqitems") . " (name,cat,atkimpact,defimpact,hpimpact,ffimpact,timpact,inshop,buyprice,sellprice,droppable,dropchance,dropmindk,dropminrep,droprace,dropprof,implvlimpact) VALUES ('$name','$cat','$atkimpact','$defimpact','$hpimpact','$ffimpact','$timpact','$inshop','$buyprice','$sellprice','$droppable','$dropchance','$dropmindk','$dropminrep','$droprace','$dropprof','$implvlimpact');";
    $note = "`^Dodano item.`0";
    $errnote = "`\$Problem z dodaniem itemku.`0";
  } else {
    $sql = "UPDATE " . db_prefix("eqitems") . " SET name='$name',cat='$cat',atkimpact='$atkimpact',defimpact='$defimpact',hpimpact='$hpimpact',ffimpact='$ffimpact',inshop='$inshop',buyprice='$buyprice',sellprice='$sellprice',droppable='$droppable',dropchance='$dropchance',dropmindk='$dropmindk',dropminrep='$dropminrep',droprace='$droprace',dropprof='$dropprof',implvlimpact='$implvlimpact' WHERE id='$id'";
    $note = "`^Zmodyfikowano item.`0";
    $errnote = "`\$Problem z modyfikacja itemku.`0";
  }
  db_query($sql);
  if (db_affected_rows() == 0) {
    output($errnote);
    rawoutput(db_error());
  } else {
    output($note);
  }
  $op = "";
} elseif ($op == "delete") {
  $sql = "DELETE FROM " . db_prefix("eqitems") . " WHERE id = '$id'";
  $query = db_query($sql);
  output("`^Usunieto item.`0");
	$op = "";
}

if ($op == ""){
  $sql = "SELECT * FROM " . db_prefix("eqitems") . " ORDER BY id ASC;";
  $query = db_query($sql);
  output("`n`n`@`c`b-= Edytor Itemkow =-`b`c`n`n");
  rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
  rawoutput("<tr class='trhead'><td>Akcja</td><td>Nazwa</td><td>Kategoria</td><td>Wplyw na atak</td><td>Wplyw na obrone</td><td>Wplyw na max. HP</td><td>Wplyw na LW</td><td>Wplyw na podroze</td><td>Do kupienia w sklepie?</td><td>Cena kupna</td><td>Cena sprzedazy</td><td>Czy dropi?</td><td>Szansa na drop</td><td>Min. DK dla dropu</td><td>Min. repy dla dropu</td><td>Wymagana rasa dla dropu</td><td>Wymagana profesja dla dropu</td><td>Na co wplywa poziom ulepszenia</td></tr>");
  $i = 0;
  while ($row = db_fetch_assoc($query)){
    $id = $row['id'];
    rawoutput("<tr class='" . ($i % 2 ? "trlight" : "trdark") . "'>");
    addnav("", "eqitemsedit.php?op=edit&id=$id");
    addnav("", "eqitemsedit.php?op=delete&id=$id");
    rawoutput("<td>[<a href='eqitemsedit.php?op=edit&id=$id'>Edytuj</a> | <a href='eqitemsedit.php?op=delete&id=$id' onClick='return confirm(\"Na pewno usunac?\");'>Usun</a>]</td>");
    rawoutput("<td>$row[name]</td>");
    rawoutput("<td>$row[cat]</td>");
    rawoutput("<td>$row[atkimpact]</td>");
    rawoutput("<td>$row[defimpact]</td>");
    rawoutput("<td>$row[hpimpact]</td>");
    rawoutput("<td>$row[ffimpact]</td>");
    rawoutput("<td>$row[timpact]</td>");
    rawoutput("<td>$row[inshop]</td>");
    rawoutput("<td>$row[buyprice]</td>");
    rawoutput("<td>$row[sellprice]</td>");
    rawoutput("<td>$row[droppable]</td>");
    rawoutput("<td>$row[dropchance]</td>");
    rawoutput("<td>$row[dropmindk]</td>");
    rawoutput("<td>$row[dropminrep]</td>");
    rawoutput("<td>$row[droprace]</td>");
    rawoutput("<td>$row[dropprof]</td>");
    rawoutput("<td>$row[implvlimpact]</td>");
    rawoutput("</tr>");
    $i++;
  }
	addnav("Funkcje");
	addnav("Dodaj Item", "eqitemsedit.php?op=add");
	addnav("Odswiez", "eqitemsedit.php");
	addnav("Resetuj Itemki", "eqitemsedit.php?op=reset");
	boss_help();
} elseif ($op == "edit" || $op == "add") {
	require_once("lib/showform.php");
  if ($op == "edit"){
    $sql = "SELECT * FROM " . db_prefix("eqitems") . " WHERE id = '$id'";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
  } elseif ($op == "add") {
    $row = array(
    "name" => "",
    "cat" => "",
    "atkimpact" => 0,
    "defimpact" => 0,
    "hpimpact" => 0,
    "ffimpact" => 0,
    "timpact" => 0,
    "inshop" => 0,
    "buyprice" => 0,
    "sellprice" => 0,
    "droppable" => 0,
    "dropchance" => 0,
    "dropmindk" => 0,
    "dropminrep" => 0,
    "droprace" => "",
    "dropprof" => "",
    "implvlimpact" => 0
    );
    $id = 0;
  }
  rawoutput("<form action='eqitemsedit.php?op=save&id=$id' method='POST'>");
  addnav("", "eqitemsedit.php?op=save&id=$id");
  showform($editarray, $row);
  rawoutput("</form>");
	addnav("Functions");
	addnav("Edytor Itemkow", "eqitemsedit.php");
	boss_help();
} elseif ($op == "reset") {
	output("`n`n`^[Not implemented].`0");
	addnav("Edytor Itemkow", "eqitemsedit.php");
}

function boss_help()
{
  //output("`nPomocna pomoc dla edytora bossow`n`n");
}

page_footer();
?>
