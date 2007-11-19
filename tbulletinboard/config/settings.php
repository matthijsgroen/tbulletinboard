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

	$GLOBALS['ivTableSpace'] = 0;
	$GLOBALS['iv_cal_daynames'] = array("ma", "di", "wo", "do", "vr", "za", "zo");
	$boardVersion = "2.0.11 &alpha;lpha version";
	
	/*
	$uploadPath = '/home/tbb/public_html/upload/';
	$uploadOnlinePath = 'upload/';
	
	// database settings
	$dbServer = "localhost";
	$dbDatabase = "tbb_data";
	$dbUser = "tbb_user";
	$dbPassword = "tbbdevelopment";
	*/
	
	$developmentMode = true;

	$uploadPath = '/var/www/tbb2/upload/';
	$uploadOnlinePath = 'upload/';
	
	// database settings
	$dbServer = "localhost";
	$dbDatabase = "tbb_blank";
	$dbUser = "root";
	$dbPassword = "msdb3181";

		
?>
