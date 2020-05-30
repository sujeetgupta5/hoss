<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Authanticate extends CI_Controller {

	function index() {
		var_dump($_POST);
		echo $this->input->post('username');
	}
	
	function getlogo() {
		list($sitetitle,$sitelink,$sitelogo)=getMultiValue("settings",array("site_name","site_link","logo"),"id",1);	
		echo $sitelogo;
	}

	function test_get(){
		$id = $this->session->userdata('id');
		$id = $this->session->userdata('apikey');
		var_dump($id);
		echo $id == false;
		// echo 'Total Results: ' . $query->num_rows();
	}

	function get_get(){
		echo 123;
	}
	
	function getid(){
		$apikey=$_REQUEST['id'];
		$q = $this->db->query("select user_id from apikeys where keyval='{$apikey}' and date_created >= now() - INTERVAL 1 DAY ");
		if($q->num_rows>0)
		{
			$r=$q->result();
			$date_created=intval($r[0]->user_id);
			
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => $date_created));
			return;
		} else {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('error' => 'failed'));
			return;	
		}
		return;
	}
	
	function checkjcr(){
		echo $this->session->userdata('jcr');
		return;
	}
	
	function chkwork(){
		echo $this->session->userdata('work order');
		return;
	}
	
	function chkusers(){
		echo $this->session->userdata('users');
		return;
	}
	
	function login() {
		// $rawpostdata = file_get_contents("php://input");
		// $post = json_decode($rawpostdata, true);
		// $username = $post['username'];
		// $password = $post['password'];
		if (!isset($_POST['username']) || !isset($_POST['password'])) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'invalid parameters'));
			return;
		}
		$username = $_POST['username'];
		$password = $_POST['password'];
		if (empty($username) || empty($password) || !preg_match('/^[a-zA-Z0-9_]*$/', $username)) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'fail', 'error' => 'invalid input'));
			return;
		}


		$username = addslashes($username);
		$password = addslashes($password);
		$password = md5($password);

		$query = $this->db->query("SELECT id, username, password, avatar, roles, email FROM user WHERE username='{$username}' and password='{$password}' and status=1 ");
		//jcr_modules, users_modules, work_modules, tickets_modules,jobs_modules, ivc_modules,timecard_modules,
		if($query->num_rows > 0){
			$result = $query->result();
			$id = $result[0]->id;
			$username = $result[0]->username;
			$avatar = $result[0]->avatar;
			$roles = $result[0]->roles;
			$email = $result[0]->email;
			
			/*$jcr_modules = $result[0]->jcr_modules;
			$users_modules = $result[0]->users_modules;
			$work_modules = $result[0]->work_modules;
			$tickets_modules = $result[0]->tickets_modules;
			$jobs_modules = $result[0]->jobs_modules;
			$ivc_modules = $result[0]->ivc_modules;
			$timecard_modules = $result[0]->timecard_modules;*/
			$roles = $result[0]->roles;
			
			/*if($modules!='')
			{
				$modules=explode(',',$modules);
				foreach($modules as $k=>$v)
				{
					$this->session->set_userdata($v, 1);
				}
			}*/
			$this->db->select("u.id, u.employee_id, e.first_name");
			$this->db->from("user u");
			$this->db->join('employees e', 'u.employee_id=e.id', 'left');
			$this->db->where('u.id',$id);
			$name_query = $this->db->get();		
			$name_arr = $name_query->result_array();
			$this->session->set_userdata('user_display_name', $name_arr[0]['first_name']);
			if($name_arr[0]['employee_id']<=0) {
				$this->session->set_userdata('user_display_name', $username);		
			}
			
			$this->db->where('id', $id);
			$this->db->update('user',array('last_login'=>date('Y-m-d h:i:s')));
			
 			$this->session->set_userdata('id', $id);
			$this->session->set_userdata('username', $username);
			$this->session->set_userdata('avatar', $avatar);
			$apikey = md5($id . $username . time());
			$this->session->set_userdata('apikey', $apikey);
			
			$this->load->config('aws_sdk');
			$this->session->set_userdata('avatar', $this->config->item('hallen_bucket').'/'.$this->config->item('environment_path').'/'.$this->config->item('users_module').'/'.$avatar);
			/*$this->session->set_userdata('jcr_modules', $jcr_modules);
			$this->session->set_userdata('users_modules', $users_modules);
			$this->session->set_userdata('work_modules', $work_modules);
			$this->session->set_userdata('tickets_modules', $tickets_modules);
			$this->session->set_userdata('jobs_modules', $jobs_modules);
			$this->session->set_userdata('ivc_modules', $ivc_modules);
			$this->session->set_userdata('timecard_modules', $timecard_modules);*/
			
			$this->session->set_userdata('roles', $roles);
			$this->session->set_userdata('email', $email);

			$data = array(
				'keyval' => $apikey,
				'user_id' => $id
			);
			$this->db->query("DELETE from apikeys where user_id='{$id}'");
			$this->db->insert('apikeys', $data);

			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'successs', 'apikey' => $apikey));
			// $this->response(array('status' => 'successs'), 200);
		}else{
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'fail'));
			// $this->response(array('status' => 'fail'), 200);
		}
		// echo 'Total Results: ' . $query->num_rows();
	}
	
	function passupdate(){
  

		$guid     = $this->input->post('id');
		$password = $this->input->post('password');
    
    $q1 = $this->db->query("select user_id from apikeys where keyval='{$guid}' and date_created >= now() - INTERVAL 1 DAY ");
    if($q1->num_rows>0)
		{
      	$rdata = $q1->result();
        $id =  $rdata[0]->user_id;
        
        
        $sql = "DELETE from `apikeys` where `user_id`='{$id}'";
         
        //Delete apikey
    		$this->db->query($sql = "DELETE from `apikeys` where `user_id`='{$id}'");
    		
      
        
    		$q = $this->db->query("select * from user where id='".$id."' ");
        if($q->num_rows>0)
    		{
    			$r = $q->result();
    			$email=$r[0]->email;
    			$this->db->where('id', $id);
    			$this->db->update('user',array('password'=>MD5($password)));
    		
          
    			$this->load->helper('url');
    			$burl=base_url();
    			$baseurl=$burl;
    
    			$this->load->library('email');
    			$this->email->set_mailtype("html");
    			$this->email->from('no-reply@hallenconstruction.com', 'Hallen Construction');
    			$this->email->to($email);
    			$this->email->subject('(PLEASE IGNORE - FOR TESTING PURPOSES ONLY) Your new password reset successfully');
    			$body='Your password successfully reset, You should login now with new password: <a href="'.$baseurl.'">Login</a>.';
    			$this->email->message($body);
    			$this->email->send();
    			
    			$data = "Password successfullt reset please login with new password";
    			header("HTTP/1.1 200 OK");
    			echo json_encode(array('status' => $data));
    			return;
            }
    			header("HTTP/1.1 200 OK");
    			echo json_encode(array('error' => 'duplicate'));
    			return;
    		//header("HTTP/1.1 200 OK");
    		//echo json_encode(array('error' => 'notmatched'));
    		//return;
    
    }
    
   
		
	
	}
	
	function resetpass(){
		$username= $_POST['username'];
		if (empty($username))
		{
			//$this->response(array('status' => 'false', 'error' => 'Enter valid email address.'), 403);
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'Enter valid username.'));
			return;
		}

		$q = $this->db->query("select * from user where username='".$username."' ");
        if ($q->num_rows > 0)
		{
			$r = $q->result();
			//print_r($r);
            $id=$r[0]->id;
			$email=$r[0]->email;
			
			$qq = $this->db->query("select * from apikeys where user_id='".$id."' ");
			if($qq->num_rows>0)
			{
				$rr=$qq->result();
				$date_created=$rr[0]->date_created;
				if(time()-strtotime($date_created) > 60*60*24)
				{
					$this->db->query("DELETE from `apikeys` where `user_id`='{$id}'");
					$apikey = md5($id . $username . time());
					$data = array(
						'keyval' => $apikey,
						'user_id' => $id,
						'date_created' => date('Y-m-d h:i:s')
					);
					$this->db->insert('apikeys', $data);
					$this->resetpassword($apikey,$email);
					
					$data = "Reset password link again send to: ". $email;
					header("HTTP/1.1 200 OK");
					echo json_encode(array('status' => $data));
					return;
				} else {
					
					header("HTTP/1.1 200 OK");
					echo json_encode(array('error' => 'failed'));
					return;
				}
				
				
			} else {
				$apikey = md5($id . $username . time());
				$data = array(
					'keyval' => $apikey,
					'user_id' => $id,
					'date_created' => date('Y-m-d h:i:s')
				);
				$this->db->insert('apikeys', $data);
				$this->resetpassword($apikey,$email);
				
				$data = "Reset password link send to: ". $email;
				header("HTTP/1.1 200 OK");
				echo json_encode(array('status' => $data));
				return;
			}
			
        }
			header("HTTP/1.1 200 OK");
			//echo json_encode(array('error' => 'Your email address not registered.'));
			echo json_encode(array('error' => 'duplicate'));
			//$this->response(array('status' => 'false', 'error' => 'Your email address not registered.'), 403);
	}
	
	private function resetpassword($apikey,$email)
	{       
  
  
		$this->load->helper('string');
		//$password= random_string('alnum', 16);
		//$this->db->where('id', $user->id);
		//$this->db->update('user',array('password'=>MD5($password)));
		$this->load->helper('url');
		$burl=base_url();
		//echo $email;
		$baseurl=$burl.'#/reset/'.$apikey;
		//$baseurl= $this->config->base_url().'reset/'.$user->id;
		//$link=base64_encode($user);
		$this->load->library('email');
		$this->email->set_mailtype("html");
		$this->email->from('no-reply@hallenconstruction.com', 'Hallen Construction');
		$this->email->to($email);
		$this->email->subject('(PLEASE IGNORE - FOR TESTING PURPOSES ONLY) Your Password Reset Link');
		//$this->email->message('You have requested the new password, Here is you new password: ');
		$body='Please <a href="'.$baseurl.'">click here</a> to reset your password. <br /><br /> You should reset new password within 24 hours using this link otherwise you need to again reset that.';
		$this->email->message($body);
		$this->email->send();
    

	}
	
	function signup(){
		// $rawpostdata = file_get_contents("php://input");
		// $post = json_decode($rawpostdata, true);
		// $username = $post['username'];
		// $password = $post['password'];
		if (!isset($_POST['username']) || !isset($_POST['password'])) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'invalid parameters'));
			return;
		}
		$username = $_POST['username'];
		$password = $_POST['password'];
		if (empty($username) || empty($password) || strlen($username) > 20 || strlen($password) > 20 || 
			!preg_match('/^[a-zA-Z0-9_]*$/', $username)) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'invalid parameters'));
			return;
		}

		$username = addslashes($username);
		$password = addslashes($password);

		$password = md5($password);

		$query = $this->db->query("SELECT username, password FROM user WHERE username='{$username}'");
		if ($query->num_rows > 0) {
			// $this->response(array('error' => 'duplicate user'), 200);
			header("HTTP/1.1 200 OK");
			echo json_encode(array('error' => 'duplicate'));
		} else {
			$data = array(
				'username' => $username,
				'password' => $password,
			);

			$this->db->insert('user', $data); 
			$query = $this->db->query("SELECT id FROM user WHERE username='{$username}'");
			
			//set session after user signup successfully
			if ($query->num_rows == 1) {
				$result = $query->result();
				$id = $result[0]->id;
				$this->session->set_userdata('id', $id);
				$apikey = md5($id . $username . time());
				$this->session->set_userdata('apikey', $apikey);

				$data = array(
					'keyval' => $apikey,
					'user_id' => $id
				);

				$this->db->insert('apikeys', $data);
				// $this->response(array('status' => 'success', 'id' => $id), 200);
				header("HTTP/1.1 200 OK");
				echo json_encode(array('status' => 'success', 'id' => $id, 'apikey' => $apikey));
			}
		}

		// echo 'Total Results: ' . $query->num_rows();
	}

	function logout(){
		$apikey = $this->session->userdata('apikey');
		$this->session->sess_destroy();
		if (!empty($apikey)) {
			$this->db->query("DELETE from `apikeys` where `keyval`='{$apikey}'");
		}
		// return $this->response(array('status' => 'success'), 200);
		header("HTTP/1.1 200 OK");
		echo json_encode(array('status' => 'successs'));
		// echo 'Total Results: ' . $query->num_rows();
	}

	function login_get(){
		$id = $this->session->userdata('id');
		return $id !== FALSE? $this->response(array('status' => 'true'), 200): $this->response(array('status' => 'false'), 200);
		// echo 'Total Results: ' . $query->num_rows();
	}


	function user_get()
    {
        if(!$this->get('id'))
        {
        	$this->response(NULL, 400);
        }

        // $user = $this->some_model->getSomething( $this->get('id') );
    	$users = array(
			1 => array('id' => 1, 'name' => 'Some Guy', 'email' => 'example1@example.com', 'fact' => 'Loves swimming'),
			2 => array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com', 'fact' => 'Has a huge face'),
			3 => array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com', 'fact' => 'Is a Scott!', array('hobbies' => array('fartings', 'bikes'))),
		);
		
    	$user = @$users[$this->get('id')];
    	
        if($user)
        {
            $this->response($user, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'User could not be found'), 404);
        }
    }
    
    function user_post()
    {
        //$this->some_model->updateUser( $this->get('id') );
        $message = array('id' => $this->get('id'), 'name' => $this->post('name'), 'email' => $this->post('email'), 'message' => 'ADDED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
    
    function user_delete()
    {
    	//$this->some_model->deletesomething( $this->get('id') );
        $message = array('id' => $this->get('id'), 'message' => 'DELETED!');
        
        $this->response($message, 200); // 200 being the HTTP response code
    }
    
    function users_get()
    {
        //$users = $this->some_model->getSomething( $this->get('limit') );
        $users = array(
			array('id' => 1, 'name' => 'Some Guy', 'email' => 'example1@example.com'),
			array('id' => 2, 'name' => 'Person Face', 'email' => 'example2@example.com'),
			3 => array('id' => 3, 'name' => 'Scotty', 'email' => 'example3@example.com', 'fact' => array('hobbies' => array('fartings', 'bikes'))),
		);
        
        if($users)
        {
            $this->response($users, 200); // 200 being the HTTP response code
        }

        else
        {
            $this->response(array('error' => 'Couldn\'t find any users!'), 404);
        }
    }


	public function send_post()
	{
		var_dump($this->request->body);
	}


	public function send_put()
	{
		var_dump($this->put('foo'));
	}
	
	/*Get all data from database*/
	function roleslist()
	{
		$query = $this->db->query("SELECT * FROM user_roles WHERE 1=1 order by role asc ");
		$result = $query->result();
		$data = array();
		foreach($result as $item){
			// $data->title = $item.title;
			// $data->desc = $item.desc;
			// $data->id = $item.id;
			// $data->amount = $item.amount;
			// $item->comments = $this->getComments($item->id);
		}
		print_r(json_encode($result));
		//header("HTTP/1.1 200 OK");
		//echo json_encode(array('data' => $result));
		//return;
	}
	/*Get all data from database*/
	
	/*Get all data from database*/
	function getlist()
	{
		$query = $this->db->query("SELECT u.*, if(u.status=1,'Active','Blocked') as status, r.role FROM user u left join user_roles r ON r.id=u.roles WHERE 1=1 order by u.id desc ");
		$result = $query->result();
		$data = array();
		foreach($result as $item){
			// $data->title = $item.title;
			// $data->desc = $item.desc;
			// $data->id = $item.id;
			// $data->amount = $item.amount;
			// $item->comments = $this->getComments($item->id);
		}
		print_r(json_encode($result));
		//header("HTTP/1.1 200 OK");
		//echo json_encode(array('data' => $result));
		//return;
	}
	/*Get all data from database*/
	
	/*Get all data from database*/
	function getlist1()
	{
		$id=intval($_REQUEST['id']);
		$query = $this->db->query("SELECT u.*,  r.role FROM user u left join user_roles r ON r.id=u.roles WHERE u.id={$id} order by u.id desc ");
		//if(u.status=1,'Active','Blocked') as status,
		$result = $query->result();
		$data = array();
		foreach($result as $item){
			// $data->title = $item.title;
			// $data->desc = $item.desc;
			// $data->id = $item.id;
			// $data->amount = $item.amount;
			// $item->comments = $this->getComments($item->id);
		}
		//print_r(json_encode($result));
		header("HTTP/1.1 200 OK");
		echo json_encode(array('data' => $result));
		return;
	}
	/*Get all data from database*/
	
	function allDelete()
	{
		$ids = implode(",",array_filter($_REQUEST['user_ids']));
		$this->db->query("DELETE FROM user WHERE id IN (".$ids.")");
		//$this->getlist();
		return;
	}
	
	/*Delete single record.*/
	function deletesingle()
	{
		$id = $_REQUEST['id'];
		$query = $this->db->query("SELECT * from user WHERE id='{$id}' ");
		if ($query->num_rows === 0) {		//nothing to delete in the DB
			header("HTTP/1.1 200 OK");
			echo json_encode(array('data' => 'not exists'));
			return;
		} else {
			$this->db->query("DELETE from user where id='{$id}' ");
			header("HTTP/1.1 200 OK");
			echo json_encode(array('status' => 'success'));
			return;
		}
	}
	
	/*Add single record*/
	function add() {
		$username=stripslashes($_REQUEST['username']);
		$email=stripslashes($_REQUEST['email']);
		$projects=implode(",",array_filter($_REQUEST['projects']));
		
		$data = array(
			'username' 	=> stripslashes($_REQUEST['username']),
			'password' 	=> md5($_REQUEST['password']),
			'email' 	=> stripslashes($_REQUEST['email']),
			'status' 	=> intval($_REQUEST['status']),
			'roles' 	=> intval($_REQUEST['roles']),
			'projects' 	=> $projects
		);
		
		$query = $this->db->query("SELECT id FROM user WHERE username='{$username}'");	
		if ($query->num_rows == 1) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('error' => 'duplicate'));
			return;
		}
		$query = $this->db->query("SELECT id FROM user WHERE email='{$email}'");
		if ($query->num_rows == 1) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('error' => 'duplicate email'));
			return;
		}
			
		$this->db->insert('user', $data);
		$id = $this->db->insert_id();
		if($id>0)
		{
			header("HTTP/1.1 200 OK");
			echo json_encode(array('data' => 'success'));
			return;
		}
	}
	/*Add single record*/
	
	/*Update single record*/
	function update(){
		$id=intval($_REQUEST['id']);
		$username=stripslashes($_REQUEST['username']);
		$email=stripslashes($_REQUEST['email']);
		
		$projects=implode(",",$_REQUEST['projects']);
		$modules=implode(",",$_REQUEST['modules']);
		if($_REQUEST['password']!=''){ $password = md5($_REQUEST['password']); } else { $password=$_REQUEST['oldpassword']; }
		
		$data = array(
			'username' 	=> stripslashes($_REQUEST['username']),
			'password' 	=> $password,
			'email' 	=> stripslashes($_REQUEST['email']),
			'status' 	=> intval($_REQUEST['status']),
			'roles' 	=> intval($_REQUEST['roles']),
			'projects' 	=> $projects,
			'modules' 	=> $modules,
		);
		
		$query = $this->db->query("SELECT id FROM user WHERE username='{$username}' and id!='{$id}' ");
		if ($query->num_rows == 1) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('error' => 'duplicate'));
			return;
		}
		$query = $this->db->query("SELECT id FROM user WHERE email='{$email}' and id!='{$id}' ");
		if ($query->num_rows == 1) {
			header("HTTP/1.1 200 OK");
			echo json_encode(array('error' => 'duplicate email'));
			return;
		}

		$this->db->where('id', $id);
		$this->db->update('user', $data);

		header("HTTP/1.1 200 OK");
		echo json_encode(array('data' => 'success'));
		return;
	}
	/*Update single record*/
}
