<?php

// Db connection data
$dbuser = "diyidprw";
$dbpass = "teip4Izaesee";
$dbhost = "localhost";

function doQuery($qryString, $dbuser, $dbpass, $dbhost) {

	// Make a MySQL Connection
	mysql_connect($dbhost, $dbuser, $dbpass) or die(mysql_error());

	// Retrieve the data
	$setUTF8 = mysql_query("SET NAMES utf8");
	$result = mysql_query($qryString) or die(mysql_error());
	
	return $result;
}

function mkCSV($rset, $datasetname){

	$now = new DateTime("", new DateTimeZone('Europe/Amsterdam'));

	// Add query description and publish date
	//$csvresult = '{"'.$datasetname.'" : ' . json_encode($rows) . ',"pubdate": "'. $now->format("d-m-Y H:i:s") .'"}';

	return convertResult($rset, "csv");
}

function mkXML($rset, $datasetname, $depth = 0){
    
    $now = new DateTime("", new DateTimeZone('Europe/Amsterdam'));

    $rows = array();
	while($r = mysql_fetch_assoc($rset)) {
	$rows[] = $r;
    }
    return "<?xml version='1.0' ?>\n<DATA>\n" . ARRAYtoXML($rows) . "</DATA>";
}

function convertResult($rs, $type, $jsonmain="") {
	// receive a recordset and convert it to csv, json (default) or to xml based on "type" parameter.
	$jsonArray = array();
	$csvString = "";
	$csvcolumns = "";
	$count = 0;
	$returndata = "";
	while($r = mysql_fetch_row($rs)) {
		for($k = 0; $k < count($r); $k++) {
			$jsonArray[$count][mysql_field_name($rs, $k)] = $r[$k];
			$csvString.=",\"".$r[$k]."\"";
		}
		if (!$csvcolumns) for($k = 0; $k < count($r); $k++) $csvcolumns.=($csvcolumns?",":"").mysql_field_name($rs, $k);
		$csvString.="\n";
		$count++;
	}

	switch ($type) {
	    case "csv":
		// CSV
		$returndata = str_replace("\n,","\n",$csvcolumns."\n".$csvString);
	    break;
	    
	    case "xml":
		// XML
		// TODO
		break;

	    default:
		// JSON
		$returndata = "{\"$jsonmain\":".json_encode($jsonArray)."}";
        }

	return ($returndata);
}


function mkJson($rset, $datasetname){
	
	$now = new DateTime("", new DateTimeZone('Europe/Amsterdam'));

	$rows = array();
	while($r = mysql_fetch_assoc($rset)) {
		$rows[] = $r;
	}
	
	// Add query description and publish date
	$jsonresult = '{"'.$datasetname.'" : ' . json_encode($rows) . ',"pubdate": "'. $now->format("d-m-Y H:i:s") .'"}';
	
	return $jsonresult;
}

function mkArray($rset, $datasetname){

	$rows = array();
	while($r = mysql_fetch_assoc($rset)) {
		$rows[] = $r;
	}

	return $rows;
}

function mkHTML($rset){

	$data = mkArray($rset, "rSet");	
	$htmlTable = "";
	
	//$htmlTable = '<html><head><link href="https://'.$_SERVER['SERVER_NAME']."/stats/".$_SERVER['PATH_INFO'].'css/stats.css" rel="stylesheet" type="text/css" /></head><body>';				
		
	$htmlTable .= array2table($data);
	
	//$htmlTable .= "</body></html>";	

	return $htmlTable;
}

/**
 * Translate a result array into a HTML table
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.3.2
 * @link        http://aidanlister.com/2004/04/converting-arrays-to-human-readable-tables/
 * @param       array  $array      The result (numericaly keyed, associative inner) array.
 * @param       bool   $recursive  Recursively generate tables for multi-dimensional arrays
 * @param       string $null       String to output for blank cells
 */
