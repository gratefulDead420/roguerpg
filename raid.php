$messageNum = 0;
while (!empty($usersArr) === true || $raidArr['hp'] > 0)
{
	static $attacking = 'p';
	if ($attacking === 'p')
	{
		$totalAttack = 0;
		$i = 0;
		foreach ($usersArr as $key => $raiduser)
		{
			echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">" . $raiduser['username'] . " attacks for " . $raiduser["attack"] . "</span> <span id=\"death_{$i}\" style=\"visibility:hidden;\"></span><br/>\n";
			$totalAttack += $raiduser['attack'];
			++$messageNum;
			++$i;		
		}
	}
	
	$raidArr['hp'] -= $totalAttack;
	if ($raidArr['hp'] <= 0)
	{
		echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">Your crew has defeated " . $raidArr['name'] . "</span>\n";
		break;
	}
	
	$attacking = 'b';
	if ($attacking === 'b')
	{
		echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">" . $raidArr['name'] . " attacks for " . $raidArr['attack'] . "</span><br/>\n";
		++$messageNum;
	}
	
	foreach ($usersArr as $key => $raiduser)
	{
		static $i;
		$raiduser['hp'] -= $raidArr['attack'];
		if ($raiduser['hp'] <= 0)
		{
			echo <<<JAVASCRIPT
			<script type="text/javascript">\n
				obj = document.getElementById('death_{$i}');\n
				obj.innerHTML = "DEAD!";\n
				obj.id = 'message_{$messageNum}';\n
			</script>\n
JAVASCRIPT;
			unset($usersArr[$key]); //removing dead players.
			++$messageNum;
		}
		++$i;
	}
	
	if (empty($usersArr) === true)
	{
		echo "<span id=\"message_{$messageNum}\" style=\"visibility:hidden;\">Your crew has been defeated!</span>\n";
		break;
	}
	
	$attacking = 'p';
	
} //end loop.

echo "<script type=\"text/javascript\">\n";
for($y = 0; $y <= $messageNum; $y++) 
{
	$time+= 1000; //sets speed.
	echo "setTimeout(\"show('message_{$y}')\", {$time});\n";
}
echo "</script>\n";

?>
