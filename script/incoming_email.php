<?php

	require_once(dirname(dirname(dirname(__FILE__))) . '/Idno/start.php');

	$EmailParser = new MimeMailParser();

    	$stream = fopen("php://stdin", "r");
    	$EmailParser->setStream($stream);

	// Extract some basics
    	$to = trim($EmailParser->getHeader('to'));
    	$from = trim($EmailParser->extractEmail('from'));
    	$subject = trim($EmailParser->getHeader('subject'));

        // See if we've got a user with that email
        if ($user = \IdnoPlugins\IdnoEmailPosting\Main::getUserBySecretEmail($to)) {
            
            // Log user on
            $session = \Idno\Core\site()->session;
            $session->logUserOn($user);
            
            // Get body
            $text = $EmailParser->getMessageBody('text');
            $html = $EmailParser->getMessageBody('html');

            // Get Any attachments
            $attachments = $EmailParser->getAttachments();

            // Get message body
            $message_body = $text;

            // Remove signature
            list ($message_body) = preg_split('/^--\s*$/', $message_body);
            
            // Santise outlook and outlook web
            list ($message_body) = explode("From: ". \Idno\Core\site()->config()->title." [mailto:", $message_body);
            list ($message_body) = explode("________________________________", $message_body);
            
            
            // Eliminate a possible security hole where the special email address could be printed in the body (from jettmail)
            $message_body = trim(str_replace($EmailParser->extractEmail('to'), "", $message_body));

            // Trigger post handlers
            \Idno\Core\site()->triggerEvent('email/post', [
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $message_body,
                'attachments' => $attachments,
                'user' => $user
            ]);
            
        }

        // Done, log out
        $session = \Idno\Core\site()->session;
        $session->logUserOff();