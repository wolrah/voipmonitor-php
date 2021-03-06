<?php
require_once('config.inc.php');

// Open MySQL connection
$db = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($db->connect_errno) {
    echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
}

$callid = $_GET['id'];

if (!$callid) {
    die("No call ID specified!");
}

$sql = "SELECT cdr.id, cdr_next.fbasename, cdr.calldate, cdr.caller, cdr.called FROM `cdr` LEFT JOIN cdr_next ON cdr.id = cdr_next.cdr_ID WHERE cdr.id = $callid";

if(!$result = $db->query($sql)){
    die('There was an error running the query [' . $db->error . ']');
}

$row = $result->fetch_assoc();

$calldatelong = $row['calldate'];
$calltime = strtotime($calldatelong);
$callday = date('Y-m-d', $calltime);
$calltimestamp = date('YmdHis', $calltime);
$caller = $row['caller'];
$called = $row['called'];

$wavfile = $wav_path . "{$callday}/" . $row['fbasename'] . '.wav';

$outfilename = "{$calltimestamp}-{$caller}-to-{$called}.wav";

//echo "$calldatelong $calltime $callday $calltimestamp $caller $called $wavfile $outfilename";

if (file_exists($wavfile)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$outfilename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($wavfile));
    ob_clean();
    flush();
    readfile($wavfile);
    exit;
} else {
die("File {$wavfile} does not exist.");
}
?>
