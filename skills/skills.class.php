<?php

class Skills
{
	private $dbh;
	private $classid;
	private $userid;
	private $skillid;
	public $messages = array();
	public $errors = array();

	public function __construct($database, $classid, $userid)
	{
		session_start();
		$this->dbh = $database;
		$this->classid = $classid; //getting the users skill class.
		$this->userid = $_SESSION['userid'];
		
		
		if (isset($_GET['skillid']))
		{
			$skillid = htmlentities((int)$_GET['skillid']);
			$this->skillid = $skillid;
			$this->loadSkill($skillid);
		}
		
		if (isset($_POST['learn']))
		{
			$skillid = htmlentities((int)$_POST['id']);
			$this->skillid = $skillid;
			$this->learnSkill();
		}

	}

	public function skillDataId($skillid)
	{
		$query = $this->dbh->prepare('SELECT `name`,`level`,`duration`,`image`,`lvlreq`,`skillid`,`recharge`,`ragecost` FROM `skills`
			WHERE `skillid` = :skillid ORDER BY `level`');

		$query->execute(array(
			':skillid' => $skillid
			));

                //return $query->fetch();
                return $query->fetchAll();
	}
	
	public function skillDataLevel($skillid, $level)
	{
		$query = $this->dbh->prepare('SELECT `desc` FROM `skills` 
			WHERE `skillid` = :skillid
			AND `level` = :level');
			
		$query->execute(array(
			':skillid' => $this->skillid,
			':level' => $level
			));
		
		if ($query)
		{	
			return $query->fetchAll();
		}
		else
		{
			return false;
		}
	}

	public function skillDataClass()
	{
                //`lvlreq` < :lvlreq
                //':lvlreq' => 40,
		$query = $this->dbh->prepare('SELECT `skillid`,`name`,`image`,`desc`,`lvlreq` FROM `skills` 
                        WHERE `level` = :level AND `lvlreq` < :lvlreq');

		$query->execute(array(
                        ':level' => 1,
                        ':lvlreq' => 100
			));

		return $query->fetchAll();

	}

	public function userSkillsData($skillid, $userid)
	{
		$query = $this->dbh->prepare('SELECT `level` FROM `playerskills`
			WHERE `skillid` = :skillid 
			AND `playerid` = :playerid');

		$query->execute(array(
			':skillid' => $this->skillid,
			':playerid' => $this->userid
			));
			
		if ($query->rowCount() != 0)
		{
			$userskillData = $query->fetch();
			$level = $userskillData['level'];
			return $level;
		}
		else
		{
			return false;
		}
	}
	

	public function castedData($userid, $skillid)
	{
		$query = $this->dbh->prepare('SELECT `recharge` FROM `castskills`
			WHERE `playerid` = :playerid
			AND `skillid` = :skillid
			AND `recharged = :recharged
			ORDER BY `skillcastid');

		$query->execute(array(
			':playerid' => $this->userid,
			':skillid' => $this->skillid,
			'recharged' => 0
			));

		if ($query->rowCount() != 0)
		{
			$castData = $query->fetch();
			$recharge = $castData['recharge'];
			return $recharge;
		}
		else
		{
			return false;
		}
	}
	

        public function userSkillLevel()
        {
	        return $this->userSkillsData($skillid, $userid);
        }
        
	public function loadSkill($skillid)
	{
		$skillData = $this->skillDataId($this->skillid);
		foreach ($skillData as $skillz)
		{
			$skill_name = $skillz['name'];
			$max_level = $skillz['level'];
			$image = $skillz['image'];
			$skill_desc = $skillz['desc'];
			$skill_rage = $skillz['ragecost'];
			$recharge = $skillz['recharge'];
			$duration = $skillz['duration'];
			$level_req = $skillz['lvlreq'];
		}

		
			if ($this->userSkillsData($this->skillid, $this->userid) != 0)
			{
				$user_has_skill = 1;
				$skill_level_ini = $this->userSkillLevel();
				$skill_inc = $item_skill_inc[$skillid];
				if (!empty($skill_inc)) 
				{
					$skill_level_inc = $skill_inc;
				}
				
				/*
				foreach ($skillData as $skillLevel)
				{
					$max_level = $skillLevel['level'];
				}
				*/
				
				if ($skill_level_ini+$skill_level_inc > $max_level) 
				{
					$skill_level = $max_level-$skill_level_inc;
				} 
				else 
				{
					$skill_level = $skill_level_ini;
				}
				$curlevelAdd = $skill_level+$skill_level_inc;
				$curLevel = $this->skillDataLevel($this->skillid, $curlevelAdd);
				
				foreach ($curLevel as $cur)
				{
					$desc = $cur['desc'];
				}
				
				
				$newLevelAdd = $skill_level+$skill_level_inc+1;
				$levelData = $this->skillDataLevel($this->skillid, $newLevelAdd);

				if ($levelData != 0)
				{
					foreach ($newLevel as $new)
					{
						$next_level_desc = $new['desc'];
					}
				}
				else
				{
					$next_level_desc = '';
				}
				
				echo '<div align="center"><img src="'.$image.'">
				<br />'.$desc.'<br />
				<br />'.$skill_name.' Level: '; 

				if ($skill_level_ini == 0) 
				{
					echo '1';
				}
				else 
				{
					echo $skill_level_ini;  
				} 
				if ($skill_level_inc != 0) 
				{
					echo ' + '.$skill_level_inc.'';
				}
				$userlevel = 5;
				
				if ($level_req  > $userlevel)
				{
					echo '<br />Level Required: '.$level_req.'';
				}

				echo '<br /><strong>Attack Cost:</strong> '.$skill_rage.'
				<br /><strong>Cooldown:</strong> '.$recharge.' mins
				<br /><strong>Duration:</strong> '.$duration.' mins<br />
				</div>';
				
				if ($this->castedData($this->userid, $this->skillid) === true)
				{
					$castedData = $this->castedData($this->userid, $this->skillid);
					$recharging = $castedData['recharge'];
					echo 'This Skill Is Recharging! '.$recharging.' minutes remaining<br />';
				}

				if (!empty($next_level_desc))
				{
					echo '<br /><strong>Next Level:</strong>'.$next_level_desc.'<br />';
				}

			}
			else
			{
				echo 'Click learn to learn '.$skill_name.'<br />';
			
				echo '<form method="POST"><input type="hidden" name="id" value="'.$this->skillid.'" />
				<input type="submit" name="learn" value="Learn!" />
				</form>';
				
				
			}
	}
	
	
	
	
	public function learnSkill()
	{
		if ($this->userSkillsData($this->skillid, $this->userid) === false) //user doesn't have skill yet?.
		{
			foreach ($this->skillDataId($this->skillid) as $skillz)
			{
				$name = $skillz['name'];
			}		
			echo $name;
		}
		else
		{
			echo 'no good.';
			//$errors[] = 'Error: You have already learned this skill! Try casting it instead of learning it!';
		}
	}

	
	
}
?>
