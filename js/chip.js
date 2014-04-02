
function Chip(player, row, column){
	this.playerid = player;
	this.row = row;
	this.column = column;

	this.getPlayerId = function(){
		return this.playerid;
	}
}