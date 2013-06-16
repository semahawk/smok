<?php

require_once("lib/villagenav.php");

function hangman_getmoduleinfo()
{
  $info = array(
    "name" => "Hangman",
    "version" => "1.0.0",
    "author" => "`i`gD`Ka`Tv`Ey Bow`i`ees",
    "category" => "Village",
    "download" => "example.com",
    "settings" => array (
      "The Hangman Game,title",
      "chances" => "Number of chances,int|10",
      //"location" => "Location,location|" . getsetting("Degolburg" . LOCATION_FIELDS) . ""
    ),
    "prefs" => array (
      "User Hangman prefs,title",
      "word" => "Word to be guessed,text",
      "guessed_letters" => "Already guessed letters,text",
      "chances" => "Number of chances,int|" . get_module_setting("chances")
    )
  );

  return $info;
}

function hangman_install()
{
  module_addhook("village");

  return true;
}

function hangman_uninstall()
{
  return true;
}

function hangman_dohook($hookname, $args)
{
  if ($session['user']['location'] == get_module_setting("location")){
    tlschema($args['schemas']['marketnav']);
    addnav($args['marketnav']);
    addnav("Wisielec","runmodule.php?module=hangman&op=enter");
  }

  return $args;
}

function hangman_runevent($type, $link)
{
  // NULL
}

