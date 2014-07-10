<?php

class WorldMap
{
	private $dbh;
	public $errors = array();
	private $mapData;
	private $zoneData;

	public function __construct($database, $roomid)
	{
		$this->dbh = $database;
	        $this->roomid = $roomid;
	        
	        if (isset($_GET['direction']))
		{
                	$direction = htmlentities(stripslashes($_GET['direction']));
			$this->movePlayer($direction);
		}
	}

	public function roomData()
	{
 	        $query = $this->dbh->prepare('SELECT `x`,`y`,`z` 
			FROM `room` 
			WHERE `roomid` = :roomid');

		$query->execute(array(
			':roomid' => $this->roomid
			 ));

		$mapData = $query->fetch();
		$zone = $mapData['z'];
		return $mapData;
	}

  	public function zone()
	{
		return $this->roomData();
	}

	public function mapData()
	{
		$mapData = $this->roomData();
		$zone = $mapData['z'];
		$query = $this->dbh->prepare('SELECT `image` 
 			FROM `roommap` 
			WHERE `z` = :z');
		
		$query->execute(array(
			':z' => $zone
			));

		$zoneData = $query->fetch();
		return $zoneData;
	}

	public function mapImage()
	{
		//return '<img src="'.$this->mapData().'">';
		$mapData = $this->mapData();
		$image = $mapData['image'];
		return $image;
	}
	
	public function movePlayer($direction)
	{
		$roomData = $this->roomData();
		$x = $roomData['x'];
		$y = $roomData['y'];
		$z = $roomData['z'];
		switch ($direction)
		{
			case up:
				--$y;
				break;
			case down:
				++$y;
				break;
			case left:
				--$x;
				break;
			case right:
				++$y;
				break;
		}
		
		$query = $this->dbh->prepare('SELECT `roomid` FROM `room`
			WHERE `x` = :x
			AND `y` = :y
			AND `z` = :z');

		$query->execute(array(
			':x' => $x,
			':y' => $y,
			':z' => $z
			));
			
		if ($query->rowCount() > 0)
		{

			$moveData = $query->fetch();
			$newroom = $moveData['roomid'];
			echo ''.$newroom.'';
		}
		else
		{
			die();
		}
	}
}

$userid = $_SESSION['userid'];
$users = $dbh->prepare('SELECT `roomid` FROM `stats` WHERE `id` = :id');
$users->execute(array(':id'=>$userid));
$user = $users->fetch();
$roomid = $user['roomid'];

//usage -->

$world = new WorldMap($dbh, $roomid);


if (empty($_GET['direction']))
{

	echo 'Zone: '. $world->mapImage() . ' ';


	echo '<br /><br /><a href="world.php?direction=left">left</a>';
	
}


echo '<br /><br />no errors.';

?>
