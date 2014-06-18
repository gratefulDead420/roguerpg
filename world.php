<?php

/*
* script: world.php verson 2.0
* developed by gratefulDeadty
*/

require 'core/init.php';


$room = isset($_GET['room']);
$stmt = $dbh->prepare('SELECT id,roompic,north,east,south,west,name FROM world WHERE id=?');
$stmt->bindValue(1, $_GET['room']);
$stmt->execute();
$room_check = $stmt->rowCount();

if ($room_check == 0)
{
        die('<div class="error_msg">Error: That room does not exist.</div>');
}

if ($_GET['room'] && empty($errors) === true)
{
        //$stmt = $dbh->prepare('SELECT * FROM world WHERE id=?');
        //$stmt->bindValue(1, $_GET['room']);
        //$stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $room)
        {        
                //echo ' '.$room['name'].' ';
                //echo '<br />';
                //echo '<img src="images/'.$room['roompic'].'">';

                $north = $room['north'];
                $south = $room['south'];
                $east = $room['east'];
                $west = $room['west'];

                if ($north > 0)
                {
	                $north = $north;
                }
                else
                {
	                $north = $_GET['room'];
                }

                if ($south > 0)
                {
	                $south = $south;
                }
                else
                {
	                $south = $_GET['room'];
                }


                if ($east > 0)
                {
	                $east = $east;
                }
                else
                {
	                $east = $_GET['room'];
                }

                if ($west > 0)
                {
	                $west = $west;
                }
                else
                {
	                $west = $_GET['room'];
                }


        ?>
                <!-- start pageUpdate ajax load. -->
                <!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script> -->
		<script type="text/javascript">
		var key1="87";  //W
		var key2="119"; //w
		var key3="83";  //S
		var key4="115"; //s
		var key5="68";  //D
		var key6="100"; //d
		var key7="65";  //A
		var key8="97";  //a
		var x='';
		
		//.ajax function that will create our new room load.
		//in the future, i'd like to make this a websocket load.
                function pageUpdate(roomid) 
                { 
                        $('#world_loading').html("<img src='images/ajaxloading.gif'>");
                        var grbData = $.ajax({
                        type: 'GET',
		        cache: false,
                        url : "http://tuts.site40.net/rpg/world.php?room="+roomid,
                        success: function (html) 
                        {
                                $("#world_create").html(html);
                        }
                        });
                }

		function handleKeyboard(evt)
		{
			evt=(evt)?evt:((window.event)?event:null);
			if (evt)
			{
				if(evt.keyCode==87)
				{
					pageUpdate('<?php echo ' '.$north.' ' ?>');
				}
				if(evt.keyCode==68)
				{
					pageUpdate('<?php echo ' '.$east.' '; ?>');
				}
				if(evt.keyCode==65)
				{ 
					pageUpdate('<?php echo ' '.$west.' '; ?>');
				}
				if(evt.keyCode==83)
				{
					pageUpdate('<?php echo ' '.$south.' '; ?>');
				}
			}
		}
		document.onkeyup = handleKeyboard;
		</script>
<!-- begin page content. -->
<div align='center'>
<div id="world_create">
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
				<img border="0" src="images/<?php echo ''.$room['roompic'].'';?>" usemap="#map"/></td>
	</tr>
	<tr>
	<tr style="border: 1px solid #000000; border-collapse: collapse;" bgcolor="#333333"><td width="250"><center>

<?php
/*
$north = $room['north'];
$west = $room['west'];
$east = $room['east'];
$south = $room['south'];
*/
//left,right,up,down image links.
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

echo '<span id="world_loading"></span>'; //loading image from the ajax request.

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


        }
//}

?>

<!-- Mobs -->

	</tr>
			</table>
		</td>
		<td valign="top" bgcolor="#333333">
			<table width="250" valign="top" bgcolor="#333333">
	<tr>
		<td bgcolor="#2b2b2b" style="border:1px solid #000000;"><span style="font:family:verdana;font-size:9px;color:#d69820"><div align="center"><strong>Creatures in this room:</strong></div></span></td>
		<?php
		$stmt = $dbh->prepare('SELECT mobid,hex FROM `roommobs` WHERE `roomid`=? ');
		$stmt->bindValue(1, $_GET['room']);
		$query = $stmt->execute();
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $roommob)
                {
                        $mobid = $roommob['mobid'];
                        $hex = $roommob['hex'];
               
                        $stmt = $dbh->prepare('SELECT name,level,trainer,rage FROM `mobs` WHERE `mobid`=? ');
                        $stmt->bindValue(1, $roommob['mobid']);
                        $stmt->execute();
                        $mobrow = $stmt->fetch();
                        
                        echo '<tr><td>';
			$name = $mobrow['name'];
                        $moblevel = $mobrow['level'];
                        $trainer = $mobrow['trainer'];
                        $rage = $mobrow['rage'];
                        $mobtitle = 'viewmobs';
		?>

<div style="border-bottom:1px dotted; border-color:#000000; height:15px;">

<a onclick="attackMob(<?php echo $mobid; ?>,'<?php echo $hex; ?>');">
	                
<img align="right" border="0" alt="Attack!" src="images/attackplayericon.jpg" style="cursor:pointer;" onMouseOver="menutip('Attack <?php echo ' '.$mob['name'].' '; ?>!');" onMouseOut="hideddrivetip();"></a> 

<a href="<?php echo $mobtitle; ?>.php?mob=<?php echo $mobid; ?>" style="cursor:pointer;"><?php echo ' '.$name.' '; ?></td>

</div>  
	</tr>
   <?php
                        }
                //}
                ?>
   </table>
</div>
<?php 
}

?>   