function hangman_run()
{
  global $session;

  $op = httpget('op');
  $here = "runmodule.php?module=hangman";
  $chances = get_module_setting("chances");
  $word = get_module_pref("word");
  $failed = false;
  $money_to_win  = 200;
  $money_to_lose = 1000;

  page_header("Gra w Wisielca");

  // Getting the word list
  require_once("modules/hangman/hangman_words.php");
  $words_size = count($hangman_words);
  // Picking up some random word
  $word = $hangman_words[e_rand(0, $words_size - 1)];
  $len = strlen($word);
  //$guessed_letters = get_module_pref("guessed_letters");
  $guessed_letters = "";

  output("`n`c`b`\$Wisielec`b`c`n`n");
  output("`jTuz za rzeźni± wkraczasz w pewien ciemny zaułek, który prowadzi parę metrów w głąb ciemnej uliczki. Nagle na ścianie widzisz jakieś dziwne malunki. \"Krew!\", myślisz sobie. Po chwili stwierdzasz, że to jedynie czerwona farba. Dostrzegasz, że kreski zaczynają świecić lekką otoczką. ");

  if ($op == "giveup"){
    // Reset the prefs
    output("`n`n`n`c`b`\$Niestety, nie udało się!`b`n`n");
    $guessed_letters_size = count(explode(";", get_module_pref("guessed_letters")));
    $tmp = round((get_module_pref("chances") / 5) / strlen(get_module_pref("word")) / ($guessed_letters_size / 2), 2);
    $tmp2 += e_rand(1, 99);
    //$tmp = round((get_module_pref("chances")) - strlen(get_module_pref("word")) - $guessed_letters_size, 0);
    //$tmp /= 10;
    //$money_to_lose *= $tmp;
    output("tmptmp $tmp tmptmp");
    output("`4Tracisz $money_to_lose złota.`c");
    set_module_pref("guessed_letters", "");
    set_module_pref("word", "");
    set_module_pref("chances", get_module_setting("chances"));
    addnav("Nowe słowo", $here);
    villagenav();
    page_footer();
  }

  if ($op == "guess"){
    $chances = get_module_pref("chances");
    $word = get_module_pref("word");
    $guessed_letters = get_module_pref("guessed_letters");
    $letter = httppost("letter");
    $len = strlen($word);
    $guessed_letters .= "$letter;";
    set_module_pref("guessed_letters", $guessed_letters);

    // Missed :C
    if (!letterhit($word, $letter)){
      $chances--;
    }
  }

  // We have some chances yet
  if ($chances > 0){
    output("`jJakiś cichy głos podpowiada Ci, że masz ");
    // Do some polishing
    if ($chances >= 5)
      output("jeszcze `&$chances `jszans");
    elseif ($chances >= 2)
      output("już tylko `&$chances `jszanse");
    else
      output("`&ostatnią `jszansę");
    output(". Na ścianie widzisz:`n`n`n");

    // Print the already guessed letters
    if ($guessed_letters != ""){
      output("`jJuż odgadywane literki:");
      foreach (explode(";", $guessed_letters) as $l){
        output("`g$l`j ");
      }
    } else {
      output("`n");
    }

    output("`c`b`\$Słowo: $word`n`n`g");

    $allhit = true;
    // Explode the already guessed letters
    $exploded = explode(";", $guessed_letters);
    // Go through the word string
    for ($i = 0; $i < $len; $i++){
      $found = false;
      foreach ($exploded as $l){
        if ($word[$i] == $l){
          $found = true;
        }
      }

      if ($found){
        output($word[$i] . " ");
      } else {
        output("_ ");
        $allhit = false;
      }
    }

    // Check if we got it!
    if ($allhit){
      output("`n`n`n`@Brawo, udało się!`b");
      $money_to_win *= $chances / 100;
      output("`n`n`2Otrzymujesz $money_to_win złota!`c");
      // Reset the prefs
      set_module_pref("guessed_letters", "");
      set_module_pref("word", "");
      set_module_pref("chances", get_module_setting("chances"));
    } else {
      // It's split into two, so the $chances variable doesn't get confused
      // and we see the "Hit!" in some nice place
      if ($op == "guess"){
        if (letterhit($word, $letter)){
          if ($session['user']['sex'] == SEX_MALE)
            output("`n`n`n`@Trafiłeś!");
          else
            output("`n`n`n`@Trafiłaś!");
        } else {
          output("`n`n`n`\$Pudło!");
        }
      } else {
        output("`n`n`n");
      }

      output("`b`n`n`n`n");
      rawoutput("<form action='$here&op=guess' method='POST'>
                   <button name='letter' value='q' disabled>Q</button>
                   <button name='letter' value='w'>W</button>
                   <button name='letter' value='e'>E</button>
                   <button name='letter' value='r'>R</button>
                   <button name='letter' value='t'>T</button>
                   <button name='letter' value='y'>Y</button>
                   <button name='letter' value='u'>U</button>
                   <button name='letter' value='i'>I</button>
                   <button name='letter' value='o'>O</button>
                   <button name='letter' value='p'>P</button>
                   <br>
                   <button name='letter' value='a'>A</button>
                   <button name='letter' value='s'>S</button>
                   <button name='letter' value='d'>D</button>
                   <button name='letter' value='f'>F</button>
                   <button name='letter' value='g'>G</button>
                   <button name='letter' value='h'>H</button>
                   <button name='letter' value='j'>J</button>
                   <button name='letter' value='k'>K</button>
                   <button name='letter' value='l'>L</button>
                   <br>
                   <button name='letter' value='z'>Z</button>
                   <button name='letter' value='x' disabled>X</button>
                   <button name='letter' value='c'>C</button>
                   <button name='letter' value='v' disabled>V</button>
                   <button name='letter' value='b'>B</button>
                   <button name='letter' value='n'>N</button>
                   <button name='letter' value='m'>M</button>
                   <br>
                   <button name='letter' value='ą'>Ą</button>
                   <button name='letter' value='ć'>Ć</button>
                   <button name='letter' value='ę'>Ę</button>
                   <button name='letter' value='ł'>Ł</button>
                   <button name='letter' value='ń'>Ń</button>
                   <button name='letter' value='ó'>Ó</button>
                   <button name='letter' value='ż'>Ż</button>
                   <button name='letter' value='ź'>Ź</button>
                 </form>");
      set_module_pref("guessed_letters", $guessed_letters);
      set_module_pref("chances", $chances);
      set_module_pref("word", $word);
      addnav("", "$here&op=guess");
      output("`c");
    }

  // OH NOES, out of chancez!
  } else {
    $failed = true;
    // Reset the prefs
    set_module_pref("guessed_letters", "");
    set_module_pref("word", "");
    set_module_pref("chances", get_module_setting("chances"));
    output("`n`n`n`c`\$Niestety, nie udało się!`n`n");
    output("Tracisz $money_to_lose złota.`c");
    addnav("Nowe słowo", $here);
    villagenav();
    page_footer();
    //output("`c`n`n`b`\$Niestety!`b`n");
    //output("`4Nie udało się! Może następnym razem?`n`n`c");
    //// Reset the prefs
    //set_module_pref("guessed_letters", "");
    //set_module_pref("word", "");
    //set_module_pref("chances", get_module_setting("chances"));
  }

  // If user has guessed the word, don't make him go through one more page
  if ($allhit || $failed){
    addnav("Nowe słowo", "$here");
    villagenav();
  } else
    addnav("Poddaj się", "$here&op=giveup");
  // Go back to village
  //villagenav();

  page_footer();
}

function letterhit($word, $letter)
{
  for ($i = 0; $i < strlen($word); $i++){
    if ($word[$i] === $letter)
      return true;
  }

  return false;
}

?>
