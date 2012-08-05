<?php

    /*
     * twitterStream, used to retrieve the latest tweets for any twitter account
     * 
     * @author: Terence Jefferies
     * 
     * @date: 05/08/2012
     * 
     * @license: Attribution 3.0 Unported
     * 
     * @params
     * - (string): screenName: The screen name for the user who's tweets
     * should be retrieved
     * 
     */
    class twitterStream{
        
        /*
         * (boolean) connectToDb, if set to true the class will attempt to 
         * connect to a database at the credentials provided, otherwise it
         * will use any existing connection
         */
        private $connectToDb = true;
        
        /*
         * (string) dbHost, the hostname of the mysql server to connect to
         */
        private $dbHost = 'localhost';
        
        /*
         * (string) dbUserName, the database username to use when connecting
         */
        private $dbUserName = 'root';
        
        /*
         * (string) dbPassword, the password for the above user
         */
        private $dbPassword = '';
        
        /*
         * (string) dbName, the name of the database to select
         */
        private $dbName = 'twitter_stream';
        
        /*
         * (string) screenName, the users screen name
         */
        private $screenName = null;
        
        /*
         * (string) latestTweet, the users last found tweet
         */
        private $latestTweet = null;
        
        /*
         * (integer) checkInterval, the time in minutes between checking twitter
         * for new tweets
         */
        private $checkInterval = 60;
        
        /*
         * (resource) connection, the mysql connection resource
         */
        private $connection = null;
        
        /*
         * __construct, performs startup proceedures of the class
         * 
         * @params
         * - (string) screenName: The users twitter screen name
         * 
         * @returns
         * - void
         * 
         */
        public function __construct($screenName) {
            
            if($screenName)
            {
             
                $this -> screenName = str_replace("@","",$screenName);
                if($this -> connectToDb) $this -> dbConnection = $this -> connectToDatabase();
                if(!$this -> verifyInstallation()) $this -> completeInstallation();
                $this -> latestTweet = $this -> retrieveLatestTweet();
                
            }
            else
            {
                
                echo 'You did not provide a screen name';
                
            }
            
        }
        
        /*
         * connectToDatabase, connects to the mysql database
         * 
         * @params
         * - void
         * 
         * @returns
         * - (resource): the mysql connection
         * 
         */
        private function connectToDatabase() {
            
            $connection = mysql_connect($this -> dbHost,$this -> dbUserName,$this -> dbPassword) or die("Twitter Stream: Unable to connect to desired database");
            mysql_select_db($this -> dbName,$connection) or die("Twitter Stream: Unable to select a database with the name provided");
            return $connection;
            
            
        }
        
        /*
         * mysqlQuery, sends a mysql query to the database
         * 
         * @params
         * - (string) query: The query to send
         * 
         * @retunrs
         * - (resource): The returned values from the query
         * 
         */
        private function mysqlQuery($query)
        {
            
            if($this -> connection)
            {
                
                return mysql_query($query,$this -> connection);
                
            }
            else
            {
                
                return mysql_query($query);
                
            }
            
        }
        
        /*
         * verifyInstallation, verifies the twitter stream mysql installation
         * 
         * @params
         * - void
         * 
         * @returns
         * - (boolean): true if the installation is correct, false otherwise
         * 
         */
        private function verifyInstallation() {
            
            $num = mysql_num_rows($this -> mysqlQuery("SHOW TABLES LIKE 'twitter_stream_tweets'"));
            return ($num) ? true : false;
            
        }
        
        /*
         * completeInstallation, used to populate the database with the 
         * appropriate table
         * 
         * @params
         * - void
         * 
         * @returns
         * - void
         *  
         */
        private function completeInstallation() {
            
            $this -> mysqlQuery("CREATE TABLE `twitter_stream_tweets` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `screen_name` VARCHAR( 64 ) NOT NULL ,
                `tweet` VARCHAR( 140 ) NOT NULL ,
                `stamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE = MYISAM ;
            ");
            
        }
        
        /*
         * retrieveLatestTweet, used to retreive the users latest tweet from the
         * database cache, if the database cache is no longer valid will request
         * the latest tweet from the twitter API
         * 
         * @params
         * - void
         * 
         * @returns
         * - (string): the users latest tweet
         * 
         */
        private function retrieveLatestTweet() {
            
            $row = mysql_fetch_array($this -> mysqlQuery("SELECT tweet FROM twitter_stream_tweets WHERE stamp > DATE_SUB(NOW(),INTERVAL " . $this -> checkInterval . " MINUTE)"));
            if($row)
            {
                
                return $row['tweet'];
                
            }
            else
            {
                
                $xml = simplexml_load_file("http://api.twitter.com/1/statuses/user_timeline.xml?screen_name=" . $this -> screenName);
                if($xml)
                {
                    
                    $tweet = mysql_real_escape_string($xml -> status -> text);
                    $this -> mysqlQuery("INSERT INTO twitter_stream_tweets (screen_name,tweet) VALUES ('" . $this -> screenName . "','" . $tweet . "')");
                    return $tweet;
                    
                }
                else
                {
                    
                    echo 'Twitter Stream: The screen name your provided is not valid, cannot read stream';
                    
                }
                
            }
            
        }
        
        /*
         * fetchLatestTweet, returns the latest tweet by the initialised screen
         * name
         * 
         * @params
         * - void
         * 
         * @returns 
         * - void
         * 
         */
        public function fetchLatestTweet() {
            
            return $this -> latestTweet;
            
        }
              
        
    }

?>