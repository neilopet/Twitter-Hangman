<?php

/* tweetman */
require_once 'F:\\xampp\\htdocs\\tweetman\\config.php';

// The tweetman library only needs once instance to be carried through the loop.
$tweetman = new Tweetman( $db, $words );

cout("Bot init\r\nEntering main() loop:\r\n\r\n");

$tmp_last_tweet_id = $last_tweet_id = getLastTweet();

$processed_tweet_ids = array();

while ( TRUE ) /* main */
{
    
    // Flush the processed tweets array if more than 3000 entries
    if (isset($processed_tweet_ids[2999]))
    {
        unset($processed_tweet_ids);
        $processed_tweet_ids = array();
    }
    
    // Find all tweets since the last tweet received.
    $mentions = $twitter->getMentions(800, $last_tweet_id);
    #var_dump($mentions); die();
    if (count($mentions) < 1) goto endMain;

    // Update the last tweet id for the first item in the mentions array.
    $tmp_last_tweet_id = current($mentions)->id_str;
    if ($tmp_last_tweet_id <= $last_tweet_id) goto endMain;
    $last_tweet_id = $tmp_last_tweet_id;
    file_put_contents(LAST_TWEET_ID, $last_tweet_id);
    
    
    // Iterate through the mentions array for all active users
    foreach ($mentions as $tweet)
    {
        //die($tweet->id_str . ':' . $last_tweet_id);
        if (in_array($tweet->id_str, $processed_tweet_ids)) continue;
        $processed_tweet_ids[] = $tweet->id_str;
        // Remove the @reply from the message text and convert to lowercase.
        $message = trim(str_replace('@realhangman', '', strtolower($tweet->text)));
        
        // Set the user var.
        $user = $tweet->user->screen_name;
        
        cout("Recv {$user}: {$message}\r\n");
        
        // Make sure we don't pick up our bot.
        if ($user == 'realhangman') continue;
        
        // Find an existing or create a new game.
        $topic = $tweetman->find( $user );
        
        // Have they guessed the word?
        if ($tweetman->compareWords( $message ))
        {
            $num = mt_rand(999, 9999);
            $twitter->postStatus("@{$user} You've won!  \r\nTo play again, tweet \"play {$num}\" to @realhangman.", $tweet->id_str);
            $tweetman->deleteGame();
            continue;
        }
        
        // Options:
        switch ($message)
        {
            case (substr($message, 0, 4) == 'play'):
                // User wishes to play a game.
                // Create a new game.
                $word_template = str_repeat('_ ', strlen($topic['word']));
                $twitter->postStatus("@{$user} The category is {$topic['category']}:\r\n{$word_template}", $tweet->id_str );
                
                // debug:
                cout("{$user}: New game - {$topic['category']} => {$topic['word']}\r\n");
                break;
            
            case 'quit':
                $tweetman->deleteGame();
                $twitter->postStatus("@{$user} Good bye.", $tweet->id_str);
                break;
                
            default:
                // They're either tweeting to me for shits and giggles or playing.
                // In either scenario, just play the game.
                
                // Clean their post and submit their guess.
                $tweetman->guess( substr($message, 0, 1) );
                
                // Check if they found the word or failed.
                $game = $tweetman->isGameOver();
                if (!$game['status'])
                {
                    // debug:
                    cout("{$user}: {$game['remaining']} left\r\n");
                    
                    //$lettersRemaining = insertSpaces($tweetman->getRemainingLetters()); 
                    $strReplyText     = "@{$user} {$game['remaining']} attempts left.  \r\n";//  Remaining characters: {$lettersRemaining}";
                    $strReplyText    .= ($game['message'] != 'You lose.') ? insertSpaces($game['message']) : $game['message'];
                    $twitter->postStatus($strReplyText, $tweet->id_str);
                }
                else
                {
                    //debug: 
                    cout("{$user} won!\r\n\t{$game['message']}\r\n--------------------\r\n");
                    
                    $twitter->postStatus("@{$user} {$game['message']}", $tweet->id_str);
                }
                break;
        }
    }
    
    endMain:
        // debug:
        cout("sleeping...\r\n\r\n");
        
        // Twitter REST API has maximum of 150 requests/IP per hour.
        // This must run at 25 second intervals or longer.
        sleep( 25 );
    // Don't run this forever!
}

fclose($fp);