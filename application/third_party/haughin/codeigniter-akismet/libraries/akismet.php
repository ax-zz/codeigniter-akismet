<?php

	class Akismet {
		
		private $_obj = NULL;
		private $_api_key = '';
		private $_blog_url = '';
		private $_valid_key = FALSE;
		private $_base_url = 'rest.akismet.com/1.1/';
		
		private static $_curl_opts = array(
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_USERAGENT      => 'codeigniter-akismet-2.0'
		);
		
		function __contruct()
		{
			$this->_obj =& get_instance();
			
			$this->_obj->load->config('akismet');
			
			$this->_api_key 	= $this->_obj->config->item('akismet_api_key');
			$this->_blog_url 	= $this->_obj->config->item('akismet_blog_url');
			
			$this->_valid_key = $this->_verify_key();
			
			if ( !$this->_valid_key )
			{
				log_message('error', 'Akismet could not verify the api key: '.$this->_api_key.' for blog: '.$this->_blog_url);
			}
		}
		
		private function _post($url, $data)
		{
			$ch = curl_init();
			$opts = self::$_curl_opts;
			
			$opts[CURLOPT_POSTFIELDS] = http_build_query($data, null, '&');
			$opts[CURLOPT_URL] = $url;
			 
			curl_setopt_array($ch, $opts);
			
			$result = curl_exec($ch);
			curl_close($ch);
			
			return $result;
		}
		
		private function _verify_key()
		{	
			$url = 'http://'.$this->_base_url.'verify-key';
			$data = array(
						'key'	=> $this->_api_key,
						'blog'	=> $this->_blog_url
					);
					
			$response = $this->_post($url, $data);
			
			return ( $response[1] == 'valid' ) ? TRUE : FALSE;
		}
		
		private function _build_url($location = '')
		{
			return 'http://'.$this->_api_key.'.'.$this->_base_url.$location;
		}
		
		private function _build_comment($comment = array())
		{
			//	Structure of comment:
			//		blog (required)
			//			The front page or home URL of the instance making the request. For a blog or wiki this would be the front page. Note: Must be a full URI, including http://.
			//		user_ip (required)
			//		    IP address of the comment submitter.
			//		user_agent (required)
			//		    User agent information.
			//		referrer (note spelling)
			//		    The content of the HTTP_REFERER header should be sent here.
			//		permalink
			//		    The permanent location of the entry the comment was submitted to.
			//		comment_type
			//		    May be blank, comment, trackback, pingback, or a made up value like "registration".
			//		comment_author
			//		    Submitted name with the comment
			//		comment_author_email
			//		    Submitted email address
			//		comment_author_url
			//		    Commenter URL.
			//		comment_content
			//		    The content that was submitted.
			
			$this->load->library('user_agent');
			
			$data = array(
						'blog' 			=> $this->_blog_url,
						'user_ip' 		=> $_SERVER['REMOTE_ADDR'],
						'user_agent'	=> $this->agent->agent_string(),
						'referrer'		=> $this->agent->referrer()
					);
					
			return array_merge($data, $comment);
		}
		
		private function _submit_comment($uri = '', $comment = array())
		{
			// Check the key is valid.
			
			if ( !$this->_valid_key ) return NULL;
			
			//	Structure of comment (sent from controller):
			//		permalink
			//		    The permanent location of the entry the comment was submitted to.
			//		comment_type
			//		    May be blank, comment, trackback, pingback, or a made up value like "registration".
			//		comment_author
			//		    Submitted name with the comment
			//		comment_author_email
			//		    Submitted email address
			//		comment_author_url
			//		    Commenter URL.
			//		comment_content
			//		    The content that was submitted.
			
			$url 	= $this->_build_url($uri);
			$data 	= $this->_build_comment($comment);
			
			$result = $this->_post($url, $data);
			
			return ( $result == 'true' ) TRUE : FALSE;
		}
		
		public function check($comment = array())
		{
			return $this->_submit_comment('comment-check', $comment);
		}
		
		public function submit_ham($comment = array())
		{
			return $this->_submit_comment('submit-ham', $comment);
		}
		
		public function submit_spam($comment = array())
		{
			return $this->_submit_comment('submit-spam', $comment);
		}
	}