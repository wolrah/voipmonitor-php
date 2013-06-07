<?php
// Include configuration
require_once('config.inc.php');

// Open MySQL connection
$db = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($db->connect_errno) {
    echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
}

// What's today's date?
$today = date('Ymd');

// Is this a later page?
if ($_GET['date']) {
    $pagedate = $_GET['date'];
} else {
    $pagedate = $today;
}

$pagedatetime = strtotime($pagedate);

$nextdate = date('Ymd', strtotime('+1 day', $pagedatetime));
$prevdate = date('Ymd', strtotime('-1 day', $pagedatetime));

$sortkeys = array(
    0 => "calldate",
    "caller",
    "called"
);

$sortdirs = array(
    0 => "DESC",
    "ASC"
);

if ($_GET['sort']) {
    $sortkey = $sortkeys[$_GET['sort']];
    $sortkeynum = $_GET['sort'];
} else {
    $sortkey = 'calldate';
    $sortkeynum = 0;
}

if ($_GET['dir']) {
    $sortdir = $sortdirs[$_GET['dir']];
    $sortdirnum = $_GET['dir'];
} else {
    $sortdir = 'DESC';
    $sortdirnum = 0;
}
if ($_GET['filter']) {
    $filternum = $_GET['filter'];
    switch ($_GET['filter']) {
        case 0:
            $filter = '';
            break;
        case 1:
            $filter = "AND (LENGTH(caller) > $exten_len AND LENGTH(called) = $exten_len)";
            break;
        case 2:
            $filter = "AND (LENGTH(caller) = $exten_len AND LENGTH(called) > $exten_len)";
            break;
        case 3:
            $filter = "AND (LENGTH(caller) = $exten_len AND LENGTH(called) = $exten_len)";
            break;
        default:
            die('Bad filter value');
    }

} else {
    $filternum = 0;
    $filter = '';
}

// Ok let's query some data
$sql = "SELECT `ID`, `calldate`, `callend`, `duration`, `connect_duration`, `caller`, `callername`, `called`, `whohanged` FROM `cdr` WHERE `connect_duration` > 0 {$filter} AND DATE(calldate) = \"{$pagedate}\" ORDER BY `{$sortkey}` {$sortdir}";

if(!$result = $db->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
}

if(!$rowsult = $db->query("SELECT COUNT(*) FROM `cdr` WHERE `connect_duration` > 0 AND DATE(calldate) = \"{$pagedate}\"")){
    die('There was an error running the query [' . $db->error . ']');
}
$numrowarray = $rowsult->fetch_row();
$numrows = $numrowarray[0];

$prevpage = $startcallid - $limit;
if ($prevpage < 0) {
    $prevpage = 0;
}

function sec2hms ($sec, $padHours = false) {
    $hms = "";
    $hours = intval(intval($sec) / 3600);
    $hms .= ($padHours)
    ? str_pad($hours, 2, "0", STR_PAD_LEFT). ':'
    : $hours. ':';
    $minutes = intval(($sec / 60) % 60);
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ':';
    $seconds = intval($sec % 60);
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
    return $hms;
}

function sortbuttons ($sortkey) {
    echo "<a href=\"?date={$pagedate}&sort={$sortkey}&dir=0&filter={$filternum}\"><img src=\"images/arrow_up.png\" /></a>";
    echo "<a href=\"?date={$pagedate}&sort={$sortkey}&dir=1&filter={$filternum}\"><img src=\"images/arrow_down.png\" /></a>";
    return TRUE;
}
?>
<!doctype html>
<html lang=en>
<head>
<meta charset=utf-8>
<style type="text/css">
table {font-size:12px;color:#333333;width:100%;border-width: 1px;border-color: #a9a9a9;border-collapse: collapse;}
table th {font-size:12px;background-color:#b8b8b8;border-width: 1px;padding: 8px;border-style: solid;border-color: #a9a9a9;text-align:left;}
table tr {background-color:#ffffff;}
table td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;border-color: #a9a9a9;}
</style>
<title>Call Recordings</title>
</head>
<body>
<p align="center">Filter Calls: 
<?php
switch ($filternum) {
	case 0:
		echo "<a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=0\">ALL</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=1\">Incoming</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=2\">Outgoing</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=3\">Internal</a>";
		break;
	case 1:
		echo "<a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=0\">All</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=1\">INCOMING</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=2\">Outgoing</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=3\">Internal</a>";
		break;
	case 2:
		echo "<a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=0\">All</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=1\">Incoming</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=2\">OUTGOING</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=3\">Internal</a>";
		break;
	case 3:
		echo "<a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=0\">All</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=1\">Incoming</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=2\">Outgoing</a> | <a href=\"?date={$pagedate}&sort={$sortkeynum}&dir={$sortdirnum}&filter=3\">INTERNAL</a>";
		break;
}
?>
</p>

<table border="1">
<tr><th>Call Time<?php sortbuttons(0); ?></th><th>WAV</th><th>Call Duration</th><th>Caller<?php sortbuttons(1); ?></th><th>Callee<?php sortbuttons(2); ?></th><th>Terminating Party</th></tr>
<?php
while($row = $result->fetch_assoc()){
    $callid = $row['ID'];
    $calldate = $row['calldate'];
    $calllen = sec2hms($row['connect_duration']);
    $callorig = $row['caller'];
    $callorigname = $row['callername'];
    if ($callorigname != $callorig) {
        $callcnam = "{$callorigname} ({$callorig})";
    } else {
        $callcnam = $callorig;
    }
    $callterm = $row['called'];
    $callhang = $row['whohanged'];
    echo "<tr><td>{$calldate}</td><td><a href=\"getwav.php?id={$callid}\"><img src=\"images/sound.png\" /></a></td><td>{$calllen}</td><td>{$callcnam}</td><td>{$callterm}</td><td>{$callhang}</td></tr>";
}
?>
<tr><td>
<?php
if (strtotime($nextdate) <= strtotime($today)) {
    echo "<a href=\"?date={$nextdate}&sort={$sortkeynum}&dir={$sortdirnum}\">Newer</a>";
} else {
    echo "Newer";
}
?>
</td><td colspan="4"><?php echo "{$numrows} calls for {$pagedate}" ?></td><td>
<?php echo "<a href=\"?date={$prevdate}&sort={$sortkeynum}&dir={$sortdirnum}\">Older</a>"; ?>
</td></tr>
</table>
</body>
</html>
