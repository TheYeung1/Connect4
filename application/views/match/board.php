
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
		//should actually be columns
		var gamecolumns = [[], [], [], [], [], [], [], []]
		//player1 gets to go first 
		$(document).ready(function(){
			//we should colour the selectors red to indicate that its not currently their turn
			if(myid!=player1){
				$('.controller_tile').css("background-color", "red");
			}
			//add the event handlers to the controller tiles we only allow one click 
			//we will re-enable this event handler later after we update the board with the
			//other users move
			else{
				$('.controller_tile').each(function(){
					$(this).click(function(){clicky(this);});
					});
			}
		});

		//the workhorse of the click event handler
		function clicky(item){
			//we gotta take the event handlers of the rest of the chips
			$('.controller_tile').each(function(){
				$(this).off('click');
			});


			//get the column this chip will be placed in

			var column = $(item).attr("id");

			//before we animate the chip we have to figure out what row of the column it blongs in
			//its the length of the array minus one because rows in the PHP/HTML are indexed starting at 0
			var row = gamecolumns[column].length;
			var where = "dispatcher";
			animate(row, column, item, where);

			//now we have to send out what just happened so that the other player can get see it on their board

			//the idea here oscar is to send out what just happened to the database similar to what happens in the form js function near the end
			//also keep the everytime thing but instead of getMsg we just get last move from db! and then apply it!
			var url = '<?=base_url() ?>board/sendMove';

			//send the JSON get request and populate the database with the new move
			$.get(url,{row: row, column: column});
			//now we have to change the selectors to red for the user telling them its not their turn anymore
			$('.controller_tile').css('background-color', 'red');
			
		}

		/*
		* Given a chip, checks if the given chip is a winning chip
		*/

		function checkForWin(chip){

			playerId = chip.getPlayerId();
			return winInDirection(parseInt(chip.row), parseInt(chip.column), 0, 1, playerId) || // check to the right
				   winInDirection(parseInt(chip.row), parseInt(chip.column), 0, -1, playerId) || // check to the left
				   winInDirection(parseInt(chip.row), parseInt(chip.column), -1, 0, playerId) || // check down
				   winInDirection(parseInt(chip.row), parseInt(chip.column), 1, 1, playerId) || // check up and right
				   winInDirection(parseInt(chip.row), parseInt(chip.column), 1, -1, playerId) || // check up and left
				   winInDirection(parseInt(chip.row), parseInt(chip.column), -1, -1, playerId) || // check down and left
				   winInDirection(parseInt(chip.row), parseInt(chip.column), -1, 1, playerId); // check down and right
			
		}

		/*
		* Given the current position of the chip, and the directions to change in (dRow, dCol) and the player,
		* cheacks if the player has connected 4 in the given direction
		*/
		function winInDirection(currRow, currColumn, dRow, dCol, player){
			streak = 1;
			

			while (currColumn + dCol >= 0 && currColumn + dCol < gamecolumns.length 
				&& currRow + dRow >= 0 && currRow + dRow <= 7 
				&& gamecolumns[currColumn + dCol].length > (currRow + dRow)
				&& gamecolumns[currColumn + dCol][currRow + dRow].getPlayerId() == player){
				streak++;
				currRow = currRow + dRow;
				currColumn = currColumn + dCol;
			}
			return streak >= 4;

		}

		//animates the chip
		//item is the top selector jquery object (the coloured circles at the top)

		function animate(row,column,item,where){

			chipcount++;
			var chip  = new Chip(myid, row, column);
			gamecolumns[column].push(chip);

			if (checkForWin(chip)){
				console.log("win!");
			}

					
			//decide on the colour of the chip
			if(where=="dispatcher"){
				if(myid == player1){
					var color = 'yellow';
				}
				else{
					var color = 'red';
				}
			}
			//request came from not knowing about a move so the colours are reversed
			else{
				if(myid == player1){
					var color = 'red';
				}
				else{
					var color = 'yellow';
				}	

			}

			$("<div></div>",{id:"chip" + chipcount}).appendTo(item);
			$("#chip"+chipcount).css({"background-color":color, "border-radius":"100%", "width":"40px", "height":"40px", "position":"absolute"});

			var root_x = $("#buffer").offset().left;
			var position = $('[id="' + row + '"]' + '[data-column="' + column + '"]').offset();
			var x = position.left;
			var y = position.top;
			//now we have to get the offset of the parent tile so we dont really screw up the positioning of the chip
			var position2 = $(item).offset();
			var y2 = position2.top;
			//the constant numbers here are small tweaks to center the chip
			$("#chip"+chipcount).animate({ left:x-root_x+5 , top:y-y2+5}, 2000, "linear");

		}

		

		//here is where we sync with the database
		$(function(){
			$('body').everyTime(5000,function(){
				$.getJSON('<?= base_url() ?>board/getMatchUpdate',function(data,text,jqZHR){
					//check if data is not null
					if(data && !(data.status)){
						var row = data.row;
						var column = data.column;
						//now we check if we were already aware of this move
						var column_array = gamecolumns[column];
						for(var i = 0; i<column_array.length; i++){}
						if(i <= row){
							//want to add it to our board
							var item = $('.controller_tile[id="' + column + '"]');
							//tells click event handler where the request came from
							var where="checker";
							animate(row,column,item,where);
							//ok so we have added it to the board, now we have to tell the current user that it is their turn by changing their selectors to green and adding the event handlers
							$('.controller_tile').css("background-color", "green");
							$('.controller_tile').each(function(){
							$(this).click(function(){clicky(this);});
							});


						}
						else{
							//dont want to add it because we were already aware of this move
						}
					}

				});
			})
		});



		//message stuff i am probably going to keep in because it is kinda cool to have a message board

		//////////////////////////////////////////////////////////////////////START MESSAGE STUFF ////////////////////////////////////////////////////////////////////////////////
		/*$(function(){
			$('body').everyTime(1000,function(){
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
	*/
	////////////////////////////////////////////////////////////////////////////////////END MESSAGE STUFF //////////////////////////////////////////////////////////////////////////
	
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
<!--
	<?php 
	
	//echo form_textarea('conversation');
	
	//echo form_open();
	//echo form_input('msg');
	//echo form_submit('Send','Send');
	//echo form_close();
	
	?>
-->	
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

