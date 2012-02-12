<?php

/**
 * Tweetman
 * A twitter hangman game that is played via tweets and retweets.
 * 
 * @package Tweetman
 * @author Neil Opet
 * @copyright 2012
 * @version 0.1.0
 * @access public
 */

interface ITweetman
{
    public function find( $user );            // accepts twitter user id and returns the current word
    public function deleteGame();             // remove the game the active user is locked in
    public function compareWords( $str );     // true if the word matches user input
    public function guess( $letter );         // true if the letter was matched; otherwise false
    public function isGameOver( /* void */ ); // returns array[ status, message, remaining attempts ]
}

class Tweetman
{
    private
        $db,
        $dictionary,
        $user,
        $current_word,
        $set = 'abcdefghijklmnopqrstuvwxyz';
    
    /**
     * Tweetman::__construct()
     * Initiates the Tweetman object.  
     * Dictionary must be an associative array with the category as the key and a child array containing the words of the category.
     * @param Database $db
     * @param array $dictionary
     * @return void
     */
    function __construct( Database $db, array $dictionary )
    {
        $this->db = $db;
        $this->dictionary = $dictionary;
    }
    
    /**
     * Tweetman::newGame()
     * Returns a newly generated category, word pair for a fresh game instance
     * @param string $user
     * @return array
     */
    private function newGame( $user )
    {
        // select category
        $categories = array_keys($this->dictionary);
        $category = $categories[array_rand($categories)];
        // select random word
        $word = $this->dictionary[$category][array_rand($this->dictionary[$category])];
        // insert into database
        @$this->db->query('INSERT INTO `games` (`category`, `word`, `user`) VALUES (?, ?, ?)', array(
            'category' => $category,
            'word'     => $word,
            'user'     => $user
        ));
        $this->user = (object)array(
            'result' => (object)array(
                'category'  => $category,
                'word'      => $word,
                'user'      => $user,
                'guesses'   => $this->set,
                'failcount' => 0
            ),
            'num_rows' => 1,
            'affected_rows' => 1
        );
        return array(
            'category' => $category,
            'word'     => $word
        );
    }
    
    /**
     * Tweetman::getByUser()
     * Returns the game data associated with the specified user
     * @param string $user
     * @return object
     */
    private function getByUser( $user )
    {
        // return game data
        return $this->db->query('SELECT * FROM `games` WHERE `user` = ?', array(
            'user' => $user
        ));
    }
    
    /**
     * Tweetman::popLetter()
     * Removes letter from available guesses
     * @param string $letter
     * @return Database result
     */
    private function popLetter( $letter )
    {
        if (!isset($this->user->result) || $this->user->num_rows < 1) return FALSE;
        $this->user->result->guesses = str_replace($letter, '', $this->user->result->guesses);
        return $this->db->query('UPDATE `games` SET `guesses`= ? WHERE `user` = ?', array(
            'guesses' => $this->user->result->guesses,
            'user'    => $this->user->result->user
        ));
    }
    
    /**
     * Tweetman::incFailCount()
     * Increments the fail counter (`failcount` in database)
     * @return Database result
     */
    private function incFailCount()
    {
        if (!isset($this->user->result) || $this->user->num_rows < 1) return FALSE;
        $this->user->result->failcount++;
        return $this->db->query('UPDATE `games` SET `failcount` = (`failcount` + 1) WHERE `user` = ?', array(
            'user'    => $this->user->result->user
        ));
    }
    
    /**
     * Tweetman::isFound()
     * Returns TRUE if the word has been found/completed
     * @return bool
     */
    private function isFound()
    {
        if (!isset($this->user->result) || $this->user->num_rows < 1) return FALSE;
        $this->current_word = '';
        $diff = array_diff(str_split($this->set), str_split($this->user->result->guesses));
        for ($i = 0, $l = strlen($this->user->result->word); $i < $l; ++$i)
        {
            $letter = $this->user->result->word{$i};
            $this->current_word .= (in_array($letter, $diff)) ? $letter : '_';            
        }
        return ($this->current_word == $this->user->result->word);
    }
    
    /**
     * Tweetman::isHung()
     * Returns TRUE if the player has lost the game
     * @return bool
     */
    private function isHung()
    {
        //if (!isset($this->user->result) || $this->user->num_rows < 1) return FALSE;
        return ($this->user->result->failcount >= 6); 
    }
    
    /**
     * Tweetman::deleteGame()
     * Removes the game associated with the active user
     * @return Database result
     */
    public function deleteGame()
    {
        return ($this->db->query('DELETE FROM `games` WHERE `user` = ?', array(
            'user' => $this->user->result->user
        ))->affected_rows > 0);
    }
    
    /**
     * Tweetman::find()
     * Returns the category, word pair the active user is playing
     * @param string $user
     * @return array
     */
    public function find( $user )
    {
        $ret = FALSE;
        // search database for game by user
        $this->user = $this->getByUser( $user );
        if (isset($this->user->result) && $this->user->num_rows > 0)
        {
            $ret = array(
                'category' => $this->user->result->category, 
                'word'     => $this->user->result->word
            );
        }
        else // create new game for user
        {
            $ret = $this->newGame( $user );
        }
        return $ret;
    }
    
    /**
     * Tweetman::compareWords()
     * Returns TRUE if the string matches the generated word. 
     * @param string $str
     * @return bool
     */
    public function compareWords( $str )
    {
        return ($str == $this->user->result->word);
    }
    
    /**
     * Tweetman::guess()
     * Returns FALSE if the letter guessed was not found in the word
     * @param char $letter
     * @return bool
     */
    public function guess( $letter )
    {
        $ret = FALSE;
        // return false if they keep guessing the same letter   
        if (FALSE === strpos($this->user->result->guesses, $letter)) return FALSE;
                    
        if (FALSE !== strpos($this->user->result->word, $letter))
        {
            // match
            $ret = ($this->popLetter( $letter )->affected_rows > 0);
        }
        else
        {
            // not found - increment the fail counter
            $this->incFailCount();
        }
        return $ret;
    }
    
    /**
     * Tweetman::isGameOver()
     * Returns an array with keys status, message, and remaining. 
     * @return array
     */
    public function isGameOver()
    {
        if ($this->isHung())
        {
            $this->deleteGame();
            return array(
                'status'    => FALSE,
                'message'   => 'You lose.',
                'remaining' => 0
            );
        }
        elseif ($this->isFound())
        {
            $this->deleteGame();
            return array(
                'status'    => TRUE,
                'message'   => "You've won! Your word: {$this->current_word}",
                'remaining' => 0 
            );
        }
        else
        {
            return array(
                'status'    => FALSE,
                'message'   => $this->current_word,
                'remaining' => (6 - $this->user->result->failcount)
            );
        }
        return array(
            'status'  => FALSE,
            'message' => 'I don\'t know how it got here.'
        );
    }
    
    public function getRemainingLetters()
    {
        return $this->user->result->guesses;
    }
}