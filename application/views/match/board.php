
<!DOCTYPE html>

<html>
	<head>
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	<script>

		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var status = "<?= $status ?>";
		
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
						echo "<div class='inner' id='controller_tile'><img id='img' src=".  base_url() . "images/arrow1.png /></div>";
					}
					else{
						echo "<div id='controller_tile'><img id='img' src=" . base_url() . "images/arrow1.png /></div>";
					}
				}
			?>
		</div>
		<?php
		for($i=0; $i<64; $i++){
			if($i%8==0){
				echo "<div class='inner' id='tile'></div>"; 
			}
			else{
				echo "<div class='outer' id='tile'></div>";
			}
		}
		?>	



	</div>

	
	
	
	
</body>

</html>

