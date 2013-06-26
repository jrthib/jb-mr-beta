<?php
require('jb-basecamp.config.php');

class JB_Basecamp {
	
	var $db;
	var $jb_beta_table;
	var $config;
	
	function __construct($jb_table) {
		global $wpdb;
		$this->db =& $wpdb;
		$this->jb_beta_table = $jb_table;
	
		$this->config = array(
		    'baseUri' => BASECAMP_API,
		    'username' => BASECAMP_USERNAME,
		    'password' => BASECAMP_PASSWORD
		);
	}
	
	function createTodo($data) {
		/*
		
		p4458862
		
		*/
		
		$postData = array(
			'content' => $data['todoText'],
			'due_at' =>  date("c", strtotime("+1 week")),
			'assignee' => array(
				'id' => 848138, // alyssa
				'type' => 'Person'
		);
		
		$listID = get_option('jb_beta_basecamp_list_id');
		$projectID = get_option('jb_beta_basecamp_project_id');

		$todoItem = $this->httpRequest(array(
			'method' => 'POST',
			'endpoint' => 'projects/'. $projectID .'/todolists/'. $listID .'/todos.json',
			'data' => $postData
		));
		
		$todoItem = json_decode($todoItem);
		$todoID = $todoItem->id;
		
		$attachment = $this->httpAttachmentRequest(array(
			'method' => 'POST',
			'endpoint' => 'attachments.json',
			'data' => '@'.$data['screenshot']
		));
		
		$attachment = json_decode($attachment);
		$attachToken = $attachment->token;
		
		
		$commentData = array(
			'content' => 'Submitted by: '.$data['name'].' -- URL: '.$data['url'],
			'attachments' => array(
				array(
				'token' => $attachToken,
				'name' => $data['screenshot_filename']
				)
			)
		);
		
		$comment = $this->httpRequest(array(
			'method' => 'POST',
			'endpoint' => 'projects/'. $projectID .'/todos/'. $todoID .'/comments.json',
			'data' => $commentData
		));
		
		$comment = json_decode($comment);
		
		if(isset($comment->id) && is_int($comment->id)) {
			return 201;
		} else {
			return "There was an error posting this issue.";
		}
		
		
	}
	
	function httpRequest($args) {
	
		try {
			$ch = curl_init($this->config['baseUri'] . $args['endpoint']);
			if (FALSE === $ch) {
	        	throw new Exception('failed to initialize');
			}
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $this->config['username'] . ":" . $this->config['password']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json', 'Content-length: '.strlen(json_encode($args['data']))));
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mr Beta (mrbeta@juliabalfour.com)");
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			if($args['method'] == "POST") {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args['data']));
			}
			
			$response = curl_exec($ch);
			if (FALSE === $response) {
	        	throw new Exception(curl_error($ch), curl_errno($ch));
			}
			
			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			return $response;
		
		} catch(Exception $e) {
	
	    	trigger_error(sprintf(
	        	'Curl failed with error #%d: %s',
	        	$e->getCode(), $e->getMessage()),
	        	E_USER_ERROR);
	
	    }
		
	}
	
	function httpAttachmentRequest($args) {
	
		$exec = "/usr/bin/curl -k --data-binary ".$args['data']." -u {$this->config['username']}:{$this->config['password']} -H 'Content-Type: image/png' -H 'User-Agent: Mr Beta (mrbeta@juliabalfour.com)' ". $this->config['baseUri'] . $args['endpoint'];
		//$exec = escapeshellcmd($exec);
		//return exec($exec);
		return shell_exec($exec);
		/*
		try {
		
			$ch = curl_init($this->config['baseUri'] . $args['endpoint']);
			if (FALSE === $ch) {
	        	throw new Exception('failed to initialize');
			}
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERPWD, $this->config['username'] . ":" . $this->config['password']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: image/png', 'Content-length: '.strlen($args['data'])));
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mr Beta (mrbeta@juliabalfour.com)");
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $args['data']);
			
			$response = curl_exec($ch);
			if (FALSE === $response) {
	        	throw new Exception(curl_error($ch), curl_errno($ch));
			}
			
			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			return $response;
		
		} catch(Exception $e) {
	
	    	trigger_error(sprintf(
	        	'Curl failed with error #%d: %s',
	        	$e->getCode(), $e->getMessage()),
	        	E_USER_ERROR);
	
	    }
	    */
		
	}
	
}

/*

curl -k -u mrbeta:2bulld0gs -H 'Content-Type: application/json' -H 'User-Agent: Testing (mrbeta@juliabalfour.com)' -d '{ "content": "test", "due_at": "2013-05-07T18:58:16+00:00" }' https://basecamp.com/1799662/api/v1/projects/1432926/todolists/3463572/todos.json

curl -u mrbeta:2bulld0gs -H 'Content-Type: application/json' -H 'User-Agent: Testing (mrbeta@juliabalfour.com)' https://basecamp.com/1799662/api/v1/projects/2043297/todolists/7093412.json

curl -k -u mrbeta:2bulld0gs -H 'User-Agent: Testing (mrbeta@juliabalfour.com)' https://basecamp.com/1799662/api/v1/projects/2043297/todolists/7093412.json

curl -k -u mrbeta:2bulld0gs -H 'User-Agent: MyApp (mrbeta@juliabalfour.com)' https://basecamp.com/1799662/api/v1/projects.json

curl -k -u mrbeta:2bulld0gs -H 'User-Agent: MyApp (mrbeta@juliabalfour.com)' https://basecamp.com/1799662/api/v1/projects/1432926/todolists.json

*/



?>