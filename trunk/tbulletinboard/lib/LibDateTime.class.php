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
	 * A class for using and calculating with time.
	 * This class works with the ISO 8601 standard, eg. Monday is the first day of the week.
	 *
	 */
	require_once("library.php");

	class LibDateTime {
		// Storage for date info
		var $privateVars;

		// [int hour [, int minute [, int second [, int month [, int day [, int year [, int is_dst]]]]]]]
		function LibDateTime($hour = -1, $minute = -1, $second = -1, $month = -1, $day = -1, $year = -1, $is_dst = -1) {
			$this->privateVars = array();

			$this->p_set("year", (($year != -1) ? $year : date("Y", time())));
			$this->p_set("month", (($month != -1) ? $month : date("m", time())));
			$this->p_set("dayOfMonth", (($day != -1) ? $day : date("d", time())));

			$this->p_set("hour", (($hour != -1) ? $hour : date("H", time())));
			$this->p_set("minute", (($minute != -1) ? $minute : date("i", time())));
			$this->p_set("second", (($second != -1) ? $second : date("s", time())));

			global $GLOBALS; // If no daynames are known, default to the english daynames
			if (!isSet($GLOBALS['iv_cal_daynames'])) {
				$this->setDayNames(
					"Monday",
					"Tuesday",
					"Wednesday",
					"Thursday",
					"Friday",
					"Saturday",
					"Sunday"
				);
			}
			// Calculate all LibDateTime info
			$this->p_calculate("year");
		}

		/**
		 * Set the daynames for all days in a week (starting with monday)
		 * These values will be used by all LibDateTime objects.
		 *@param string $dayname (7 times: the name of a day in the week)
		 */
		function setDayNames() {
			global $GLOBALS;
			if (func_num_args() != 7) return;
			$GLOBALS['iv_cal_daynames'] = func_get_args();
		}

		/**
		 * Returns a LibDateTime timestamp. This is no Linux timestamp, and can not be
		 * used for calculations! this is merely for copying LibDateTime data through a string.
		 *@return string the timestamp value of this datetime
		 */
		function getTimestamp() {
			$result = paddString($this->get(LibDateTime::year()),'0',4,false) . '-' . 
				paddString($this->get(LibDateTime::dayOfYear()),'0',3,false) . '-';
			$result .= paddString($this->get(LibDateTime::hour()),'0',2,false) . '-' . 
				paddString($this->get(LibDateTime::minute()),'0',2,false) . '-' . paddString($this->get(LibDateTime::second()),'0',2,false);
			return $result;
		}

		/**
		 * Sets a LibDateTime timestamp. This is no Linux timestamp, and can not be
		 * used for calculations! this is merely for copying LibDateTime data through a string.
		 *@param string $timestamp the timestamp value of this datetime
		 */
		function setTimestamp($timestamp) {
			list($y, $doy, $h, $m, $s) = explode("-", $timestamp);
			$this->set(LibDateTime::year(), $y);
			$this->set(LibDateTime::dayOfYear(), $doy);
			$this->set(LibDateTime::hour(), $h);
			$this->set(LibDateTime::minute(), $m);
			$this->set(LibDateTime::second(), $s);
		}

		/**
		 * Sets the value for a time element.
		 * example: set(LibDateTime::hour(), 14);
		 *@param string $element the name of the element to set
		 *@param int $value the value for the specified element
		 */
		function set($element, $value) {
			$this->p_set($element, $value);
			$this->p_calculate($element); // calculate other fields
			return true;
		}

		/**
		 * Private function to set a LibDateTime value, without calculation of the other fields
		 *@access private
		 *@param string $element the name of the element to set
		 *@param int $value the value for the specified element
		 */
		function p_set($element, $value) {
			$maxValue = $this->getMax($element);
			if (($maxValue != -1) && ($value > $maxValue)) $value = $maxValue;
			$minValue = $this->getMin($element);
			if (($minValue != -1) && ($value < $minValue)) $value = $minValue;
			$this->privateVars[$element] = (int)$value;
			return true;
		}

		/**
		 * Returns the current value of the given LibDateTime element
		 *@param string $element the name of the element to retrieve its current value from
		 *@return int the value of the element
		 */
		function get($element) {
			if (!isset($this->privateVars[$element])) return 0;			
			return $this->privateVars[$element];
		}

		/**
		 * Returns the maximum value of the given LibDateTime element, in its current context.
		 * eg. If the year is a leapyear, the month is february, the max value of DayOfMonth would be 29
		 *@param string $element the name of the element to retrieve its maximum value from
		 *@return int the maximum value of the element
		 */
		function getMax($element) {
			switch($element) {
				case "dayOfMonth":
					$month = $this->get("month");
					switch ($month) {
						case 2: // February
							$leapYear = $this->isLeapYear();
						return ($leapYear) ? 29 : 28;
						// Jan, Mar, May, Jul, Aug, Oct, Dec have 31 days
						case 1: case 3: case 5: case 7: case 8: case 10: case 12: return 31;
						// Apr, Jun, Sep, Nov have 30 days
						case 4: case 6: case 9: case 11: return 30;
					}
				break;
				case "second": return 59;
				case "hour": return 23;
				case "minute": return 59;
				case "dayOfYear":
					$leapYear = $this->isLeapYear();
					return ($leapYear) ? 365 : 366;
				case "month": return 12;
				case "dayOfWeek": return 6;
				case "week": return -1; // TODO -- Fix this!
			}
			return -1;
		}

		/**
		 * Returns the minimum value of the given LibDateTime element
		 *@param string $element the name of the element to retrieve its minimum value from
		 *@return int the minimum value of the element
		 */
		function getMin($element) {
			switch($element) {
				case "dayOfMonth": return 1;
				case "second": return 0;
				case "hour": return 0;
				case "minute": return 0;
				case "dayOfYear": return 1;
				case "month": return 1;
				case "year": return 0;
				case "dayOfWeek": return 0;
				case "week": return 1;
			}
			return -1;
		}


		/**
		 * Gets the difference between this LibDateTime object and another LibDateTime object in the given unit (second, minute ,day)
		 *@param LibDateTime the other LibDateTime (the B in A - B = result)
		 *@param string the element name of the unit of the asked result : "second", "minute" or "day"
		 *@return int the difference  is < 0 the B > A, if = 0 B = A and if > 0 A > B
		 **/
		function getDifference(&$otherLibDateTime, $element) {
			$result = 0;
			switch($element) {
				case "second" :
					$dayDiff = $this->getDifference($otherLibDateTime, "day");
					$result += $dayDiff*24*60*60;
					$result += $otherLibDateTime->getSecondsOfDay() - $this->getSecondsOfDay();
				break;
				case "minute" :
					$result = floor($this->getDifference($otherLibDateTime, "second")/60);
				break;
				case "day" :
					$JDA = gregorianToJD($this->get("month"), $this->get("dayOfMonth"), $this->get("year"));
					$JDB = gregorianToJD($otherLibDateTime->get("month"), $otherLibDateTime->get("dayOfMonth"), $otherLibDateTime->get("year"));
					$result = $JDB - $JDA;
				break;
			}
			return $result;
		}

		/**
		 * Copy time elements from an other dateTime to this dateTime.
		 *@param LibDateTime other date time to get the element values from
		 *@param string $elements,... names of elements to copy
		 */
		function copyValuesFrom(&$otherLibDateTime) {
			if (func_num_args() == 1) return;
			for ($i = 1; $i < func_num_args(); $i++) {
				$element = func_get_arg($i);
				$this->set($element, $otherLibDateTime->get($element));
			}
		}

		/**
		* Clones this LibDateTime Object
		*@return LibDateTime, a clone of this LibDateTime object
		**/
		function cloneLibDateTime() {
			$newLibDateTime = new LibDateTime();

			$newLibDateTime->p_set(ivYear, $this->get(ivYear));
			$newLibDateTime->p_set(ivMonth, $this->get(ivMonth));
			$newLibDateTime->p_set(ivDay, $this->get(ivDay));
			$newLibDateTime->p_set(ivHour, $this->get(ivHour));
			$newLibDateTime->p_set(ivMinute, $this->get(ivMinute));
			$newLibDateTime->p_set(ivSecond, $this->get(ivSecond));
			$newLibDateTime->p_calculate(ivYear);

			return $newLibDateTime;
		}

		/**
		 * Gets the amount of seconds since 0:00:00
		 *@return the amount of seconds since 0:00:00
		 **/
		function getSecondsOfDay() {
			$result = 0;
			$result += $this->get("hour")*60*60;
			$result += $this->get("minute")*60;
			$result += $this->get("second");
			return $result;
		}

		/**
		 * Gets the amount of minutes since 0:00
		 *@return the amount of seconds since 0:00
		 **/
		function getMinutesOfDay() {
			$result = 0;
			$result += $this->get("hour")*60;
			$result += $this->get("minute");
			return $result;
		}


		/**
		 * Returns wether it is a leap year or not
		 *@return bool true if it is a leap year, false otherwise
		 */
		function isLeapYear() {
			$leapYear = false;
			$year = $this->get("year");
			if (($year % 4) == 0) $leapYear = true;
			if (($year % 100) == 0) $leapYear = false;
			if (($year % 400) == 0) $leapYear = true;
			return $leapYear;
		}

		/**
		 * Adds an amount of time to this LibDateTime.
		 *@param string $element the time unit to add
		 *@param int $value the amount of the time unit to add
		 */
		function add($element, $value) {
			if ($value < 0) {
				$this->sub($element, $value * -1);
				return;
			}
			if ($value == 0) return;
			$this->p_add($element, $value);
			$this->p_calculate($element);
		}

		/**
		 * Private function to add time to this LibDateTime, without calculation of the other fields
		 *@access private
		 *@param string $element the time unit to add
		 *@param int $value the amount of the time unit to add
		 */
		function p_add($element, $value) {
			$maxValue = $this->getMax($element);
			$totalValue = $this->get($element) + $value;
			switch($element) {
				case "second":
					if ($totalValue > $maxValue) {
						$minutes = floor($totalValue / ($maxValue + 1));
						$this->p_add("minute", $minutes);
					}
					$seconds = $totalValue % ($maxValue + 1);
					$this->p_set($element, $seconds);
					break;
				case "minute":
					if ($totalValue > $maxValue) {
						$hours = floor($totalValue / ($maxValue + 1));
						$this->p_add("hour", $hours);
					}
					$minutes = $totalValue % ($maxValue + 1);
					$this->p_set($element, $minutes);
					break;
				case "hour":
					if ($totalValue > $maxValue) {
						$days = floor($totalValue / ($maxValue + 1));
						$this->p_add("dayOfMonth", $days);
					}
					$hours = $totalValue % ($maxValue + 1);
					$this->p_set($element, $hours);
					break;
				case "dayOfWeek": // It's all adding days
				case "dayOfYear":
				case "dayOfMonth":
					$jDate = gregorianToJD($this->get("month"), $this->get("dayOfMonth"), $this->get("year"));
					$jDate += $value;
					list($gregMonth, $gregDay, $gregYear) = explode("/", JDtoGregorian($jDate));
					$this->p_set("year", $gregYear);
					$this->p_set("month", $gregMonth);
					$this->p_set("dayOfMonth", $gregDay);
					break;
				case "week": $this->p_add("dayOfMonth", $value * 7);
				case "month":
					if ($totalValue > $maxValue) {
						$years = floor($totalValue / $maxValue);
						$this->p_add("year", $years);
					}
					$months = (($totalValue -1) % $maxValue) + 1;
					$this->p_set($element, $months);
					$this->p_set('dayOfMonth', $this->get('dayOfMonth')); // Fix for going to 31 feb
					break;
				case "year":
					$this->p_set($element, $totalValue);
					break;
			}
		}

		/**
		 * Subtracts an amount of time from this LibDateTime.
		 *@param string $element the time unit to subtract
		 *@param int $value the amount of the time unit to subtract
		 */
		function sub($element, $value) {
			if ($value < 0) {
				$this->add($element, $value * -1);
				return;
			}
			if ($value == 0) return;
			$this->p_sub($element, $value);
			$this->p_calculate($element);
		}

		/**
		 * Private function to subtract time to this LibDateTime, without calculation of the other fields
		 *@access private
		 *@param string $element the time unit to subtract
		 *@param int $value the amount of the time unit to subtract
		 */
		function p_sub($element, $value) {
			$minValue = $this->getMin($element);
			$maxValue = $this->getMax($element);
			$totalValue = $this->get($element) - $value;
			switch($element) {
				case "second":
					if ($totalValue < $minValue) {
						$minutes = floor($totalValue / ($maxValue + 1)) * -1;
						$this->p_sub("minute", $minutes);
						$totalValue = ($totalValue % ($maxValue + 1)) + ($maxValue + 1);
					}
					$this->p_set($element, $totalValue);
					break;
				case "minute":
					if ($totalValue < $minValue) {
						$hours = floor($totalValue / ($maxValue + 1)) * -1;
						$this->p_sub("hour", $hours);
						$totalValue = ($totalValue % ($maxValue + 1)) + ($maxValue + 1);
					}
					$this->p_set($element, $totalValue);
					break;
				case "hour":
					if ($totalValue < $minValue) {
						$days = floor($totalValue / ($maxValue + 1)) * -1;
						$this->p_sub("dayOfMonth", $days);
						$totalValue = ($totalValue % ($maxValue + 1)) + ($maxValue + 1);
					}
					$this->p_set($element, $totalValue);
					break;
				case "dayOfWeek":
				case "dayOfYear":
				case "dayOfMonth":
					$jDate = gregorianToJD($this->get("month"), $this->get("dayOfMonth"), $this->get("year"));
					$jDate -= $value;
					list($gregMonth, $gregDay, $gregYear) = explode("/", JDtoGregorian($jDate));
					$this->p_set("year", $gregYear);
					$this->p_set("month", $gregMonth);
					$this->p_set("dayOfMonth", $gregDay);
					break;
				case "week": $this->p_sub("dayOfMonth", $value * 7);
				case "month":
					if ($totalValue < $minValue) {
						$years = floor(($totalValue - $minValue) / $maxValue) * -1;
						$this->p_sub("year", $years);
						$totalValue = (($totalValue) % $maxValue) + ($maxValue);
					}
					$this->p_set($element, $totalValue);
					$this->p_set('dayOfMonth', $this->get('dayOfMonth'));
					break;
				case "year":
					$this->p_set($element, $totalValue);
					break;
			}
		}

		/**
		 * Returns if this LibDateTime is before the given LibDateTime
		 *@param LibDateTime the LibDateTime to compare to
		 *@return bool true if this LibDateTime lies before the other LibDateTime. False otherwise
		 */
		function before($otherLibDateTime) {
			return $otherLibDateTime->after($this);
		}

		/**
		 * Returns if this LibDateTime is after the given LibDateTime
		 *@param LibDateTime the LibDateTime to compare to
		 *@return bool true if this LibDateTime lies after the other LibDateTime. False otherwise
		 */
		function after($otherLibDateTime) {
			if ($otherLibDateTime->get("year") > $this->get("year")) return false;
			if ($otherLibDateTime->get("year") < $this->get("year")) return true;
			if ($otherLibDateTime->get("month") > $this->get("month")) return false;
			if ($otherLibDateTime->get("month") < $this->get("month")) return true;
			if ($otherLibDateTime->get("dayOfMonth") > $this->get("dayOfMonth")) return false;
			if ($otherLibDateTime->get("dayOfMonth") < $this->get("dayOfMonth")) return true;
			if ($otherLibDateTime->get("hour") > $this->get("hour")) return false;
			if ($otherLibDateTime->get("hour") < $this->get("hour")) return true;
			if ($otherLibDateTime->get("minute") > $this->get("minute")) return false;
			if ($otherLibDateTime->get("minute") < $this->get("minute")) return true;
			if ($otherLibDateTime->get("second") > $this->get("second")) return false;
			if ($otherLibDateTime->get("second") < $this->get("second")) return true;
			return false; // equal, that is not after, nor before
		}

		/**
		 * Returns if this LibDateTime is equal to the given LibDateTime
		 *@param LibDateTime the LibDateTime to compare to
		 *@param string $elements,... names of elements to compare
		 *@return bool true if this LibDateTime is equal. False otherwise
		 */
		function isEqual(&$otherLibDateTime) {
			if (func_num_args() == 1) {
				if ($otherLibDateTime->get("year") != $this->get("year")) return false;
				if ($otherLibDateTime->get("month") != $this->get("month")) return false;
				if ($otherLibDateTime->get("dayOfMonth") != $this->get("dayOfMonth")) return false;
				if ($otherLibDateTime->get("hour") != $this->get("hour")) return false;
				if ($otherLibDateTime->get("minute") != $this->get("minute")) return false;
				if ($otherLibDateTime->get("second") != $this->get("second")) return false;
			} else {
				for ($i = 1; $i < func_num_args(); $i++) {
					$element = func_get_arg($i);
					if ($otherLibDateTime->get($element) != $this->get($element)) return false;
				}
			}
			return true;
		}

		/**
		 * Returns a string value of the LibDateTime. Letters like in the PHP function Date can be used.
		 *@param string $format the format to print the LibDateTime in.
		 *@return string the string format of this LibDateTime
		 */
		function toString($format = "d-m-Y H:i:s") {
			$result = $format;
			$checkLetters = array("d", "g", "G", "h", "H", "i", "j", "L", "m", "n", "s", "t", "w", "W", "y", "Y", "z", "a", "A", "l");

			global $GLOBALS;
			for ($i = 0; $i < count($checkLetters); $i++) {
				$letter = $checkLetters[$i];
				$value = "";
				$length = -1;
				switch($letter) {
					// Lowercase Ante meridiem and Post meridiem
					case 'a': $value = ($this->get(LibDateTime::hour()) > 11) ? "pm" : "am"; break;
					// Uppercase Ante meridiem and Post meridiem
					case 'A': $value = ($this->get(LibDateTime::hour()) > 11) ? "PM" : "AM"; break;
					// Day of the month, 2 digits with leading zeros
					case 'd': $value = "".$this->p_getLeadZero($this->get(LibDateTime::dayOfMonth()), 2); break;
					// 12-hour format of an hour without leading zeros
					case 'g': $value = "".((($this->get(LibDateTime::hour()) + 11) % 12) + 1); break;
					// 24-hour format of an hour without leading zeros
					case 'G': $value = "".$this->get(LibDateTime::hour()); break;
					// 12-hour format of an hour with leading zeros
					case 'h': $value = "".$this->p_getLeadZero((($this->get(LibDateTime::hour()) + 11) % 12) + 1, 2); break;
					// 24-hour format of an hour with leading zeros
					case 'H': $value = "".$this->p_getLeadZero($this->get(LibDateTime::hour()), 2); break;
					// Minutes with leading zeros
					case 'i': $value = "".$this->p_getLeadZero($this->get(LibDateTime::minute()), 2); break;
					// Day of the month without leading zeros
					case 'j': $value = "".$this->get(LibDateTime::month()); break;
					// A full textual representation of the day of the week
					case 'l': $value = "".$GLOBALS['iv_cal_daynames'][$this->get(LibDateTime::dayOfWeek())]; break;
					// Whether it's a leap year. 1 if it is a leap year, 0 otherwise.
					case 'L': $value = ($this->isLeapYear()) ? "1" : "0"; break;
					// Numeric representation of a month, with leading zeros
					case 'm': $value = "".$this->p_getLeadZero($this->get(LibDateTime::month()), 2); break;
					// Numeric representation of a month, without leading zeros
					case 'n': $value = "".$this->get(LibDateTime::month()); break;
					// Seconds, with leading zeros
					case 's': $value = "".$this->p_getLeadZero($this->get(LibDateTime::second()), 2); break;
					// Number of days in the given month
					case 't': $value = "".$this->getMax(LibDateTime::dayOfMonth()); break;
					// Numeric representation of the day of the week. 0 (for Monday) through 6 (for Sunday)
					case 'w': $value = "".$this->get(LibDateTime::dayOfWeek()); break;
					// ISO-8601 week number of year, weeks starting on Monday
					case 'W': $value = "".$this->get(LibDateTime::week()); break;
					// A full numeric representation of a year, 4 digits
					case 'Y': $value = "".$this->get(LibDateTime::year()); break;
					// A two digit representation of a year
					case 'y': $value = subStr($this->get(LibDateTime::year()), 2); break;
					// The day of the year
					case 'z': $value = $this->get(LibDateTime::dayOfYear());
				}
				if (strpos($result, $letter) !== false) {
					if ($value != "") $result = str_Replace($letter, $value, $result);
				}
			}
			return $result;
		}

		/**
		 * Private function to return a value with leading zeros
		 *@access private
		 *@param int $value an amount to give leading zeros
		 *@param int $length the length the value should have
		 *@param string the value with leading zeros. The length of the string matches $length.
		 */
		function p_getLeadZero($value, $length) {
			$value = "".$value;
			$need = $length - strLen($value);
			for ($i = 0; $i < $need; $i++) { $value = "0" . $value; }
			return $value;
		}

		/**
		 * Private function to calculate other time fields
		 *@access private
		 *@param string $element the element that has changed.
		 */
		function p_calculate($element) {
			switch($element) {
				case "dayOfYear": // Day of year has changed. Calculate the new date, and recalculate all.
					$doy = $this->get($element);
					$jDate = gregorianToJD(1, 1, $this->get("year"));
					$jDate = $jDate + ($doy - 1);
					list($gregMonth, $gregDay, $gregYear) = explode("/", JDtoGregorian($jDate));
					$this->p_set("year", $gregYear);
					$this->p_set("month", $gregMonth);
					$this->p_set("dayOfMonth", $gregDay);

					// calculate new values using this new date
					$this->p_calculate("year");
					break;
				case "week": // Week of year has changed. Calculate the new date, and recalculate all.
					$week = $this->get("week"); // this value has altered, calculate the new date

					$jDate = gregorianToJD(1, 1, $this->get("year"));
					$jDate2001 = gregorianToJD(1, 1, 2001); // 1 january 2001 is a monday
					$daysDiff = $jDate - $jDate2001;
					if ($daysDiff < 0) {
						$daysDiff = ($jDate2001 - $jDate);
						$dow = 7 - ($daysDiff % 7); // calculate from the other side of the week
					} else {
						$dow = ($daysDiff % 7);
					}
					$jDate = $jDate - $dow + (($week-1) * 7);
					list($gregMonth, $gregDay, $gregYear) = explode("/", JDtoGregorian($jDate));
					$this->p_set("year", $gregYear);
					$this->p_set("month", $gregMonth);
					$this->p_set("dayOfMonth", $gregDay);

					// calculate new values using this new date
					$this->p_calculate("year");
					break;
				default: 	// calculate the week, dayofyear and dayofweek fields using day, month and year
					$this->p_set('dayOfMonth', $this->get('dayOfMonth'));
					// day of year
					$month = $this->get("month");
					$dom = $this->get("dayOfMonth");
					$doy = $dom;
					if ($month > 1) {
						for ($i = 1; $i < $month; $i++) {
							$this->p_set("month", $i);
							$doy += $this->getMax("dayOfMonth");
						}
					}
					$this->p_set("month", $month);
					$this->p_set("dayOfYear", $doy);
					// day of week
					$jDate = gregorianToJD($this->get("month"), $this->get("dayOfMonth"), $this->get("year"));
					$jDate2001 = gregorianToJD(1, 1, 2001); // 1 january 2001 is a monday
					$daysDiff = $jDate - $jDate2001;
					if ($daysDiff < 0) {
						$daysDiff = ($jDate2001 - $jDate);
						$dow = 7 - ($daysDiff % 7); // calculate from the other side of the week
					} else {
						$dow = ($daysDiff % 7);
					}
					$this->p_set("dayOfWeek", $dow);
					$fdoy = ($dow + 8 - ($doy % 7)) % 7;
					$woy = ceil(($doy + $fdoy + 1 - $dow) / 7);
					if (($month == 12) && (($dom + (7 - $dow)) > 31 )) $woy = 1; // first week of new year
					$this->p_set("week", $woy);
					break;
			}
		}

		/**
		* Compare function for sorting an array with LibDateTime Objects
		**/
		function compare($a, $b) {
       if ($a->isEqual($b)) return 0;
       return ($a->after($b)) ? +1 : -1;
		}

		// element name constants. Usable as LibDateTime::second()
		static function second() { return "second"; }
		static function minute() { return "minute"; }
		static function hour() { return "hour"; }
		static function dayOfMonth() { return "dayOfMonth"; }
		static function dayOfYear() { return "dayOfYear"; }
		static function dayOfWeek() { return "dayOfWeek"; }
		static function day() { return LibDateTime::dayOfMonth(); }
		static function week() { return "week"; }
		static function month() { return "month"; }
		static function year() { return "year"; }
	}

	define("ivDay",LibDateTime::day());
	define("ivMonth",LibDateTime::month());
	define("ivYear",LibDateTime::year());
	define("ivHour",LibDateTime::hour());
	define("ivMinute",LibDateTime::minute());
	define("ivSecond",LibDateTime::second());
	define("ivWeek",LibDateTime::week());

?>
