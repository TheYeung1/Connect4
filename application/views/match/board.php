
<!DOCTYPE html>

<html>
	<head>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	<script src="<?= base_url() ?>/js/chip.js"></script>
	<script>

		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var status = "<?= $status ?>";
		var player1 = "<?= $player1	?>";
		var player2 = "<?= $player2 ?>";
		var myid = "<?= $user->id ?>";
		var otherid = "<?= $otherUser->id ?>";
		var chipcount = 0;
		var gamerows = [[], [], [], [], [], [], [], []]
		//player1 gets to go first 
		$(document).ready(function(){
			//we should colour the selectors red to indicate that its not currently their turn
			if(myid!=player1){
				$('.controller_tile').css("background-color", "red");
			}
			//add the event handlers to the controller tiles
			$('.controller_tile').each(function(){
				$(this).click(function(){
					//get the column this chip will be placed in
					var column = $(this).attr("id");
					//create a new chip and add it to game array. make sure it is associated with a player
					//another chip the number uniquely identifies each chip which is basically just a div.
					chipcount++;
					var chip  = new Chip(myid);
					gamerows[column].push(chip);

					//var offset = $(this).offset();
					//var x = offset.left;
					//var y = offset.top;
					
					//decide on the colour of the chip
					if(myid == player1){
						var color = 'yellow';
					}
					else{
						var color = 'red';
					}

					$("<div></div>",{id:"chip" + chipcount}).appendTo(this);
					$("#chip"+chipcount).css({"background-color":color, "border-radius":"100%", "width":"40px", "height":"40px", "position":"absolute"});
					//before we animate the chip we have to figure out what row of the column it blongs in
					//its the length of the array minus one because rows in the PHP/HTML are indexed starting at 0
					var row = gamerows[column].length - 1;
					//get the offset of the dest tile so we know where to stop the animation
					var position = $('[id="' + row + '"]' + '[data-column="' + column + '"]').position();
					var x = position.left;
					var y = position.top;
					//these numbers are seemingly magic but have to do with how the chips position must
					//be absolute and not relative (get some strange behaviour with relative)
					$("#chip"+chipcount).animate({ left:x-460 , top:y-195}, 2000, "linear");




				});
			});
		});
			

		$(function(){
			$('body').everyTime(2000,function(){
					if (status == 'waiting') {
						$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
								if (data && data.status=='rejected') {
									alert("Sorry, your invitation to play was declined!");
									window.location.href = '<?= base_url() ?>arcade/index';
								}
								if (data && data.status=='accepted') {
									status = 'playing';
									$('#status').html('Playing ' + otherUser);
								}
								
						});
					}
					var url = "<?= base_url() ?>board/getMsg";
					$.getJSON(url, function (data,text,jqXHR){
						if (data && data.status=='success') {
							var conversation = $('[name=conversation]').val();
							var msg = data.message;
							if (msg.length > 0)
								$('[name=conversation]').val(conversation + "\n" + otherUser + ": " + msg);
						}
					});
			});







			$('form').submit(function(){
				var arguments = $(this).serialize();
				var url = "<?= base_url() ?>board/postMsg";
				$.post(url,arguments, function (data,textStatus,jqXHR){
						var conversation = $('[name=conversation]').val();
						var msg = $('[name=msg]').val();
						$('[name=conversation]').val(conversation + "\n" + user + ": " + msg);
						});
				return false;
				});	






		});
	
	</script>

	<link rel="stylesheet" type="text/css" href="<?= base_url() ?>css/style.css">
	</head> 


<body>  
	<h1>Game Area</h1>

	<div>
	Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  
	</div>
	
	<div id='status'> 
	<?php 
		if ($status == "playing")
			echo "Playing " . $otherUser->login;
		else
			echo "Wating on " . $otherUser->login;
	?>
	</div>
	
	<div id="gameboard">
		<p id="name"> Connect 4! </p>
		<!--Tiles go here -->
		<div id="buffer">
			<?php
				for($i=0; $i<8; $i++){
					if($i==0){
						echo "<div class='controller_tile' id=$i><img id='img' src=".  base_url() . "images/arrow1.png /></div>";
					}
					else{
						echo "<div class='controller_tile' id=$i><img id='img' src=" . base_url() . "images/arrow1.png /></div>";
					}
				}
			?>
		</div>
		<?php
		for($i=7; $i>=0; $i--){
			for($j=0; $j<8; $j++){
				//Note: j is column while i is row
				echo "<div class='tile' data-column=$j id=$i ></div>";
			}
		}
		?>	



	</div>

	
	
	
	
</body>

</html>

