<?php

/*
* world.php
* developed by gratefulDeadty
*/

require 'config.php';

class World
{
	private $dbh;
	
	public function __construct($database)
	{
		$this->dbh = $database;
	}

	public function getRoom($roomid)
	{
		$query = $this->dbh->prepare('SELECT * FROM `world` WHERE `id`=?');
		$query->bindValue(1, $roomid);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getWorld($user_roomid)
	{
		$query = $this->dbh->query('SELECT * FROM `world` WHERE `id`=?');
		$query->bindValue(1, $user_roomid);
		$query->execute();
		return $query->fetch();
	}
	
	public function insertMovelog($userid, $ip, $userip, $roomid, $last)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$query = $this->dbh->prepare("INSERT INTO `move_log` (`userid`, `ip`, `userip`, `roomid`, `last`) VALUES (?, ?, ?, ?, ?) ");
		$query->bindValue(1, $userid);
		$query->bindValue(2, $ip);
		$query->bindValue(3, $userip);
        $query->bindValue(4, $roomid);
        $query->bindValue(5, $last);
	}
		
	public function updateMove($roomid, $userid)
	{	
		$query = $this->dbh->prepare('UPDATE `users` SET `room`=? WHERE');
		$query->bindValue(1, $roomid);
		$query->bindValue(2, $userid);
		$query->execute();
	}
	
	public function getMobs($roomid)
	{
		$spawn_mob_time = 0;
		$query = $this->dbh->prepare('SELECT * FROM `mobs` WHERE `room`=? AND `spawn_mob_time`<=? ');
		$query->bindValue(1, $roomid);
		$query->bindValue(2, $spawn_mob_time);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
	
}
	//errors array.
	$errors = array();
	if (empty($errors) === false)
	{
		echo ' '.implode($errors).' ';
	}

	$user_roomid = $user['room'];
	$world_main = new World($dbh);
	$rooms = $world_main->getRoom(isset($_GET['room']));
	$worlds = $world_main->getWorld($user_roomid);
	
