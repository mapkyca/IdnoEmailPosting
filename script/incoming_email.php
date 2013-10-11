<?php

	require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/Idno/start.php');
        
        require_once(dirname(dirname(__FILE__)).'/MimeMailParser.php');
        require_once(dirname(dirname(__FILE__)).'/MimeMailParser_attachment.php');

        error_log("************** Idno: Post via Email **************");
        
	$EmailParser = new MimeMailParser();

    	$stream = fopen("php://stdin", "r");
    	$EmailParser->setStream($stream);

	// Extract some basics
    	$to = trim($EmailParser->extractEmail('to'));
    	$from = trim($EmailParser->extractEmail('from'));
    	$subject = trim($EmailParser->getHeader('subject'));

        error_log("Extracted: to=$to, from=$from, subject='$subject'");
                
        // See if we've got a user with that email
        if ($user = \IdnoPlugins\IdnoEmailPosting\Main::getUserBySecretEmail($to)) {
            
            error_log("$to is a secret email!");
            
            // Log user on
            $session = \Idno\Core\site()->session;
            $session->logUserOn($user);
            
            // Get body
            $text = $EmailParser->getMessageBody('text');
            $html = $EmailParser->getMessageBody('html');

            error_log("Extracted text: text=" . strlen($text) . "bytes, html=" . strlen($html). "bytes");
            
            // Get Any attachments
            $attachments = $EmailParser->getAttachments();
            if (!empty($attachments))
                error_log("Counting " . count($attachments) . " attachments...");
            
            // Get message body
            $message_body = $text;

            // Remove signature
            if (strrpos($message_body, "\n--")!==false)
                $message_body = substr($message_body, 0, strrpos($message_body, "\n--")); // list ($message_body) = preg_split('/^--\s*$/', $message_body);
     
            // Santise outlook and outlook web
            list ($message_body) = explode("From: ". \Idno\Core\site()->config()->title." [mailto:", $message_body);
            list ($message_body) = explode("________________________________", $message_body);
            
            
            // Eliminate a possible security hole where the special email address could be printed in the body (from jettmail)
            $message_body = trim(str_replace($EmailParser->extractEmail('to'), "", $message_body));
            
            error_log("Message body is: \n$message_body");

            // Trigger post handlers
            error_log("Triggering post...");
            \Idno\Core\site()->triggerEvent('email/post', [
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $message_body,
                'attachments' => $attachments,
                'user' => $user
            ]);
            
            
            // Done, log out
            $session = \Idno\Core\site()->session;
            $session->logUserOff();
            
        }
        else
            error_log("No user attached to $to");

        
        error_log("**************************************************");

