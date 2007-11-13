<?php
    
    // Preferences
    $mysqlhost = 'localhost';
    $mysqluser = 'menhir_user';
    $mysqlpass = 'traviantest';
    $mysqldb = 'menhir_data';
    
    // Create database connection and select database
    $db = @mysql_connect($mysqlhost, $mysqluser, $mysqlpass) OR die('Can not connect to DB-Server!');
    $db_select = @mysql_select_db($mysqldb) OR die('Can not select DB!');
    
    // load the map.sql via system command using "wget" into the folder data/
    // IMPORTANT: PHP has to be allowed to write into that folder, if necessary set the needed rights!
    system('wget http://s5.travian.com/map.sql -O /home/menhir/public_html/upload/modules/travian/data/tmp.sql');

    // Check whether the file has been downloaded and is larger than zero bytes
    if (file_exists('/home/menhir/public_html/upload/modules/travian/data/tmp.sql') AND filesize('/home/menhir/public_html/upload/modules/travian/data/tmp.sql')) {
        
        // Empty table
        $query = 'TRUNCATE TABLE x_world';
        $result = @mysql_query($query) OR die('Can not clear table x_world!');
        
        // Exceute map.sql using the programme "mysql"
        // IMPORTANT: The charset "latin1" has to be used for T2 game worlds (if there should be any left with that version)
        system('mysql --host='.$mysqlhost.' --user='.$mysqluser.' --password='.$mysqlpass.' --default-character-set=utf8 '.$mysqldb.' < /home/menhir/public_html/upload/modules/travian/data/tmp.sql');
        
        echo 'Update finished!';
        
    } else {
        
        echo 'Failed downloading map.sql or file is empty!';

    }
    
    // In case the temporary file exists it will be deleted
    if (file_exists('data/tmp.sql')) {
        unlink('data/tmp.sql');
    }
    
    // Close database connection
    @mysql_close($db);

?> 
