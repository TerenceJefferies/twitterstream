<!DOCTYPE html>
<html>
    
    <head>
        
        <title>Example Twitter Stream Runner</title>
        
    </head>
    <body>
        
        <h1>Twitter Stream</h1>
        <p>This is an example page for the Twitter Stream class, created by Terence Jefferies (@TJRLZ) - The twitter stream class can be used to retrieves the most recent twitter messages for any twitter account.</p>
        
        <p>
            
            The latst tweet by @TJRLZ is: 
            <q>
                
                <?php
                
                    include 'class/twitterstream.php';
                    
                    $twitterstream = new twitterStream('TJRLZ');
                    echo $twitterstream -> fetchLatestTweet();
                
                ?>
                
            </q>
            
        </p>
        
    </body>
    
</html>