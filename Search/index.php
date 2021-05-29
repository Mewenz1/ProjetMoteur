<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

$q = htmlspecialchars(strip_tags($_GET['q']));

$ck1 = htmlspecialchars(strip_tags($_GET['ck1']));
$ck2 = htmlspecialchars(strip_tags($_GET['ck2']));
$ck3 = htmlspecialchars(strip_tags($_GET['ck3']));
$ck4 = htmlspecialchars(strip_tags($_GET['ck4']));
$ck5 = htmlspecialchars(strip_tags($_GET['ck5']));
$ck6 = htmlspecialchars(strip_tags($_GET['ck6']));


$qtrim = trim($q);

$recherches = preg_split('/ /', $qtrim);

// $a = array('À','Á','Â','Å','Ã','Ä','à','á','â','å','ã','ä');
// $e = array('É','Ê','È','é','ê','è','ë');

// $remplacementA = str_replace('a',$a,$recherches);
// $remplacementE = str_replace('e',$e,$recherches);
// $recherches = array_merge($remplacementA,$remplacementE,$recherches);

//array_push($recherches,$remplacementA);
//print_r ($recherches);


$where = '';
$sansOR ='(';

if ($ck1!='on' && $ck2!='on' && $ck3!='on' && $ck4!='on' && $ck5!='on' && $ck6!='on' )
{
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' (etatdefendeur LIKE "%'.$value.'%" OR datedelarret LIKE "%'.$value.'%"  OR thematique LIKE "%'.$value.'%" 
			OR auteur LIKE "%'.$value.'%" OR revue LIKE "%'.$value.'%" OR reference LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR ( etatdefendeur LIKE "%'.$value.'%" OR datedelarret LIKE "%'.$value.'%"  OR  thematique LIKE "%'.$value.'%" 
			OR auteur LIKE "%'.$value.'%" OR revue LIKE "%'.$value.'%" OR reference LIKE "%'.$value.'%") ';
		}
	}
}
else if($ck1=='on' && $ck2!='on' && $ck3!='on' && $ck4!='on' && $ck5!='on' && $ck6!='on' ) {
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' ( etatdefendeur LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR ( etatdefendeur LIKE "%'.$value.'%") ';
		}
	}
}
else if($ck1!='on' && $ck2=='on' && $ck3!='on' && $ck4!='on' && $ck5!='on' && $ck6!='on' ) {
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' ( datedelarret  LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR ( datedelarret  LIKE "%'.$value.'%") ';
		}
	}
}
else if($ck1!='on' && $ck2!='on' && $ck3=='on' && $ck4!='on' && $ck5!='on' && $ck6!='on') {
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' ( thematique LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR (  thematique LIKE "%'.$value.'%") ';
		}
	}
}
else if($ck1!='on' && $ck2!='on' && $ck3!='on' && $ck4=='on' && $ck5!='on' && $ck6!='on') {
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' ( auteur LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR ( auteur LIKE "%'.$value.'%") ';
		}
	}
}
else if($ck1!='on' && $ck2!='on' && $ck3!='on' && $ck4!='on' && $ck5=='on' && $ck6!='on') {
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' (  revue LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR ( revue LIKE "%'.$value.'%") ';
		}
	}
}
else if($ck1!='on' && $ck2!='on' && $ck3!='on' && $ck4!='on' && $ck5!='on' && $ck6=='on') {
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' ( reference LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR ( reference LIKE "%'.$value.'%") ';
		}
	}
}

