<?php

/*
 * Author: Ciuf
 * Heavily based on titleedit.php by Lonny Luberts / JT Traub
 */

require_once("common.php");
require_once("lib/http.php");

check_su_access(SU_MEGAUSER);

tlschema("eqedit");

page_header("Edytor Kamieni");

$op = httpget('op');
$id = httpget('id');

$editarray = array(
	"Ekwipunek,title",
  "name" => "Nazwa kamienia,text|",
  "implvlinc" => "O ile zwieksza poziom ulepszenia,text|",
  "maximplvl" => "Maksymalny poziom ulepszenia,int|",
  "dropchance" => "Szansa na drop w lesie,int|",
  "impchance" => "Szansa na powodzenie ulepszenia,int|",
  "burnchance" => "Szansa na spalenie itemku,int|",
);

addnav("Inne");
require_once("lib/superusernav.php");
superusernav();
addnav("Funkcje");

if ($op == "save") {
  $name       = httppost('name');
  $implvlinc  = httppost('implvlinc');
  $maximplvl  = httppost('maximplvl');
  $dropchance = httppost('dropchance');
  $impchance  = httppost('impchance');
  $burnchance = httppost('burnchance');

  if ((int)$id == 0) {
    $sql = "INSERT INTO " . db_prefix("eqstones") . " (name,implvlinc,maximplvl,dropchance,impchance,burnchance) VALUES ('$name','$implvlinc','$maximplvl','$dropchance','$impchance','$burnchance');";
    $note = "`^Dodano kamien.`0";
    $errnote = "`\$Problem z dodaniem kamienia.`0";
  } else {
    $sql = "UPDATE " . db_prefix("eqstones") . " SET name='$name',implvlinc='$implvlinc',maximplvl='$maximplvl',dropchance='$dropchance',impchance='$impchance',burnchance='$burnchance' WHERE id=$id";
    $note = "`^Zmodyfikowano kamien.`0";
    $errnote = "`\$Problem z modyfikacja kamienia.`0";
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
  $sql = "DELETE FROM " . db_prefix("eqstones") . " WHERE id = '$id'";
  $query = db_query($sql);
  output("`^Usunieto kamien.`0");
	$op = "";
}

if ($op == ""){
  $sql = "SELECT * FROM " . db_prefix("eqstones") . " ORDER BY id ASC;";
  $query = db_query($sql);
  output("`n`n`@`c`b-= Edytor Kamieni =-`b`c`n`n");
  rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
  rawoutput("<tr class='trhead'><td>Akcja</td><td>Nazwa</td><td>O ile zwieksza poziom ulepszenia</td><td>Maksymalny poziom ulepszenia</td><td>Szansa na drop</td><td>Szansa na powodzenie ulepszenia</td><td>Szansa na spalenie itemku</td></tr>");
  $i = 0;
  while ($row = db_fetch_assoc($query)){
    $id = $row['id'];
    rawoutput("<tr class='" . ($i % 2 ? "trlight" : "trdark") . "'>");
    addnav("", "eqstonesedit.php?op=edit&id=$id");
    addnav("", "eqstonesedit.php?op=delete&id=$id");
    rawoutput("<td>[<a href='eqstonesedit.php?op=edit&id=$id'>Edytuj</a> | <a href='eqstonesedit.php?op=delete&id=$id' onClick='return confirm(\"Na pewno usunac?\");'>Usun</a>]</td>");
    rawoutput("<td>$row[name]</td>");
    rawoutput("<td>$row[implvlinc]</td>");
    rawoutput("<td>$row[maximplvl]</td>");
    rawoutput("<td>$row[dropchance]</td>");
    rawoutput("<td>$row[impchance]%</td>");
    rawoutput("<td>$row[burnchance]%</td>");
    rawoutput("</tr>");
    $i++;
  }
	addnav("Funkcje");
	addnav("Dodaj Kamien", "eqstonesedit.php?op=add");
	addnav("Odswiez", "eqstonesedit.php");
	addnav("Resetuj Kamienie", "eqstonesedit.php?op=reset");
	boss_help();
} elseif ($op == "edit" || $op == "add") {
	require_once("lib/showform.php");
  if ($op == "edit"){
    $sql = "SELECT * FROM " . db_prefix("eqstones") . " WHERE id = '$id'";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
  } elseif ($op == "add") {
    $row = array(
    "name" => "",
    "implvlinc" => 0,
    "maximplvl" => 0,
    "dropchance" => 0,
    "impchance" => 0,
    "burnchance" => 0,
    );
    $id = 0;
  }
  rawoutput("<form action='eqstonesedit.php?op=save&id=$id' method='POST'>");
  addnav("", "eqstonesedit.php?op=save&id=$id");
  showform($editarray, $row);
  rawoutput("</form>");
	addnav("Functions");
	addnav("Edytor Kamieni", "eqstonesedit.php");
	boss_help();
} elseif ($op == "reset") {
	output("`n`n`^[Not implemented].`0");
	addnav("Edytor Kamieni", "eqstonesedit.php");
}

function boss_help()
{
  //output("`nPomocna pomoc dla edytora bossow`n`n");
}

page_footer();
?>
