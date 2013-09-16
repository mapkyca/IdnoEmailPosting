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
                    
                    // If there are attachments, see if any of them are pictures
                    if (!empty($attachments)) {
                        
                        // TODO
                        
                    }
                    
                    
                    // If short message, post as status
                    if (strlen("$subject $body") <= 140) {
                        $this->callAction('/status/edit', 'IdnoPlugins\Status\Pages\Edit', ['body' => "$subject $body"]);
                    }
                    
                    // Longer form, post as post
                    $this->callAction('/text/edit', 'IdnoPlugins\Text\Pages\Edit', ['body' => $body, 'title' => $subject]);
                    
                });
                 
	    }

        }
    }