else if ($ck1=='on' && $ck2=='on' && $ck3=='on' && $ck4=='on' && $ck5=='on' && $ck6=='on')
{
	foreach ($recherches as &$value) {
		if (empty($where))
		{
			$where .= ' (etatdefendeur LIKE "%'.$value.'%" OR datedelarret LIKE "%'.$value.'%"  OR thematique LIKE "%'.$value.'%" 
			OR auteur LIKE "%'.$value.'%" OR revue LIKE "%'.$value.'%" OR reference LIKE "%'.$value.'%") ';
		}
		else if  (!empty($where))
		{
			$where .= ' OR (etatdefendeur 	LIKE "%'.$value.'%" OR datedelarret LIKE "%'.$value.'%" OR thematique LIKE "%'.$value.'%" 
			OR auteur LIKE "%'.$value.'%" OR revue LIKE "%'.$value.'%" OR reference LIKE "%'.$value.'%") ';
		}
	}
}
else 
{
	foreach ($recherches as &$value)
	 	{
			if  (!empty($where))
			{
				$where .= ') AND (';
			}
			
			if (($ck1=='on') && empty($where))
			{
				$where .= ' ( etatdefendeur LIKE "%'.$value.'%" ';
			}
			else if  (($ck1=='on') && !empty($where))
			{
				$where .= ' etatdefendeur LIKE "%'.$value.'%" ';
			}

			$lastChar = substr($where, -1);

			if (($ck2=='on') && empty($where))
			{
				$where .= ' ( datedelarret LIKE "%'.$value.'%" ';
			}
			else if  (($ck2=='on') && !empty($where) && ($lastChar!=$sansOR))
			{
				$where .= ' OR datedelarret LIKE "%'.$value.'%" ';
			}
			else if (($ck2=='on') && ($lastChar==$sansOR))
			{
				$where .= ' datedelarret LIKE "%'.$value.'%" ';
			}

			$lastChar = substr($where, -1);

			if (($ck3=='on') && empty($where))
			{
				$where .= ' ( thematique LIKE "%'.$value.'%" ';
			}
			else if  ( ($ck3=='on') && !empty($where) && ($lastChar!=$sansOR))
			{
				$where .= ' OR thematique LIKE "%'.$value.'%" ';
			}
			else if (($ck3=='on') && ($lastChar==$sansOR))
			{
				$where .= ' thematique LIKE "%'.$value.'%" ';
			}

			$lastChar = substr($where, -1);

			if (($ck4=='on') && empty($where))
			{
				$where .= ' ( auteur LIKE "%'.$value.'%" ';
			}
			else if  ( ($ck4=='on') && !empty($where) && ($lastChar!=$sansOR))
			{
				$where .= ' OR auteur LIKE "%'.$value.'%" ';
			}
			else if (($ck4=='on') && ($lastChar==$sansOR))
			{
				$where .= ' auteur LIKE "%'.$value.'%" ';
			}

			$lastChar = substr($where, -1);

			if (($ck5=='on') && empty($where))
			{
				$where .= ' ( revue LIKE "%'.$value.'%" ';
			}
			else if  ( ($ck5=='on') && !empty($where) && ($lastChar!=$sansOR))
			{
				$where .= ' OR revue LIKE "%'.$value.'%" ';
			}
			else if (($ck5=='on') && ($lastChar==$sansOR))
			{
				$where .= ' revue LIKE "%'.$value.'%" ';
			}

			$lastChar = substr($where, -1);

			if (($ck6=='on') && empty($where))
			{
				$where .= ' ( reference LIKE "%'.$value.'%" ';
			}
			else if  ( ($ck6=='on') && !empty($where) && ($lastChar!=$sansOR))
			{
				$where .= ' OR reference LIKE "%'.$value.'%" ';
			}
			else if (($ck6=='on') && ($lastChar==$sansOR))
			{
				$where .= ' reference LIKE "%'.$value.'%" ';
			}

		}
	$where.= ' )';
}

$query = 'SELECT  etatdefendeur, datedelarret,thematique,auteur,revue,reference  FROM viewResultat WHERE';
$query .= $where;

$stmt = $db->prepare($query);
$stmt->execute();
 
$result_arr=array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		// $row['ess_value']
		// coucou
        extract($row);
		$item = array($etatdefendeur,$datedelarret,$thematique,$auteur,$revue,$reference);
        array_push($result_arr, $item);
}
echo json_encode($result_arr);
