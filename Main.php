<?php

    namespace IdnoPlugins\IdnoEmailPosting {

        class Main extends \Idno\Common\Plugin {

            /**
             * Find a user based on the secret email address they can use to post to.
             * @param type $email
             * @return boolean
             */
            static function getUserBySecretEmail($email) {
                if ($result = \Idno\Core\site()->db()->getObjects('Idno\\Entities\\User', array('secret_email' => $email), null, 1)) {
                    foreach ($result as $row) {
                        return $row;
                    }
                }
                return false;
            }
            
            function callAction($action, $action_class, array $vars = null) {
                
                // Sign
                if (empty($vars)) $vars = array();
                $time = time();
                $vars["__bTs"] = $time;
                $vars["__bTk"] = \Bonita\Forms::token($action, $time);
                $vars["__bTa"] = htmlentities($action);
                
                // Construct query
                $page = new $action_class();
                $page->init();
                foreach ($vars as $key => $value)
                    $page->setInput($key, $value);
                
                $page->postContent();
                
                exit(); // Handled, prevent further processing.
            }
            
            function registerPages() {
                
                // Register an account menu
                \Idno\Core\site()->template()->extendTemplate('account/menu/items','account/emailpost/menu');
                
                
                // Register the callback URL
                 \Idno\Core\site()->addPageHandler('account/emailposting','\IdnoPlugins\IdnoEmailPosting\Pages\Account');
                 
                 
                // Now, lets handle some events
                \Idno\Core\site()->addEventHook('email/post',function(\Idno\Core\Event $event) {

                    $subject = $event->data()['subject'];
                    $body = $event->data()['body'];
                    $attachments = $event->data()['attachments'];
                    $syndication = [];
                    
                    // Parse subject for syndication hashtags
                    $matches = [];
                    if (preg_match_all('/\|[a-zA-Z]+/', $subject, $matches)) {
                        $subject = explode('|', $subject);
                        $subject = trim($subject[0]);
                        
                        foreach ($matches[0] as $match)
                            $syndication[] = trim($match, '| ');
                        
                    }
		    
		    // Parse body for syndication hashtags
		    $matches = [];
                    if (preg_match_all('/\|[a-zA-Z]+/m', $body, $matches)) {
			$body = preg_replace('/\|[a-zA-Z]+/m', ' ', $body); // Remove tags from body
                        foreach ($matches[0] as $match)
                            $syndication[] = trim($match, '| ');
                    }
		    
		    $syndication = array_unique($syndication); // Remove duplicates
		    
		    if (count($syndication))
			foreach ($syndication as $service)
			    error_log("Syndicating to $service");
		    
		    // Remove any blank lines from end of body (which may be left over from removing tags)
		    $body = rtrim($body);

                    // If there are attachments, see if any of them are pictures
                    if (!empty($attachments)) {
                        
                        foreach ($attachments as $attachment) {
                            
                            $content_type = $attachment->getContentType();
                            error_log("Found attachment of $content_type...");
                            
                            // We know how to handle images...
                            if (strpos($content_type, 'image/')!== false)
                            {
                                error_log("I know how to handle an image...");
                                
                                // Write temp file
                                $tmpfname = tempnam("/tmp", "IdnoEmailPosting");

                                $handle = fopen($tmpfname, "w");
                                fwrite($handle, $attachment->getContent());
                                fclose($handle);
                                
                                // Fake a file upload
                                $_FILES = [
                                    'photo' => [
                                        'tmp_name' => $tmpfname,
                                        'name' => $attachment->getFilename(),
                                        'type' => $content_type
                                    ]
                                ];
                                
                                $this->callAction('/photo/edit', 'IdnoPlugins\Photo\Pages\Edit', ['body' => $body, 'title' => $subject, 'syndication' => $syndication]);
                            }
                            
                        }
                    }
                    
                    
                    // If short message, post as status
                    if (strlen("$subject $body") <= 140) {
                        $this->callAction('/status/edit', 'IdnoPlugins\Status\Pages\Edit', ['body' => "$subject $body", 'syndication' => $syndication]);
                    }
                    
                    // Longer form, post as post
                    $this->callAction('/text/edit', 'IdnoPlugins\Text\Pages\Edit', ['body' => $body, 'title' => $subject, 'syndication' => $syndication]);
                    
                });
                 
	    }

        }
    }

