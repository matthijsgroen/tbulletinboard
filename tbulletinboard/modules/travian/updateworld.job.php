<?php
	/**
	 *	TBB2, an highly configurable and dynamic bulletin board
	 *	Copyright (C) 2007  Matthijs Groen
	 *
	 *	This program is free software: you can redistribute it and/or modify
	 *	it under the terms of the GNU General Public License as published by
	 *	the Free Software Foundation, either version 3 of the License, or
	 *	(at your option) any later version.
	 *	
	 *	This program is distributed in the hope that it will be useful,
	 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *	GNU General Public License for more details.
	 *	
	 *	You should have received a copy of the GNU General Public License
	 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *	
	 */

    
    // Preferences
    $mysqlhost = 'localhost';
    $mysqluser = 'menhir_user';
    $mysqlpass = 'traviantest';
    $mysqldb = 'menhir_data';
    $path = '/home/menhir/public_html/upload/modules/travian/data';
    /* -- *
    $mysqlhost = 'localhost';
    $mysqluser = 'root';
    $mysqlpass = 'msdb3181';
    $mysqldb = 'tbb2';
    $path = '/var/www/tbb2/upload/modules/travian/';
    /* -- */
  
    
    // Create database connection and select database
    $db = @mysql_connect($mysqlhost, $mysqluser, $mysqlpass) OR die('Can not connect to DB-Server!');
    $db_select = @mysql_select_db($mysqldb) OR die('Can not select DB!');
    
    // load the map.sql via system command using "wget" into the folder data/
    // IMPORTANT: PHP has to be allowed to write into that folder, if necessary set the needed rights!
    system('wget http://s5.travian.com/map.sql -O '.$path.'tmp.sql');

    // Check whether the file has been downloaded and is larger than zero bytes
    if (file_exists(''.$path.'tmp.sql') AND filesize(''.$path.'tmp.sql')) {
        
        // Empty table
        $query = 'TRUNCATE TABLE x_world';
        $result = @mysql_query($query) OR die('Can not clear table x_world!');
        
        // Exceute map.sql using the programme "mysql"
        // IMPORTANT: The charset "latin1" has to be used for T2 game worlds (if there should be any left with that version)
        system('mysql --host='.$mysqlhost.' --user='.$mysqluser.' --password='.$mysqlpass.' --default-character-set=utf8 '.$mysqldb.' < '.$path.'tmp.sql');
        
        echo 'Update finished!';
        
    } else {
        
        echo 'Failed downloading map.sql or file is empty!';

    }
    
    // In case the temporary file exists it will be deleted
    if (file_exists(''.$path.'tmp.sql')) {
        unlink(''.$path.'tmp.sql');
    }
    
    // Close database connection
    @mysql_close($db);

?> 
