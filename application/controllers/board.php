<?php

class Board extends CI_Controller {
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    } 
          
    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions	
    		
    		if (!isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
 	    	
	    	return call_user_func_array(array($this, $method), $params);
    }
    
    
    function index() {
		$user = $_SESSION['user'];
    		    	
	    	$this->load->model('user_model');
	    	$this->load->model('invite_model');
	    	$this->load->model('match_model');
	    	
	    	$user = $this->user_model->get($user->login);

	    	$invite = $this->invite_model->get($user->invite_id);
	    	
	    	if ($user->user_status_id == User::WAITING) {
	    		$data['player1'] = $invite->user2_id;
	    		$data['player2'] = $invite->user1_id;
	    		$otherUser = $this->user_model->getFromId($invite->user2_id);
	    	}
	    	else if ($user->user_status_id == User::PLAYING) {
	    		$match = $this->match_model->get($user->match_id);
	    		$data['player1']=$match->user1_id;
	    		$data['player2'] = $match->user2_id;
	    		if ($match->user1_id == $user->id)
	    			$otherUser = $this->user_model->getFromId($match->user2_id);
	    		else
	    			$otherUser = $this->user_model->getFromId($match->user1_id);
	    	}
	    	$data['user']=$user;
	    	$data['otherUser']=$otherUser;
	    	
	    	switch($user->user_status_id) {
	    		case User::PLAYING:	
	    			$data['status'] = 'playing';
	    			break;
	    		case User::WAITING:
	    			$data['status'] = 'waiting';
	    			break;
	    	}
	    	
		$this->load->view('match/board',$data);
    }

 	function postMsg() {
 		$this->load->library('form_validation');
 		$this->form_validation->set_rules('msg', 'Message', 'required');
 		
 		if ($this->form_validation->run() == TRUE) {
 			$this->load->model('user_model');
 			$this->load->model('match_model');

 			$user = $_SESSION['user'];
 			 
 			$user = $this->user_model->getExclusive($user->login);
 			if ($user->user_status_id != User::PLAYING) {	
				$errormsg="Not in PLAYING state";
 				goto error;
 			}
 			
 			$match = $this->match_model->get($user->match_id);			
 			
 			$msg = $this->input->post('msg');
 			
 			if ($match->user1_id == $user->id)  {
 				$msg = $match->u1_msg == ''? $msg :  $match->u1_msg . "\n" . $msg;
 				$this->match_model->updateMsgU1($match->id, $msg);
 			}
 			else {
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
 			}
 				
 			echo json_encode(array('status'=>'success'));
 			 
 			return;
 		}
		
 		$errormsg="Missing argument";
 		
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	function sendMove(){

 		//get the row and columns posted via get
 		$row = $this->input->get("row");
 		$column = $this->input->get("column");

 		//load up the all important models
 		$this->load->model("match_model");
 		$this->load->model("user_model");

 		//get the user to get the game id
 		$user = $_SESSION['user'];
 		$user = $this->user_model->getExclusive($user->login);
 		if($user->user_status_id != User::PLAYING){
 			$errormsg = "Not In Playing State!";
 			goto error;
 		}
 		$match = $this->match_model->get($user->match_id);
 		$id = $match->id;
 		//now actually do the updating of the match

 		$this->match_model->updateMatch($row,$column,$id);

 		echo json_encode(array('status'=>'success'));
 		return;

 		error:
 			echo json_encode(array('status'=>'failure', 'message'=>$errormsg));

 	}

 	function endMatch(){


 		$status = $this->input->get('status');

 		$this->load->model("match_model");
 		$this->load->model("user_model");



 		//get the user to get the game id
 		$user = $_SESSION['user'];
 		$user = $this->user_model->getExclusive($user->login);
 		if($user->user_status_id != User::PLAYING){
 			$errormsg = "Not In Playing State!";
 			echo json_encode(array('status'=>'failure', 'message'=>$errormsg));
 			return;
 		}

 		$this->db->trans_begin();

 		$this->user_model->updateStatus($match->user1_id, User::AVAILABLE);
 		$this->user_model->updateStatus($match->user2_id, User::AVAILABLE);

 		
 		$match = $this->match_model->get($user->match_id);
 		$this->match_model->updateStatus($match->id, $status);

 		if ($this->db->trans_status() === FALSE)
	    		return;
	    
	    // if all went well commit changes
	    $this->db->trans_commit();

 	}

 	function getMatchUpdate(){

 		$this->load->model("match_model");
 		$this->load->model("user_model");

 		//get the user to get the game id
 		$user = $_SESSION['user'];
 		$user = $this->user_model->getExclusive($user->login);
 		if($user->user_status_id != User::PLAYING){
 			$errormsg = "Not In Playing State!";
 			goto error;
 		}
 		$match = $this->match_model->get($user->match_id);
 		$id = $match->id;
 		$match = $this->match_model->getMove($id);
 		if(is_null($match)){
 			$errormsg = "Database Error, Cannot Update Game Board";
 			goto error;
 		}
 		
 		else{
 			$encoded_array = $match->board_state;
 			echo $encoded_array;
 			return;
 		}
 		
 		error:
 			echo json_encode(array('status'=>'failure', 'message'=>$errormsg));

 	}
 
	function getMsg() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 			
 		$user = $_SESSION['user'];
 		 
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		// start transactional mode  
 		$this->db->trans_begin();
 			
 		$match = $this->match_model->getExclusive($user->match_id);			
 			
 		if ($match->user1_id == $user->id) {
			$msg = $match->u2_msg;
 			$this->match_model->updateMsgU2($match->id,"");
 		}
 		else {
 			$msg = $match->u1_msg;
 			$this->match_model->updateMsgU1($match->id,"");
 		}

 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 		
 		// if all went well commit changes
 		$this->db->trans_commit();
 		
 		echo json_encode(array('status'=>'success','message'=>$msg));
		return;
		
		transactionerror:
		$this->db->trans_rollback();
		
		error:
		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	
 }

