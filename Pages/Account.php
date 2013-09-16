<?php

    namespace IdnoPlugins\IdnoEmailPosting\Pages {

        /**
         * Default class to serve Facebook-related account settings
         */
        class Account extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->gatekeeper(); // Logged-in users only
                $t = \Idno\Core\site()->template();
                $body = $t->__(['login_url' => $login_url])->draw('account/emailpost');
                $t->__(['title' => 'Post via Email', 'body' => $body])->drawPage();
            }

            function postContent() {
                $this->gatekeeper(); // Logged-in users only
                
                $session = \Idno\Core\site()->session(); 
                $user = $session->currentUser(); 
                
                // Generate a new email address for this user.
                do {
                    $email = substr(md5(rand()), 0, 10) . '@' . \Idno\Core\site()->config()->host;
                } while (\IdnoPlugins\IdnoEmailPosting\Main::getUserBySecretEmail($email));
                
                
                $user->secret_email = $email;
                
                if ($user->save())
                    \Idno\Core\site()->session()->addMessage('New secret email address generated...');
                
                
                $this->forward('/account/emailposting/');
            }

        }

    }