	/* error checks. */
	if (empty($_GET['room']) === true)
	{
		$errors[] = 'Error: Invalid room. (error1)';
	}
	if (!is_numeric($_GET['room']))
	{
		$errors[] = 'Error: Invalid room (error2).';
	}
	if (empty($_GET['room'] === false)
	{
		if (count($world['id']) == 0)
		{
			$errors[] = 'Error: This room does not exist.';
		}
		
		foreach ($rooms as $room)
		{
			if ($room['id'] != $user_roomid)
			{
				if ($room['north'] != $user_roomid  AND $room['south'] != $user_roomid AND $room['west'] != $user_roomid AND $room['east'] != $user_roomid)
				{
					$errors[] = 'Error: You\'re not next to this room';
				}
		}
		$userid = $user['id'];
		$userip = $user['ip'];
		$roomid = $room['id'];
		$user_roomid = $user['room'];
		$insert_move = $world_main->insertMovelog($userid,$ip,$userip,$roomid,$last);
		$update_room = $world_main->updateMove($roomid,$userid);
		?>
		<!-- start pageUpdate ajax load. -->
		<script type="text/javascript">
		function handleKeyboard(evt)
		{
			evt=(evt)?evt:((window.event)?event:null);
			if (evt)
			{
				if(evt.keyCode==87)
				{
					pageupdate('<?php echo ' '.$room['north'].' ' ?>');
				}
				if(evt.keyCode==68)
				{
					pageupdate('<?php echo ' '.$room['east'].' '; ?>');
				}
				if(evt.keyCode==65)
				{ 
					pageupdate('<?php echo ' '.$room['west'].' '; ?>');
				}
				if(evt.keyCode==83)
				{
					pageupdate('<?php echo ' '.$room['south'].' '; ?>');
				}
			}
		}
		document.onkeyup = handleKeyboard;
		</script>
		
<!-- begin page content. -->
<div id="yourhugediv">
	<table border="0" cellspacing="1" cellpadding="1" width="50%" bgcolor="#000000">
	<tr>
		<td colspan="2" bgcolor="#333333">
			<table class="content" align="center" width="100%" bgcolor="#333333">
	<tr>
		<td colspan="3"  style="border:1px solid #000000;" bgcolor="#2b2b2b" align="center"><font color="#d69820"><strong>-<?php echo ' '.$room['name']. ' '; ?>-</strong></td>
	</tr>
	<tr>
		<td valign="top">
			<table width="155" valign="top" bgcolor="#333333">
	<tr>
		<td width="155" style="border:1px solid #000000;" bgcolor="#333333" COLSPAN=3>
				<img border="0" src="<?php echo ' '.$room['roompic'].' ';?>" usemap="#map"/></td>
	</tr>
	<tr>
	<tr style="border: 1px solid #000000; border-collapse: collapse;" bgcolor="#333333"><td width="250"><center>

<?php

$north = $room['north'];
$west = $room['west'];
$east = $room['east'];
$south = $room['south'];

/* left,right,up,down error image links. */
echo '<tr bgcolor="#333333"><td width="250" style="border: 1px solid #000000;" bgcolor="#2b2b2b"><center>';

if ($north > 0) 
{
	echo "<a href='javascript:void(0);' onclick=pageUpdate('$north')><img src='images/arrowN.png'></a>";
}
else
{
	echo '<img src="images/arrowNa.png">';
}

if ($west > 0) 
{
	echo "<a href='javascript:void(0);' onclick=pageUpdate('$west')><img src='images/arrowW.png'></a>";
}
else
{
	echo '<img src="images/arrowWa.png">';
}

echo '<span id="divsp"></span>';

if ($east > 0) 
{
	echo "<a href='javascript:void(0);' onclick=pageUpdate('$east')><img src='images/arrowE.png'></a>";
}
else
{
	echo '<img src="images/arrowEa.png">';
}

if ($south > 0) 
{
	echo "<a href='javascript:void(0);' onclick=pageUpdate('$south')><img src='images/arrowS.png'></a>";
}
else
{
	echo '<img src="images/arrowSa.png">';
}

?>

<!-- Mobs -->

	</tr>
			</table>
		</td>
		<td valign="top" bgcolor="#333333">
			<table width="250" valign="top" bgcolor="#333333">
	<tr>
		<td bgcolor="#2b2b2b" style="border:1px solid #000000;"><center><strong>Creatures in this room:</strong></td>
		<?php
		$mobs = $world_main->getMobs(isset($_GET['room']));
		foreach ($mobs as $mob)
		{	
			$mobid = $mob['id'];
			$mobname = $mob['name'];
			$moblevel = $mob['level'];
			echo '<tr><td>';
		
			$timeleft = 0;
			$stmt = $dbh->prepare('SELECT * FROM dead_mobs WHERE ' . 'mid=:mid AND ' . 'timeleft>:timeleft AND ' . 'uid=:uid ');
			$stmt->bindParam('mid', $mobid);
			$stmt->bindParam('timeleft', $timeleft);
			$stmt->bindParam('uid', $userid);
			$stmt->execute();
			$count = $stmt->rowCount();
			if ($count > 0)
			{
				echo '';
			}
			else
			{
				if (empty($mob['type']) === true)
				{
					$mobtitle = 'viewmob';
				}
				if ($mob['type'] == 'Q')
				{
					$mobtitle = 'viewquest';
				}
				if ($mob['type'] == 'T')
				{
					$mobtitle = 'viewtrainer';
				}
				?>
	<div style="border-bottom:1px dotted; border-color:#000000; height:15px;"> 
    <a href="mobattack.php?mob=<?php echo ' '.$mobid.' '; ?>">
	<img align="right" border="0" alt="Attack!" src="images/attackplayericon.jpg" style="cursor:pointer;" onMouseOver="menutip('Attack <?php echo ' '.$mob['name'].' '; ?>!');" onMouseOut="hideddrivetip();"> 
	<a href="<?php echo ' '.$mobtitle.' ';?>.php?mob=<?php echo ' '.$mobid.' '; ?>" style="cursor:pointer;">
		
	<?php
	if ($mob['type'] == 'Q')
	{
		echo "<img align='right' src='images/talk_icon.jpg' border='0' onMouseOver=\"menutip('$mobname has a quest for you!');\" onMouseOut=\"hideddrivetip();\"></a>";   
		echo '<a href="'.$mobtitle.'.php?mob='.$mobid.'" style="cursor:pointer;">';
	}
	if ($mob['type'] == 'T')
	{
		echo "<img align='right' src='images/world/trainer.jpg' border='0' onMouseOver=\"menutip('$mobname is a trainer!');\" onMouseOut=\"hideddrivetip();\"></a>";
		echo '<a href="'.$mobtitle.'.php?mob='.$mobid.'" style="cursor:pointer;" ONMOUSEOVER="menutip(Level $moblevel)" ONMOUSEOUT="hideddrivetip()">';
		echo '<font color="#949477">&nbsp;'.$mobname.' ('.$moblevel.')&nbsp;</font></a></div>';
	}
	    echo '</div>';
	
	echo '</tr></table>';


		}
	} 
	
	}
}
	
