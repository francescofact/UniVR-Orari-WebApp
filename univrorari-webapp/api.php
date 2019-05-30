<?php
date_default_timezone_set('Europe/Rome');

//continua il codice

$what = $_GET['w'];
$debug = $_GET['debug'];
if ($what == "getweek"){

  $corso = $_GET['c'];
  $anno = $_GET['a']; //primo dei due quando indichi anno scolastico
  $anno2 = $_GET['a2'];

  $json = file_get_contents("https://logistica.univr.it/PortaleStudentiUnivr/grid_call.php?form-type=corso&aa=".$anno."&cdl=420&anno=".$anno."&corso=".$corso."&anno2=".$anno2."&date=".date("d-m-Y")."&_lang=it&all_events=0");

  $obj = json_decode($json, true);

  $lezioni = array();
  for($i=0; $i<7; $i++){
    foreach ($obj["celle"] as $cella){
      if ($cella["giorno"] == $i){
        $lezione = array($cella["nome_insegnamento"], $cella["aula"], $cella["ora_inizio"], $cella["ora_fine"], $cella["docente"], $cella["giorno"]);
        array_push($lezioni, $lezione);
      }
    }
  }

  $json = json_encode($lezioni, JSON_PRETTY_PRINT);
  print $json;
} else if ($what == "getyears"){
  $obj = getMyComboCall("years", "");
  $json = json_encode($obj, JSON_PRETTY_PRINT);
  $anni = array();
  foreach ($obj[0] as $anno){
    $elem = array($anno["label"], $anno["valore"]);
    array_push($anni, $elem);
  }
  $json = json_encode($anni, JSON_PRETTY_PRINT);

  print $json;

} else if ($what == "getcourses"){
  $year = $_GET['year'];

  $obj = getMyComboCall("courses", $year);

  $corsi = array();
  foreach ($obj as $anno){
    if ($anno["valore"] == $year){
      foreach ($anno["elenco"] as $corso){
        $elem = array($corso["label"], $corso["valore"], $corso["elenco_anni"]);
        array_push($corsi, $elem);
      }
    }
  }

  $json = json_encode($corsi, JSON_PRETTY_PRINT);

  print $json;

} else if ($what == "getyears2"){
  $year = $_GET['year'];
  $course = $_GET['course'];

  $obj = getMyComboCall("", "");

  $years = array();
  foreach ($obj as $anno){
    if ($anno["valore"] == $year){
      foreach ($anno["elenco"] as $corso){
        if ($corso["valore"] == $course){
          foreach($corso['elenco_anni'] as $elenco_anno){
            $elem = array($elenco_anno["label"], $elenco_anno["valore"]);
            array_push($years, $elem);
          }
        }
      }
    }
  }

  $json = json_encode($years, JSON_PRETTY_PRINT);

  print $json;
} else if ($what = "all"){
  print json_encode(getMyComboCall("", ""));
} else {
  echo "no param what";
}


//La mia funzione per recuperare e modificare il JSON da combo_call.php
function getMyComboCall($param1, $param2){
  $json = "";
  if ($param1 == "years"){
  	$json = file_get_contents("https://logistica.univr.it/PortaleStudentiUnivr/combo_call.php?aa=1");
	  $json = str_replace("var anni_accademici_ec = ", "", $json);
    $json = rtrim($json,";");
    $json = "[".str_replace("}}{", "}},{", $json)."]";
  } else if ($param1 == "courses"){
  	$json = file_get_contents("https://logistica.univr.it/PortaleStudentiUnivr/combo_call.php?sw=ec_&aa=".$param2."&page=corsi");
	   //rimuovo le variabili js e converto per avere un json e un array
    $json = str_replace("var elenco_corsi = ", "", $json);
    $jj = explode('var ec_settimana_standard_cdl', $json);
    $json = substr($jj[0], 0, -2);
  }


  return json_decode($json, true);
}
?>