function array2table($array, $recursive = false, $null = '&nbsp;', $bgcolor='#ccc;')
{
    // Sanity check
    if (empty($array) || !is_array($array)) {
        return false;
    }
 
    if (!isset($array[0]) || !is_array($array[0])) {
        $array = array($array);
    }
 
    // Start the table
    $table = "<table cellspacing='3' cellpadding='3'>\n";
 
    // The header
    $table .= "\t<tr>";
    // Take the keys from the first row as the headings
    foreach (array_keys($array[0]) as $heading) {
        $table .= '<th>' . $heading . '</th>';
    }
    $table .= "</tr>\n";

    // The body
	$x=0;
    foreach ($array as $row) {
		$x++; 
		$bgcolor = ($x%2 == 0)? '#FFFFFF': '#E0E0E0';
		
        $table .= "\t<tr bgcolor='". $bgcolor. "'>" ;
        foreach ($row as $cell) {
            $table .= '<td valign="top">';
 
            // Cast objects
            if (is_object($cell)) { $cell = (array) $cell; }
             
            if ($recursive === true && is_array($cell) && !empty($cell)) {
                // Recursive mode
                $table .= "\n" . array2table($cell, true, true) . "\n";
            } else {
                $table .= (strlen($cell) > 0) ?
                    htmlspecialchars((string) $cell) :
                    $null;
            }
 
            $table .= '</td>';
        }
 
        $table .= "</tr>\n";
    }
 
    $table .= '</table>';
    return $table;
}

	$sqlString = "SELECT 
	username as 'username',
	password as 'password',
	diy.uid as 'urn:oid:0.9.2342.19200300.100.1.1 (uid)',
	schacHomeOrganization as 'urn:oid:1.3.6.1.4.1.25178.1.2.9 (schacHomeOrganization)', 
	CONCAT(diy.uid, '@', schacHomeOrganization) as 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6 (eduPersonPrincipalName)',
	cn.cn as 'urn:oid:2.5.4.3 (cn)',
	gn.givenName as 'urn:oid:2.5.4.42 (givenName)',
	sn.sn as 'urn:oid:2.5.4.4 (sn)',
	dn.displayName as 'urn:oid:2.16.840.1.113730.3.1.241 (displayName)',
	mail.mail as 'urn:oid:0.9.2342.19200300.100.1.3 (mail)', 
	epa.eduPersonAffiliation as 'urn:oid:1.3.6.1.4.1.5923.1.1.1.1 (eduPersonAffiliation)',
	epe.eduPersonEntitlement as 'urn:oid:1.3.6.1.4.1.5923.1.1.1.7 (eduPersonEntitlement)',
	imo.isMemberOf as 'urn:oid:1.3.6.1.4.1.5923.1.5.1.1 (isMemberOf)',
        spuc.schacPersonalUniqueCode as 'urn:oid:1.3.6.1.4.1.25178.1.2.14 (schacPersonalUniqueCode)',
        epsa.eduPersonScopedAffiliation as 'urn:oid:1.3.6.1.4.1.5923.1.1.1.9 (eduPersonScopedAffiliation)'


	FROM diyidp.users diy
	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(cn SEPARATOR ', ') as cn from 
		( SELECT uid, cn FROM diyidp.users
		  GROUP BY uid, cn
		  ORDER BY UID
		) cn
		GROUP BY uid
	) AS cn
	ON diy.uid = cn.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(eduPersonEntitlement SEPARATOR ', ') as eduPersonEntitlement from 
		( SELECT uid, eduPersonEntitlement FROM diyidp.users
		  WHERE length(eduPersonEntitlement) <> 0
		  GROUP BY uid, eduPersonEntitlement
		  ORDER BY UID
		) epe
		GROUP BY uid
	) AS epe
	ON diy.uid = epe.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(displayName SEPARATOR ', ') as displayName from 
		( SELECT uid, displayName FROM diyidp.users
		  GROUP BY uid, displayName
		  ORDER BY UID
		) dn
		GROUP BY uid
	) AS dn
	ON diy.uid = dn.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(sn SEPARATOR ', ') as sn from 
		( SELECT uid, sn FROM diyidp.users
		  GROUP BY uid, sn
		  ORDER BY UID
		) sn
		GROUP BY uid
	) AS sn
	ON diy.uid = sn.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(givenName SEPARATOR ', ') as givenName from 
		( SELECT uid, givenName FROM diyidp.users
		  GROUP BY uid, givenName
		  ORDER BY UID
		) givenName
		GROUP BY uid
	) AS gn
	ON diy.uid = gn.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(mail SEPARATOR ', ') as mail from 
		( SELECT uid, mail FROM diyidp.users
		  GROUP BY uid, mail
		  ORDER BY UID
		) mail
		GROUP BY uid
	) AS mail
	ON diy.uid = mail.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(eduPersonAffiliation SEPARATOR ', ') as eduPersonAffiliation from 
		( SELECT uid, eduPersonAffiliation FROM diyidp.users
		  GROUP BY uid, eduPersonAffiliation
		  ORDER BY UID
		) eduPersonAffiliation
		GROUP BY uid
	) AS epa
	ON diy.uid = epa.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(isMemberOf SEPARATOR ', ') as isMemberOf from 
		( SELECT uid, isMemberOf FROM diyidp.users
		  GROUP BY uid, isMemberOf
		  ORDER BY UID
		) isMemberOf
		GROUP BY uid
	) AS imo
	ON diy.uid = imo.uid

	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(schacPersonalUniqueCode SEPARATOR ', ') as schacPersonalUniqueCode from 
		( SELECT uid, schacPersonalUniqueCode FROM diyidp.users
		  GROUP BY uid, schacPersonalUniqueCode
		  ORDER BY UID
		) schacPersonalUniqueCode
		GROUP BY uid
	) AS spuc
	ON diy.uid = spuc.uid
	
	LEFT JOIN
	(	SELECT uid, GROUP_CONCAT(eduPersonScopedAffiliation SEPARATOR ', ') as eduPersonScopedAffiliation from 
		( SELECT uid, eduPersonScopedAffiliation FROM diyidp.users
		  GROUP BY uid, eduPersonScopedAffiliation
		  ORDER BY UID
		) eduPersonScopedAffiliation
		GROUP BY uid
	) AS epsa
	ON diy.uid = epsa.uid
GROUP BY diy.uid
ORDER BY LPAD(lower(username), 2,0), LPAD(lower(username), 10,0)";
	
	// Run the query
	$qryrset = doQuery($sqlString, $dbuser, $dbpass, $dbhost);

	//var_dump($qryrset);
	
	$rows = array();
	while($r = mysql_fetch_assoc($qryrset)) {
		$rows[] = $r;
	}
	
	$htmlTable = array2table($rows);
	print_r($htmlTable);

?>
