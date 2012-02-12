<?php

class Twitter
{
    private
        $mentions,
        $handle;
        
    function __construct(TwitterOAuth $handle)
    {
        $this->handle = $handle;
    }
    
    public function getMentions( $count = 800, $since = NULL )
    {
        return $this->mentions = $this->handle->get('statuses/mentions.json?include_rts=true&count=' . $count . (isset($since) ? ('&since_id=' . $since) : ''));
    }
    
    public function getMyStatuses()
    {
        return $this->handle->get('statuses/home_timeline');
    }
    
    public function postStatus( $message, $tweet_id = NULL )
    {
        $post = array
        (
            'status' => $message
        );
        
        if (isset($tweet_id))
            $post['in_reply_to_status_id'] = $tweet_id;
            
        return $this->handle->post('statuses/update', $post);
    }
}