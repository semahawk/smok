<?php
// addnews ready
// translator ready
// mail ready
function reltime($date,$short=true){
	$now = strtotime("now");
	$x = abs($now - $date);
	$d = (int)($x/86400);
	$x = $x % 86400;
	$h = (int)($x/3600);
	$x = $x % 3600;
	$m = (int)($x/60);
	$x = $x % 60;
	$s = (int)($x);
	if ($short){
		if ($d > 0)
			$o = $d."d".($h>0?$h."h":"");
		elseif ($h > 0)
			$o = $h."h".($m>0?$m."m":"");
		elseif ($m > 0)
			$o = $m."m".($s>0?$s."s":"");
		else
			$o = $s."s";
	}else{
		if ($d > 0)
			$o = "$d day".($d>1?"s":"").($h>0?", $h hour".($h>1?"s":""):"");
		elseif ($h > 0)
			$o = "$h hour".($h>1?"s":"").($m>0?", $m minute".($m>1?"s":""):"");
		elseif ($m > 0)
			$o = "$m minute".($m>1?"s":"").($s>0?", $s second".($s>1?"s":""):"");
		else
			$o = $s." second".($s>0?"s":"");
	}
	return $o;
}

function relativedate($indate){
	$laston = round((strtotime("now")-strtotime($indate)) / 86400,0) . " days";
	if (substr($laston,0,2)=="1 ")
		$laston=translate_inline("1 day");
	elseif (date("Y-m-d",strtotime($laston)) == date("Y-m-d"))
		$laston=translate_inline("Today");
	elseif (date("Y-m-d",strtotime($laston)) == date("Y-m-d",strtotime("-1 day")))
		$laston=translate_inline("Yesterday");
	elseif (strpos($indate,"0000-00-00")!==false)
		$laston = translate_inline("Never");
	else {
		$laston= sprintf_translate("%s days", round((strtotime("now")-strtotime($indate)) / 86400,0));
		rawoutput(tlbutton_clear());
	}
	return $laston;
}

function checkday() {
	global $session,$revertsession,$REQUEST_URI;
  /* Ciuf { */
  $sql = "SELECT dragonkills FROM " . db_prefix("accounts") . " ORDER BY dragonkills DESC LIMIT 1";
  $result = db_query($sql);
  $row = db_fetch_assoc($result);
  $max_dk = $row["dragonkills"];
  $dk_needed = get_module_setting("dk_needed", "final");
  /* Ciuf } */
	if ($session['user']['loggedin']){
		output_notl("<!--CheckNewDay()-->",true);
		if(is_new_day()){
			$session=$revertsession;
			$session['user']['restorepage']=$REQUEST_URI;
			$session['allowednavs']=array();
			addnav("","newday.php");
			redirect("newday.php");
    }
    /* Ciuf { */
    else if($max_dk >= $dk_needed){
      output("`EW tym miejscu powinien wyskoczyc `gSMOK`E! (limit `E$dk_needed`e bossow zostal osiagniety (`E$max_dk`e))`n`n");
      addnav("","runmodule.php?module=final");
      rawoutput("<a href='runmodule.php?module=final'>DO SMOKA</a>");
    }
    /* Ciuf } */
	}
}

function is_new_day($now=0){
	global $session;

	if ($session['user']['lasthit'] == "0000-00-00 00:00:00") {
		return true;
	}
	$t1 = gametime();
	$t2 = convertgametime(strtotime($session['user']['lasthit']." +0000"));
	$d1 = gmdate("Y-m-d",$t1);
	$d2 = gmdate("Y-m-d",$t2);

	if ($d1!=$d2){
		return true;
	}
	return false;
}

function getgametime(){
	return gmdate("g:i a",gametime());
}

function gametime(){
	$time = convertgametime(strtotime("now"));
	return $time;
}

function convertgametime($intime,$debug=false){

	//adjust the requested time by the game offset
	$intime -= getsetting("gameoffsetseconds",0);

	// we know that strtotime gives us an identical timestamp for
	// everywhere in the world at the same time, if it is provided with
	// the GMT offset:
	$epoch = strtotime(getsetting("game_epoch",gmdate("Y-m-d 00:00:00 O",strtotime("-30 days"))));
	$now = strtotime(gmdate("Y-m-d H:i:s O",$intime));
	$logd_timestamp = ($now - $epoch) * getsetting("daysperday",4);
	if ($debug){
		echo "Game Timestamp: ".$logd_timestamp.", which makes it ".gmdate("Y-m-d H:i:s",$logd_timestamp)."<br>";
	}
	return $logd_timestamp;
}

function gametimedetails(){
	$ret = array();
	$ret['now'] = date("Y-m-d 00:00:00");
	$ret['gametime'] = gametime();
	$ret['daysperday'] = getsetting("daysperday", 4);
	$ret['secsperday'] = 86400/$ret['daysperday'];
	$ret['today'] = strtotime(gmdate("Y-m-d 00:00:00 O", $ret['gametime']));
	$ret['tomorrow'] =
		strtotime(gmdate("Y-m-d H:i:s O",$ret['gametime'])." + 1 day");
	$ret['tomorrow'] = strtotime(gmdate("Y-m-d 00:00:00 O",$ret['tomorrow']));
	// Why isn't this
	// $ret['tomorrow'] =
	//	strtotime(gmdate("Y-m-d 00:00:00 O",$ret['gametime'])." + 1 day");
	$ret['secssofartoday'] = $ret['gametime'] - $ret['today'];
	$ret['secstotomorrow'] = $ret['tomorrow']-$ret['gametime'];
	$ret['realsecssofartoday'] = $ret['secssofartoday'] / $ret['daysperday'];
	$ret['realsecstotomorrow'] = $ret['secstotomorrow'] / $ret['daysperday'];
	$ret['dayduration'] = ($ret['tomorrow']-$ret['today'])/$ret['daysperday'];
	return $ret;
}

function secondstonextgameday($details=false) {
	if ($details===false) $details = gametimedetails();
	return strtotime("{$details['now']} + {$details['realsecstotomorrow']} seconds");
}

function getmicrotime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}


?>
