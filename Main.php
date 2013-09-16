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
            
            function registerPages() {
                
                // Register an account menu
                \Idno\Core\site()->template()->extendTemplate('account/menu/items','account/emailpost/menu');
                
                
                // Register the callback URL
                 \Idno\Core\site()->addPageHandler('account/emailposting','\IdnoPlugins\IdnoEmailPosting\Pages\Account');
	    }

        }
    }

