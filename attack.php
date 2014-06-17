<?php

/*
* script: attack.php
*
* this script is a test for an attack system for an rpg game.
* $attacker & $defender represent the user attacking & the defender from the database.
*/

echo <<<TEST
	<script type="text/javascript">\n
	function show(message) {\n
		obj = document.getElementById(message);\n
		obj.style.visibility = 'visible';\n
	}\n
	</script>\n
TEST;

$attacker = array('user' => 'attacker', 'level' => '22', 'hp' => '212', 'attack' => '115'); 
	
$defender = array('user' => 'defender', 'level' => '20', 'hp' => '300', 'attack' => '100'); 

$attackerhp = $attacker['hp'];
$defenderhp = $defender['hp'];


$won = false;
$messageNum = 0;
while ($attackerhp >= 0 AND $defenderhp >= 0 AND !$won)
{
	$attackerdamage = round ( rand ( 5, 10 ) * $attacker['attack'] / 100 ) * ( $attacker['level'] );
	$defenderdamage = round ( rand ( 5, 10 ) * $defender['attack'] / 100 ) * ( $defender['level'] );
	
	if (!$won) 
	{
                $messageNum++;
		echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">You attack for ".$attackerdamage." (100%)</span><br />";
		$defenderhp = $defenderhp-$attackerdamage;
		$attackerhp = $attackerhp;
		$attackerhpremains = $attackerhp/$attacker['hp'];
		$attackerhpremains = $attackerhpremains*100;
		$attackerhpremains = round($attackerhpremains);
		
		$defenderhpremains = $defenderhp/$attacker['hp'];
		$defenderhpremains = $defenderhpremains*100;
		$defenderhpremains = round($defenderhpremains);
		
		if ($defenderhpremains <= 0)
		{
			$won = true;
		}
	}
        ++$messageNum;
	
	if (!$won)
	{
		echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">" .$defender['user']. " attacks for " .$defenderdamage. " (".$defenderhpremains."%)</span><br />";
	}
	
} //end while();
	
if ($defenderhpremains <= 0)
{
	echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">" .$attacker['user']. " wins!<br />";
}
else
{
	echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">You have been defeated by " .$defender['user']. "<br />";
}

echo "<script type=\"text/javascript\">\n";
for($y = 0; $y <= $messageNum; $y++) 
{
        $time+= 1000;
	echo "setTimeout(\"show('message_{$y}')\", {$time});\n";
}
echo "</script>\n";


?>
	
	
	
		
