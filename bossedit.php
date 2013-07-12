<?php

/*
 * Author: Ciuf
 * Heavily based on bossedit.php by Lonny Luberts / JT Traub
 */

require_once("common.php");
require_once("lib/http.php");

check_su_access(SU_EDIT_USERS);

tlschema("bossedit");

page_header("Boss Editor");

$op = httpget('op');
$id = httpget('id');

$editarray = array(
	"Bosses,title",
  "bossname" => "Nazwa bossa,text|",
  "bossdesc_before" => "Tekst przed walka,text|",
  "bossdesc_after" => "Tekst po walce,text|",
  "bossweapon" => "Bron bossa,text|",
  "bosslocation" => "Gdzie mozna go spotkac,text|",
);

addnav("Inne");
require_once("lib/superusernav.php");
superusernav();
addnav("Funkcje");

if ($op == "save") {
  $name = httppost('bossname');
  $desc_before = httppost('bossdesc_before');
  $desc_after = httppost('bossdesc_after');
  $weapon = httppost('bossweapon');
  $location = httppost('bosslocation');
  // Ref is currently unused
  // $ref = httppost('ref');
  $ref = '';

  if ((int)$id == 0) {
    $sql = "INSERT INTO " . db_prefix("bosses") . " (bossid, bossname, bossdesc_before, bossdesc_after, bossweapon, bosslocation) VALUES ($id, '$name', '$desc_before', '$desc_after', '$weapon', '$location');";
    $note = "`^Dodano bossa.`0";
    $errnote = "`\$Problem z dodaniem bossa.`0";
  } else {
    $sql = "UPDATE " . db_prefix("bosses") . " SET bossid=$id, bossname='$name', bossdesc_before='$desc_before', bossdesc_after='$desc_after', bossweapon='$weapon', bosslocation='$location' WHERE bossid=$id";
    $note = "`^Zmodyfikowano bossa.`0";
    $errnote = "`\$Problem z modyfikacja bossa.`0";
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
  $sql = "DELETE FROM " . db_prefix("bosses") . " WHERE bossid = '$id'";
  $query = db_query($sql);
  output("`^Usunieto bossa.`0");
	$op = "";
}

if ($op == ""){
  $sql = "SELECT * FROM " . db_prefix("bosses") . " ORDER BY bossid ASC;";
  $query = db_query($sql);
  output("`@`c`b-= Edytor Bossow =-`b`c");
  rawoutput("<table border='0' cellspacing='0' cellpadding='2' width='100%' align='center'>");
  rawoutput("<tr class='trhead'><td>Akcja</td><td>Nazwa</td><td>Opis przed spotkaniem</td><td>Opis po zabiciu</td><td>Bron</td><td>Lokacja</td></tr>");
  $i = 0;
  while ($row = db_fetch_assoc($query)){
    $id = $row['bossid'];
    rawoutput("<tr class='" . ($i % 2 ? "trlight" : "trdark") . "'>");
    addnav("", "bossedit.php?op=edit&id=$id");
    addnav("", "bossedit.php?op=delete&id=$id");
    rawoutput("<td>[<a href='bossedit.php?op=edit&id=$id'>Edytuj</a> | <a href='bossedit.php?op=delete&id=$id' onClick='return confirm(\"Na pewno usunac?\");'>Usun</a>]</td>");
    rawoutput("<td>$row[bossname]</td>");
    rawoutput("<td>$row[bossdesc_before]</td>");
    rawoutput("<td>$row[bossdesc_after]</td>");
    rawoutput("<td>$row[bossweapon]</td>");
    rawoutput("<td>$row[bosslocation]</td>");
    rawoutput("</tr>");
    $i++;
  }
	addnav("Funkcje");
	addnav("Dodaj Bossa", "bossedit.php?op=add");
	addnav("Odswiez", "bossedit.php");
	addnav("Resetuj Bossy", "bossedit.php?op=reset");
	boss_help();
} elseif ($op == "edit" || $op == "add") {
	require_once("lib/showform.php");
  if ($op == "edit"){
    $sql = "SELECT * FROM " . db_prefix("bosses") . " WHERE bossid = '$id'";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
  } elseif ($op == "add") {
    $row = array("bossname" => "", "bossdesc_before" => "", "bossdesc_after" => "", "bosslocation" => "", "bossweapon" => "");
    $id = 0;
  }
  rawoutput("<form action='bossedit.php?op=save&id=$id' method='POST'>");
  addnav("", "bossedit.php?op=save&id=$id");
  showform($editarray, $row);
  rawoutput("</form>");
	addnav("Functions");
	addnav("Edytor Bossow", "bossedit.php");
	boss_help();
} elseif ($op == "reset") {
	output("`n`n`^[Not implemented].`0");
	addnav("Edytor Bossow", "bossedit.php");
}

function boss_help()
{
  //output("`nPomocna pomoc dla edytora bossow`n`n");
}

page_footer();
?>
