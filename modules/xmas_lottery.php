<?php

require_once("lib/villagenav.php");

function xmas_lottery_getmoduleinfo()
{
  $info = array(
    "name" => "Christmas Lottery",
    "version" => "0.1.0",
    "author" => "Davy Bowes",
    "category" => "Village",
    "download" => "example.com"
  );

  return $info;
}

function xmas_lottery_install()
{
  module_addhook("village");

  return true;
}

function xmas_lottery_uninstall()
{
  return true;
}

function xmas_lottery_dohook($hookname, $args)
{
  tlschema($args['schemas']['marketnav']);
  addnav($args['marketnav']);
  tlschema();
  addnav(array("XMAS!"), "runmodule.php?module=xmas_lottery");
  return $args;
}

function xmas_lottery_runevent($type, $link)
{
  //xmas_lottery_doit($type);
}

function xmas_lottery_run()
{
  global $session;

  $op = httpget('op');
  $here = "runmodule.php?module=xmas_lottery";
  $session['user']['specialinc'] = "module:xmas_lottery";

  page_header("Christmas lottery!");
  output("It's a lottery, yeah. %s", $session['user']['name']);
  addnav("Testish");
  addnav(array("Refresh"), $here);
  addnav("Return");
  villagenav();
  page_footer();

  if ($op == "run"){
    $session['user']['specialinc'] = "";
  }
}

?>
