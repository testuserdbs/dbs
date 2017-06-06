<?php 
//Diese Library erlaubt es uns csv Dateien leicht zu manipulieren.
//https://github.com/parsecsv/parsecsv-for-php
//Zeilen 851 bis 853 mussten wir jedoch auskommentieren. Wir glauben, die gehören da gar nicht hin.
require_once '/home/celvic/DBS/codes/parsecsv.lib.php';

//Wenn der Postgresql-Treiber für PHP installiert und aktiviert ist, wird mit folgender Zeile eine Verbindung aufgebaut:
$dbo = new PDO('pgsql:dbname=twitter;   host=localhost;   user=postgres;   password=postgres');

//Diese Funktion fügt die eine Tweet-Zeile in die Datenbank ein
function addTweetInfo($dbo, $row) {
  $statement = $dbo->prepare( // An dieser Stelle benutzen wir prepared statements ohne Namen. ? sind Platzhalter für die eigentlichen Werte.
		"INSERT INTO public.\"Tweet\" 
	   (text, t_time, favorite_count, retweet_count)
	   VALUES (?, ?::timestamp, ?, ?)" // ::timestamp wird benutzt, um einen String zu einem timestamp zu konvertieren
	);
	return $statement->execute($row); // Die Platzhalter in der Abfrage werden der Reihe nach mit den Werten aus dem Array ersetzt. 
}

$csv = new parseCSV();
$csv->delimiter = ";"; // CSV's können mit unterschiedlichen Trennern benutzt werden. Wir brauchen das Semikolon.
$csv->encoding('windows-1252','UTF-8'); //Wir wandeln in ein besseres Zeichenformat um. ANSI ist in dem Fall windows-1252.
$csv->parse('/home/celvic/DBS/codes/american-election-tweets.csv');

$new = array();
foreach($csv->data as $row) {
  // Wir benutzen SANITIZE_FILTER von PHP. FILTER_UNSAFE_ROW tut nichts, was nicht explizit als Parameter genannt wird.
  // Wir geben die Parameter FILTER_FLAG_STRIP_[LOW/HIGH] an.
  // Näheres dazu: http://nl1.php.net/manual/en/filter.filters.flags.php
  // Wir löschen alle Zeichen, deren numerischer Wert unter 32 und über 127 liegt. Alle üblichen Zeichen (die man so auf
  // der Tastatur findet, werden erhalten) Näheres: http://www.columbia.edu/kermit/ascii.html
  $text = filter_var($row['text'],FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
  $time = str_replace("T"," ", $row['time']); // ISO 8601 Format, ohne T (YYYY-MM-DD hh:mm:ss)
  // Dann füttern wir das neue Array mit den gefilterten und gereinigten Daten
  $curarr = array($text, $time, $row['retweet_count'], $row['favorite_count']); 
  $new[] = $curarr;
  // Für den Datenimport benutzen wir außerdem diese Funktion, da wir die aktuelle Zeile jetzt auch in die Datenbank einführen können.
  addTweetInfo($dbo, $curarr);
}

// An dieser Stelle schreiben wir die neue, bereinigte CSV
// Erster Parameter ist der Name; der Zweite ist das Array, das die Daten enthält. Der Dritte gibt an, ob die CSV überschrieben werden soll
// und der letzte übergibt ein Array mit den neuen Headern.
$csv->save("nfile.csv",$new,false,array("text","time","retweet_count", "favorite_count"));

// Diese Funktion schaut mittels RegularExpressions nach durch Hashtags markierte Worte und liefert sie in einem Array zurück
function getHashtags($string) {
  $output = array();
  $pattern = " (#[^\.\s#]+) "; // Dieses RegEx filtert die Tags
  preg_match_all($pattern, $string, $output, PREG_PATTERN_ORDER); // Die Funktion ersetzt den aktuellen Wert der Variablen $output.
  return $output[0]; // Das Array hat eine Tiefe zu viel, daher geben wir das erste Element zurück und erhalten das gewünschte Ergebnis.
}
?>

