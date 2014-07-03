<?php

	class Akismettest extends Controller {
		
		function __contruct()
		{
			parent::Controller();
			
			//I cant unserstand this path of package you loaded . maybe it's codeigniter-akismet not  codeigniter-facebook 
			$this->load->add_package_path(APPPATH.'third_party/haughin/codeigniter-akismet/');
		}
		
		function index()
		{
			$this->load->library('akismet');

			// Comment array content:
			// 
			// permalink
			//     The permanent location of the entry the comment was submitted to.
			// comment_type
			//     May be blank, comment, trackback, pingback, or a made up value like "registration".
			// comment_author
			//     Submitted name with the comment
			// comment_author_email
			//     Submitted email address
			// comment_author_url
			//     Commenter URL.
			// comment_content
			//     The content that was submitted.
			
			$comment = 	array(
							'permalink' 			=> 'http://blog.com/my-post-page',
							'comment_type' 			=> 'comment',
							'comment_author' 		=> 'Joe Bloggs',
							'comment_author_email' 	=> 'joe@bloggs.com',
							'comment_author_url' 	=> 'http://www.joebloggsrocks.com',
							'comment_content' 		=> 'So, this is my comment, and I love n00bs'
						);
			
			var_dump($comment);
			
			echo 'checking comment';
			
			if ( $this->akismet->check($comment) )
			{
				// The comment isn't spam according to akismet!
				
				echo 'comment is not spam';
				
				// Ok, so let's say that we want to now submit this comment to akismet as 'spam'.
				$this->akismet->submit_spam($comment);
				
				echo 'comment submitted as spam';
			}
			else
			{
				// :( Sad faces all round, this looks like spam.
				
				echo 'comment is spam';
				
				// Wait! This isn't spam! I know it's not!
				// Let's mark it as 'ham' (which is good)
				
				$this->akismet->submit_ham($comment);
				
				echo 'comment submitted as ham';
			}
		}
	}
