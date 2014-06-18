<?php

/*
* script: quest.php
* developed by gratefulDeadty
*/

require 'core/init.php';

if (isset($_GET['questid']))
{
	define(quest_to_do);
	$finished = $_GET['finished'];
	$questid = $_GET['questid'];
	$userid = 1;
	$level = 5;
	$stmt = $dbh->prepare('SELECT * FROM `playerquests` WHERE `questid`=? AND `playerid`=? ');
	$stmt->bindValue(1, $_GET['questid']);
	$stmt->bindValue(2, $userid);
	$stmt->execute();
	$quest_complete = $stmt->rowCount();
	if ($quest_complete == 0)
	{
		$stmt = $dbh->prepare('SELECT * FROM `quests` WHERE `questid`=? ');
		$stmt->bindValue(1, $_GET['questid']);
		$stmt->execute();
		$questdata = $stmt->fetch();
		$mobid = $questdata['questmobid'];
		$active = $questdata['active'];
	
		if ($active == 0)
		{	
			echo '<div align="center">Error: This quest is not active';
			echo '<br /><form><input value="Go Back" onclick="history.go(-1)" type="button"></form></div>';
			die();
		}
		
		$stmt = $dbh->prepare('SELECT * FROM `mobs` WHERE `mobid`=? ');
		$stmt->bindValue(1, $mobid);
		$stmt->execute();
		$mobdata = $stmt->fetch();
		$mobname = $mobdata['name'];
		$welcometext = $questdata['intro'];
		$prompt = $questdata['prompt']; //quest prompt.
		$rewardtext = $questdata['reward']; //quest accepted.
		$special = $questdata['special'];
		$questlevel = $questdata['questlevel'];

		if ($level < $questlevel)
		{
			die('Error: Your not the right level for this quest.');
		}
		
		$mobimage = $mobdata['image'];
		$req_quest = $questdata['requiredquest'];
		if ($req_quest != 0) 
		{
			$stmt = $dbh->prepare('SELECT * FROM `playerquests` WHERE `questid`=? AND `playerid`=? ');
			$stmt->bindValue(1, $req_quest);
			$stmt->bindValue(2, $userid);
			$stmt->execute();
			$result = $stmt->rowCount();
			if ($result == 0)
			{
				die('Finish the required quests before trying to start this quest');
			}
			else
			{
				$resultrow = $stmt->fetch();
				$quest_started = $resultrow['date'];
			}
		}
		
		$quest_req = 0;
		$quest_done = 0;
		$date = date("Y\-m\-d H\:i\:s");
		$killid = $questdata['killid'];
		$colid = $questdata['colid'];
		$godkill = $questdata['godkill'];
		
		//start mob kills.
		if ($killid != 0) 
		{
			//explode values, they're all stored together.
			$killnum = $questdata['killnum'];
			$kill_ids = explode(",",$killid);
			$kill_nums = explode(",",$killnum);
			$kill_id_count = count($kill_ids);
			//If there is more than one mob to be killed use the exploded values.
			if ($kill_id_count > 1) 
			{
				for ( $i = 0; $i < $kill_id_count; $i += 1) 
				{
					$quest_req++;
					$mob_id = $kill_ids[$i];
					$mob_num = $kill_nums[$i];
					$stmt = $dbh->prepare('SELECT * FROM `questkills` WHERE `realmobid`=? AND `id`=? AND `questid`=? AND `date`>? ');
					$stmt->bindParam(1, $mob_id);
					$stmt->bindParam(2, $userid);
					$stmt->bindParam(3, $quest_started);
					$stmt->execute();
					$mob_kills = $stmt->rowCount();
					
					//get name of mob.
					$stmt = $dbh->prepare('SELECT `name` FROM `mobs` WHERE `mobid`=? ');
					$stmt->bindValue(1, $mob_id);
					$stmt->execute();
					$mobnameRow = $stmt->fetch();
					$mob_name = $mobnameRow['name'];
					if ($mob_kills >= $mob_num) 
					{
						$quest_done++;
						$quest_todo .= "<font color=green><b>$mob_name $mob_kills / $mob_num </b></font><br />";
					} 				
					else 
					{
						$quest_todo .= "<font color=red>$mob_name $mob_kills / $mob_num </font><br />";
					}
				}
			} 
			else 
			{
			
				//1 mob to kill.
				$quest_req++;
				$stmt = $dbh->prepare('SELECT * FROM `questkills` WHERE `realmobid`=? AND `id`=? AND `questid`=? AND `date`>? ');
				$stmt->bindParam(1, $killid);
				$stmt->bindParam(2, $userid);
				$stmt->bindParam(3, $questid);
				$stmt->bindParam(4, $quest_started);
				$mob_kills = $stmt->rowCount();
				
				$stmt = $dbh->prepare('SELECT name FROM `mobs` WHERE `mobid`=? ');
				$stmt->bindValue(1, $killid);
				$stmt->execute();
				$mobnameRow2 = $stmt->fetch();
				$mob_name = $mobnameRow2['name'];
				
				if ($mob_kills >= $killnum) 
				{
					$quest_done++;
					$quest_todo .= "<font color=green><b>$mob_name $mob_kills / $killnum </b></font><br />";
				} 
				else 
				{
					$quest_todo .= "<font color=red>$mob_name $mob_kills / $killnum </font><br />";
				}
			}
		}
		
		//item hand in
		if ($colid != 0) 
		{
			$colnum = $questdata['colnum'];
			$col_ids = explode(",",$colid);
			$col_nums = explode(",",$colnum);
			$col_id_count = count($col_ids);
			
			if ($col_id_count > 1) 
			{
				for ( $i = 0; $i < $col_id_count; $i += 1) 
				{
					$quest_req++;
					$item_id = $col_ids[$i];
					$item_num = $col_nums[$i];
					if ($item_num != 0) 
					{
						$dropped = 0;
						$equipped = 0;
						$trading = 0;
						$stmt = $dbh->prepare('SELECT * FROM`playeritems` WHERE `itemid`=? AND `id`=? AND `dropped`=? AND `equipped`=? AND `trading`=? ');
						$stmt->bindValue(1, $item_id);
						$stmt->bindValue(2, $userid);
						$stmt->bindValue(3, $dropped);
						$stmt->bindValue(4, $equipped);
						$stmt->bindValue(5, $trading);
						$stmt->execute();
						$item_has = $stmt->rowCount();
						
						$stmt = $dbh->prepare('SELECT `name` FROM `items` WHERE `itemid`=? ');
						$stmt->bindValue(1, $item_id);
						$stmt->execute();
						$itemnameRow = $stmt->fetch();
						$item_name = $itemnameRow['name'];
						if ($item_has >= $item_num) 
						{
							$quest_done++;
							$quest_todo .= "<font color=green><b>$item_name $item_has / $item_num </b></font><br />";
    } else {
    $quest_todo .= "<font color=red>$item_name $item_has / $item_num</font><br />";
    }
					} 
					else 
					{
						$quest_done++;
					}
				}
			} 
			else 
			{
				$quest_req++;
				$dropped = 0;
				$equipped = 0;
				$trading = 0;
				$stmt = $dbh->prepare('SELECT * FROM`playeritems` WHERE `itemid`=? AND `id`=? AND `dropped`=? AND `equipped`=? AND `trading`=? ');
				$stmt->bindValue(1, $colid);
				$stmt->bindValue(2, $userid);
				$stmt->bindValue(3, $dropped);
				$stmt->bindValue(4, $equipped);
				$stmt->bindValue(5, $trading);
				$stmt->execute();
				$item_has = $stmt->rowCount();
				$stmt = $dbh->prepare('SELECT `name` FROM `items` WHERE `itemid`=? ');
				$stmt->bindValue(1, $colid);
				$stmt->execute();
				$itemnameRow = $stmt->fetch();
				$item_name = $itemnameRow['name'];
				if ($item_has >= $colnum) 
				{
					$quest_done++;
					 $quest_todo .= "<font color=green><b>$item_name $item_has / $colnum </b></font><br />";
    } else {
    $quest_todo .= "<font color=red>$item_name $item_has / $colnum</font><br />";
    }
			}
		}
		
		//god quests.
		if ($godkill != 0) 
		{
			$god_ids = explode(",",$godkill);
			$god_id_count = count($god_ids);
			//if more than one god is too be killed, split values.
			if ($god_id_count > 0) 
			{
				for ( $i = 0; $i < $god_id_count; $i++) 
				{
					$quest_req++;
					$god_id = $god_ids[$i];
					if ($god_id != 0) 
					{	
						$stmt = $dbh->prepare('SELECT name FROM `god` WHERE `godid`=? ');
						$stmt->bindValue(1, $god_id);
						$stmt->execute();
						$godnameRow = $stmt->fetch();
						$god_name = $godnameRow['name'];
						
						//getting kills
						$stmt = $dbh->prepare('SELECT * FROM `god_kills` WHERE `playerid`=? AND `crewid`=? AND `godid`=? AND `date`>? ');
						$stmt->bindValue(1, $userid);
						$stmt->bindValue(2, $crewid);
						$stmt->bindValue(3, $god_id);
						$stmt->bindValue(4, $quest_started);
						$stmt->execute();
						$killed_god = $stmt->rowCount();
						if ($killed_god != 0) 
						{
							$quest_done++;
							$quest_todo .= "<font color=00FF00><b>$god_name 1 / 1 </b></font><br />";
						} 
						else 
						{
							$quest_todo .= "<font color=red><b>$god_name 0 / 1 </b></font><br />";
						}
					} 
					else 
					{
						$quest_done++;
					}
				}
			} 
			else 
			{
				$quest_req++;
				$dropped = 0;
				$equipped = 0;
				$trading = 0;
				$stmt = $dbh->prepare('SELECT * FROM`playeritems` WHERE `itemid`=? AND `id`=? AND `dropped`=? AND `equipped`=? AND `trading`=? ');
				$stmt->bindValue(1, $colid);
				$stmt->bindValue(2, $userid);
				$stmt->bindValue(3, $dropped);
				$stmt->bindValue(4, $equipped);
				$stmt->bindValue(5, $trading);
				$stmt->execute();
				$item_has = $stmt->rowCount();
				
				$stmt = $dbh->prepare('SELECT name FROM `items` WHERE `itemid`=? ');
				$stmt->bindValue(1, $colid);
				$stmt->execute();
				$godnameRow = $stmt->fetch();
				$item_name = $itemnameRow['name'];
				if ($item_has >= $colnum) 
				{
					$quest_done++;
					   $quest_todo .= "<font color=green><b>$item_name $item_has / $colnum </b></font><br />";
    } else {
    $quest_todo .= "<font color=red>$item_name $item_has / $colnum</font><br />";
    }
			}
		}
		echo"<br /><br />
		<div align='center'>
					<table cellspacing=\"0\" cellpadding=\"0\" width=\"75%\">
			<tr>
				<td valign=\"top\" bgcolor=\"#666666\" style=\"background-color:#;border:2px solid #000000;width:75%;padding:5px;\">
				<img align=\"right\" style=\"margin-left:5px;\" src=\"$mobimage\">
				<font size=\"3\" color=\"#FFFFFF\"><b>$mobname</b></font>";
		if ($finish == 1) 
		{
			echo'
			<style type=\"text/css\">
			<!--
			a:link 
			{
				color: #00FFFF;
			}
			-->
			</style>';
			
			
			if ($quest_req == $quest_done) 
			{
				$reward_exp = $questdata['rewxp'];
				$reward_item_id = $questdata['rewid'];
				$reward_item_type = $questdata['rewtype'];
				$repetable = $questdata['repetable'];
				$keep = $questdata['keep'];
				if ($reward_item_id != 0) 
				{
					$reward_items_array = explode(",",$reward_item_id);
					$reward_items_type_array = explode(",",$reward_item_type);
					$items_count = count($reward_items_array);
					if ($items_count > 1) 
					{
						for ( $i = 0; $i < $items_count; $i += 1) 
						{
							$item_id = $reward_items_array[$i];
							$item_type = $reward_items_type_array[$i];
							
							$stmt = $dbh->prepare('SELECT name,duration FROM `items` WHERE `itemid`=? ');
							$stmt->bindValue(1, $item_id);
							$stmt->execute();
							$rewarditemnameRow = $stmt->fetch();
							$reward_item_name = $rewarditemnameRow['name'];
							$reward_item_duration = $rewarditemnameRow['duration'];
							$expires = ($item_type != 'potions') ? $reward_item_duration : 0;
	
							//user gets item
							$stmt = $dbh->prepare('INSERT INTO `playeritems` (`itemid`, `id`, `type`, `expires`) VALUES (?, ?, ?, ?) ');
							$stmt->bindValue(1, $reward_item_id);
							$stmt->bindValue(2, $userid);
							$stmt->bindValue(3, $item_type);
							$stmt->bindValue(4, $expires);
							$stmt->execute();
							$reward_item .= "$reward_item_name!<br />";
						}
					} 
					else 
					{
						$stmt = $dbh->prepare('SELECT name,duration FROM `items` WHERE `itemid`=? ');
						$stmt->bindValue(1, $reward_item_id);
						$stmt->execute();
						$rewarditemnameRow = $stmt->fetch();
						$reward_item_name = $rewarditemnameRow['name'];	
						$reward_item_duration = $rewarditemnameRow['duration'];
						$expires = ($item_type != "potions") ? $reward_item_duration :0;
 
						$stmt = $dbh->prepare('INSERT INTO `playeritems` (`itemid`, `id`, `type`, `expires`) VALUES (?, ?, ?, ?) ');
						$stmt->bindValue(1, $reward_item_id);
						$stmt->bindValue(2, $userid);
						$stmt->bindValue(3, $reward_item_type);
						$stmt->bindValue(4, $expires);
						$stmt->execute();
						$reward_item .= "$reward_item_name!<br />";
					}
				}
				
				//reward xp.
				if ($reward_exp != 0)
				{
					$stmt = $dbh->prepare('UPDATE `stats` SET `exp`=?, `ggain`=?, `gtoday=?` WHERE `id`=? ');
					$stmt->bindValue(1, $reward_exp);
					$stmt->bindValue(2, $reward_exp);
					$stmt->bindValue(3, $reward_exp);
					$stmt->bindValue(4, $userid);
					$stmt->execute();
					$reward_xp = number_format($reward_exp);
				}
				
				//insert into quest log.
				if ($repetable == 0) 
				{
					$stmt = $dbh->prepare('INSERT INTO `playerquests` (`playerid`, `questid`, `date`) VALUES(?, ?, ?) ');
					$stmt->bindValue(1, $userid);
					$stmt->bindValue(2, $questid);
					$stmt->bindValue(3, $date);
				}
				
				if ($keep == 0) 
				{
					if ($colid != 0) 
					{
						if ($col_id_count > 1) 
						{
							for ($i = 0; $i < $col_id_count; $i++) 
							{
								$collect_item_id = $col_ids[$i];
								$collect_item_num = $col_nums[$i];
								$stmt = $dbh->prepare('DELETE FROM `playeritems` WHERE `itemid`=? AND `id`=? LIMIT ? ');
								$stmt->bindValue(1, $collect_item_id);
								$stmt->bindValue(2, $userid);
								$stmt->bindValue(3, $collect_item_num);
								$stmt->execute();
							}
							
						}
						else
						{
							$stmt = $dbh->prepare('DELETE FROM `playeritems` WHERE `itemid`=? AND `id`=? LIMIT ? ');
							$stmt->bindValue(1, $colid);
							$stmt->bindValue(2, $userid);
							$stmt->bindValue(3, $colnum);
							$stmt->execute();
						}
					}
				}
				
				//delete ALL quest kills.
				$killid = $questdata['killid'];
				if($killid != 0)
				{
					$killnum = $questdata['killnum'];
					$kill_ids = explode(",",$killid);
					$kill_nums = explode(",",$killnum);
					$kill_id_count = count($kill_ids);
					if ($kill_id_count > 1) 
					{
						for ( $i = 0; $i < $kill_id_count; $i += 1) 
						{
							$mob_id = $kill_ids[$i];
							$mob_num = $kill_nums[$i];
							$counter = 0;
							while ( $counter <= $mob_num ) 
							{
							
								$stmt = $dbh->prepare('DELETE FROM `questkills` WHERE `realmobid`=? AND `id`=? AND `questid`=? ');
								$stmt->bindValue(1, $mob_id);
								$stmt->bindValue(2, $userid);
								$stmt->bindValue(3, $questid);
								$stmt->execute();							
								$counter++;
							}
						}
					} 
					else 
					{
					
						$stmt = $dbh->prepare('DELETE FROM `questkills` WHERE `realmobid`=? AND `id`=? AND `questid`=? ');
						$stmt->bindValue(1, $killid);
						$stmt->bindValue(2, $userid);
						$stmt->bindValue(3, $questid);
						$stmt->execute();
					}
				}
				
				echo '<br /><br />'.$rewardtext.'<br /><br />';
				if ($reward_exp != 0)
				{ 
					echo '<p>You Have Gained '.$reward_xp.' Experience!!</p>';
					echo '<br><br>'; 
				}
				if ($reward_item != "")
				{ 
					echo '<p>You Have Recieved '.$reward_item.'</p>'; 
					echo "<br /><br />"; 
				}
				eval($special);
				echo '<br /><br />';
			} 
			else 
			{
				echo 'You still have work:<br /><br />'.$quest_todo.'<br /><br />';
				echo '<br><a href="world.php">Go Back</a>';
			}
		} 
		else 
		{
			echo '<p>'.$welcometext.'</p>';
			echo '<p>'.$quest_todo.'</p>';
			if ($quest_req == $quest_done) 
			{
				echo '<p><a href="quest.php?questid='.$questid.'&finish=1">'.$prompt.'</a></p>';
			}
		}
		echo'<a href="world.php">Go Back</a></td>
			</tr>
		</table>
		</center>';
	}
}
?>

