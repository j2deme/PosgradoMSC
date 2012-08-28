<?php
/**
 * Multi purpose library
 * @author Jaime Jesús Delgado Meraz
 */
class Toolbox {

	/**
	 * Turns an associative array into objects.
	 *
	 * @param array $data Associative array.
	 * @return object Object created from associative array.
	 */
	function objectify($data) {
		$object = (object)$data;
		foreach ($object as $property) {
			if (is_array($property))
				$this -> objectify($property);
		}
		return $object;
	}

	/**
	 * Generates a random positive integer within a range.
	 *
	 * @param int $min The lowest value to return. (default: 0)
	 * @param int $max The highest value to return. (default: 100)
	 * @return int A pseudo random value between min (or 0) and max (or 100, inclusive).
	 * @see get_random_string_from_list()
	 * @see get_random_string()
	 * @see get_random_float()
	 * @see get_random_hex_color()
	 * @see get_random_file()
	 */
	function get_random_number($min = 0, $max = 100) {
		return rand($min, $max);
	}

	/**
	 * Generates a random string from a given array of words.
	 *
	 * @param array $list_of_words Set of words to choose from.
	 * @return string Random string from given set.
	 * @see get_random_number()
	 * @see get_random_string()
	 * @see get_random_float()
	 * @see get_random_hex_color()
	 * @see get_random_file()
	 */
	function get_random_string_from_list($list_of_words) {
		return $list_of_words[rand(0, sizeof($list_of_words) - 1)];
	}

	/**
	 * Generates an alphanumeric random string of a given length.
	 *
	 * @param int $length Length of string. (default: 6)
	 * @param string $characters Set of characters to use. (default: Upper & Lowercase alphanumeric, Hyphen & Dash)
	 * @return string Random string.
	 * @see get_random_number()
	 * @see get_random_string_from_list()
	 * @see get_random_float()
	 * @see get_random_hex_color()
	 * @see get_random_file()
	 */
	function get_random_string($length = 6, $characters = "ABCDEFGHIJKLMNOPRQSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_") {
		$num_characters = strlen($characters) - 1;
		$text = "";
		while (strlen($text) < $length) {
			$text .= $characters[mt_rand(0, $num_characters)];
		}
		return $text;
	}

	/**
	 * Generates a human readable string that will look more close to dictionary words (useful for captchas).
	 *
	 * @param int $length Length of random string, must be a multiple of 2. (Default: 6)
	 * @return string Generated password.
	 */
	function readable_random_string($length = 6) {
		$conso = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z");
		$vocal = array("a", "e", "i", "o", "u");
		$password = "";
		srand((double)microtime() * 1000000);
		$max = $length / 2;
		for ($i = 1; $i <= $max; $i++) {
			$password .= $conso[rand(0, 19)];
			$password .= $vocal[rand(0, 4)];
		}
		return $password;
	}

	/**
	 * Generates a random positive float within a given range.
	 *
	 * @param float $min The lowest value to return. (default: 0.1)
	 * @param float $max The highest value to return. (default: 0.9)
	 * @return float Random float number.
	 * @see get_random_number()
	 * @see get_random_string_from_list()
	 * @see get_random_string()
	 * @see get_random_hex_color()
	 * @see get_random_file()
	 */
	function get_random_float($min = 0.1, $max = 0.9) {
		return ($min + lcg_value() * (abs($max - $min)));
	}

	/**
	 * Generates a random hexadecimal value to use as a color.
	 *
	 * @param string $values Characters to generate a valid hexadecimal color value. (default: abcdef0123456789)
	 * @param int $length Positive value to generate a valid hexadecimal color value. (default: 6)
	 * @return string A valid hexade*cimal color.
	 * @see get_random_number()
	 * @see get_random_string_from_list()
	 * @see get_random_string()
	 * @see get_random_float()
	 * @see get_random_file()
	 */
	function get_random_hex_color($values = 'abcdef0123456789', $length = 6) {
		$num_characters = strlen($characters) - 1;
		while (strlen($code) < $length) {
			$return .= $characters[mt_rand(0, $num_characters)];
		}
		return '#' . $return;
	}

	/**
	 * Choose a random file from an specified directory
	 *
	 * @param string $dir Directory path
	 * @return string Selected file from given directory
	 * @see get_random_number()
	 * @see get_random_string_from_list()
	 * @see get_random_string()
	 * @see get_random_float()
	 * @see get_random_hex_color()
	 */
	function get_random_file($dir) {
		while (false !== ($file = readdir($dir))) {
			$files[] = $file;
		}
		return $files[rand(0, sizeof($files) - 1)];
	}

	/**
	 * Get days in a month, taking into consideration leap years.
	 *
	 * @param int $month Month.
	 * @param int $year Year.
	 * @return int Days in a month.
	 */
	function get_days_in_month($month, $year) {
		return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
	}

	/**
	 * Translate month number to its name in spanish, in consideration that using date("F")
	 * will return the name of the month in english.
	 *
	 * @param int $num Month number.
	 * @return string Month name in spanish.
	 */
	function spanish_months($num) {
		$months = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
		return $months[$num - 1];
	}

	/**
	 * Calculates the age of a person based on a given date.
	 *
	 * @param string $date A string containing an English date format or a Unix timestamp.
	 * @return string Age as an integer or an empty string if date format is invalid.
	 */
	function age($date) {
		$year_diff = '';
		$time = strtotime($date);
		if (FALSE === $time) {
			return 0;
		}

		$date = date('Y-m-d', $time);
		list($year, $month, $day) = explode("-", $date);
		$year_diff = date("Y") - $year;
		$month_diff = date("m") - $month;
		$day_diff = date("d") - $day;
		if ($day_diff < 0 || $month_diff < 0)
			$year_diff--;
		return $year_diff;
	}

	/**
	 * Given a period of time in seconds turns it to years, days, hours, minutes and seconds.
	 *
	 * @param int $time Amount of time in seconds.
	 * @return array|boolean Array of corresponding values or FALSE if $time isn't numeric.
	 */
	function seconds2time($time) {
		if (is_numeric($time)) {
			$value = array("years" => 0, "days" => 0, "hours" => 0, "minutes" => 0, "seconds" => 0, );
			if ($time >= 31556926) {
				$value["years"] = floor($time / 31556926);
				$time = ($time % 31556926);
			}
			if ($time >= 86400) {
				$value["days"] = floor($time / 86400);
				$time = ($time % 86400);
			}
			if ($time >= 3600) {
				$value["hours"] = floor($time / 3600);
				$time = ($time % 3600);
			}
			if ($time >= 60) {
				$value["minutes"] = floor($time / 60);
				$time = ($time % 60);
			}
			$value["seconds"] = floor($time);

			return (array)$value;
		} else {
			return (bool)FALSE;
		}
	}

	/**
	 * Determines the difference between two timestamps.
	 *
	 * The difference is returned in a human readable format such as "1 hour",
	 * "5 mins", "2 days".
	 *
	 * @param int $from Unix timestamp from which the difference begins.
	 * @param int $to Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
	 * @return string Human readable time difference.
	 */
	function human_time_diff($from, $to = '') {
		if (empty($to))
			$to = time();
		$diff = (int) abs($to - $from);
		if ($diff <= 3600) {
			$mins = round($diff / 60);
			if ($mins <= 1) {
				$mins = 1;
			}
			/* translators: min=minute */
			$since = sprintf(_n('%s min', '%s mins', $mins), $mins);
		} else if (($diff <= 86400) && ($diff > 3600)) {
			$hours = round($diff / 3600);
			if ($hours <= 1) {
				$hours = 1;
			}
			$since = sprintf(_n('%s hour', '%s hours', $hours), $hours);
		} elseif ($diff >= 86400) {
			$days = round($diff / 86400);
			if ($days <= 1) {
				$days = 1;
			}
			$since = sprintf(_n('%s day', '%s days', $days), $days);
		}
		return $since;
	}

	/**
	 * Returns the distance between two geopositions in the specified unit.
	 *
	 * @param float $lat1 Latitude of first geopoint.
	 * @param float $lon1 Longitude of first geopoint.
	 * @param float $lat2 Latitude of second geopoint.
	 * @param float $lon2 Longitude of second geopoint.
	 * @param string $unit K for kilometers, M for meters (Default: miles).
	 * @return int|float Distance between the geopositions.
	 * */
	function distance($lat1, $lon1, $lat2, $lon2, $unit) {
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344);
		} else if ($unit == "M") {
			return ($miles * 1.609344 * 1000);
		} else {
			return ($miles * 1.609344);
		}
	}

	/**
	 * Get the latitude and longitude of a given address.
	 *
	 * @param string $address Human readabable address.
	 * @return array Geoposition values with "lat" and "lon" indexes.
	 */
	function get_lat_long($address) {
		if (!is_string($address)) {
			return FALSE;
		} else {
			$_url = sprintf('http://maps.google.com/maps?output=js&q=%s', rawurlencode($address));
			$_result = FALSE;
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $_url);
			curl_setopt($curl, CURLOPT_PORT, 80);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$_result = curl_exec($curl);
			if ($_result) {
				if (strpos($_result, 'errortips') > 1 || strpos($_result, 'Did you mean:') !== FALSE) {
					return FALSE;
				}
				preg_match('!center:\s*{lat:\s*(-?\d+\.\d+),lng:\s*(-?\d+\.\d+)}!U', $_result, $_match);
				$_coords['lat'] = $_match[1];
				$_coords['lon'] = $_match[2];
				return $_coords;
			}
		}
	}

	/**
	 * Reverse geocoding from latitude and longitude values to the nearest place.
	 *
	 * @param float $lat Latitude of place.
	 * @param float $long Longitude of place.
	 * @param string $provider Provider to get the results from. Available: Google, OSM. (Default: OSM)
	 * @param boolean $array If TRUE instead of formatted address, the complete result from provider will be returned. (Default: FALSE)
	 * @return string|array Display name from nearest place or complete result from provider. FALSE if no result is returned.
	 */
	function reverse_geocode($lat, $long, $provider = "osm", $array = FALSE) {
		$providers = array("google" => "http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&sensor=false", "osm" => "http://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$long");
		$provider = strtolower($provider);
		$url = $providers["$provider"];
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_PORT, 80);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($curl);
		if ($json) {
			$addresses = json_decode($json);
			if ($provider == "google") {
				$address = $addresses -> results[0] -> formatted_address;
			} else {
				$address = $addresses -> display_name;
			}

			/*if($array){
			 return $addresses;
			 } else {
			 return $address;
			 }*/
			return ($array ? $addresses : $address);
		} else {
			return FALSE;
		}
	}

	/**
	 * Get last twitter status from given user.
	 *
	 * @param string $user Whose last status will be retrieved.
	 * @return string|boolean Linkified text from status retrieved or boolean.
	 */
	function last_twitter_status($user) {
		$url = sprintf("http://twitter.com/statuses/user_timeline/%s.json", $user);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_PORT, 80);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($curl);
		$statuses = json_decode($json);
		if (is_array($statuses)) {
			if (isset($statuses[0])) {
				$status_text = $statuses[0] -> text;
				return $this -> linkify_twitter_status($status_text);
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Turn all HTTP URLs, Twitter \@usernames, and \#tags into clickable links.
	 *
	 * @param string $status_text Twitter status in plain text.
	 * @return string Linkified status text.
	 * @see linkify()
	 * @see remove_urls()
	 */
	function linkify_twitter_status($status_text) {
		// linkify URLs
		$status_text = preg_replace('/(https?:\/\/\S+)/', '<a href="\1">\1</a>', $status_text);
		// linkify twitter users
		$status_text = preg_replace('/(^|\s)@(\w+)/', '\1@<a href="http://twitter.com/\2">\2</a>', $status_text);
		// linkify tags
		$status_text = preg_replace('/(^|\s)#(\w+)/', '\1#<a href="http://search.twitter.com/search?q=%23\2">\2</a>', $status_text);

		return utf8_decode($status_text);
	}

	/**
	 * Turn all HTTP, HTTPS, FTP and mailto URLs into clickable links.
	 *
	 * @param string $text Plain text lo linkify.
	 * @return string Linkified text.
	 * @see linkify_twitter_status()
	 * @see remove_urls()
	 */
	function linkify($text) {
		$text = preg_replace('/(https?:\/\/\S+)/', '<a href="\1">\1</a>', $text);
		$text = preg_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\\1">\\1</a>', $text);
		$text = preg_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\\1<a href="http://\\2">\\2</a>', $text);
		$text = preg_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', '<a href="mailto:\\1">\\1</a>', $text);

		return $text;
	}

	/**
	 * Strip out HTTP, HTTPS, FTP and file links from a given text.
	 *
	 * @param string $text Text with links.
	 * @return string Plain Text.
	 * @see linkify()
	 * @see linkify_twitter_status()
	 */
	function remove_urls($text) {
		$text = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $text);
		return $text;
	}

	/**
	 * Modifies a string to remove all non ASCII characters and spaces.
	 *
	 * @param string $str String to slugified.
	 * @param array $replace Set of characters to replace with space.
	 * @param string $delimiter Character to separate words.
	 */
	function slugify($text, $separator = 'dash', $lowercase = TRUE) {
		if(!$this->isUTF8($text)){
			$text = utf8_encode($text);
		}
		$text = strip_tags($text);
		$text = preg_replace("`\[.*\]`U", "", $text);
		$text = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $text);
		$text = htmlentities($text, ENT_COMPAT, 'utf-8');
		$text = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $text);
		$text = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $text);

		if ($lowercase === TRUE) {
			$text = strtolower($text);
		}

		if ($separator != 'dash') {
			$text = str_replace('-', '_', $text);
			$separator = '_';
		} else {
			$separator = '-';
		}

		return trim($text, $separator);

	}

	function slug($name, $utf8 = true) {
		if(!$this->isUTF8($name)){
			$name = utf8_encode($name);
		}
		$sname = trim($name);
		$sname = strtolower(preg_replace('/\s+/', '-', $sname));

		$table = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'C' => 'C', 'c' => 'c', 'C' => 'C', 'c' => 'c', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'S', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'R' => 'R', 'r' => 'r', ',' => '');
		$sname = strtr($sname, $table);
		if ($utf8) {
			$sname = utf8_decode($sname);
		}
		$sname = preg_replace('/[^A-Za-z0-9-]+/', "", $sname);

		return $sname;
	}

	function stripAccents($text) {
		return strtr($text, 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ', 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	}

	function isUTF8($string) {
		for ($idx = 0, $strlen = strlen($string); $idx < $strlen; $idx++) {
			$byte = ord($string[$idx]);

			if ($byte & 0x80) {
				if (($byte & 0xE0) == 0xC0) {
					// 2 byte char
					$bytes_remaining = 1;
				} else if (($byte & 0xF0) == 0xE0) {
					// 3 byte char
					$bytes_remaining = 2;
				} else if (($byte & 0xF8) == 0xF0) {
					// 4 byte char
					$bytes_remaining = 3;
				} else {
					return FALSE;
				}

				if ($idx + $bytes_remaining >= $strlen) {
					return FALSE;
				}

				while ($bytes_remaining--) {
					if ((ord($string[++$idx]) & 0xC0) != 0x80) {
						return FALSE;
					}
				}
			}
		}

		return TRUE;
	}

	/**
	 * Generates variable length and strength passwords.
	 *
	 * @param int $length Length of generated password. (Default: 9)
	 * @param int $strength Strength of the password. (Default: 0, Max: 10)
	 * @return string Generated password.
	 */
	function generate_password($length = 9, $strength = 0) {
		$vowels = 'aeiouy';
		$consonants = 'bcdfghjklmnpqrstvwxz';
		if ($strength >= 1) {
			$consonants .= 'BCDFGHJKLMNPQRSTVWXYZ';
		}
		if ($strength >= 2) {
			$vowels .= "AEIOU";
		}
		if ($strength >= 4) {
			$consonants .= '123456789';
		}
		if ($strength >= 8) {
			$vowels .= '@#$%';
		}

		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
	
	/**
	 * Generates a salted password
	 */
	function salted_password($password){
		$salt = sha1(md5($password));
		$salted_pass = md5($password.$salt); 
		return $salted_pass;
	}
	
	/**
	 * Validates if a given email is valid by using Regex and domain check.
	 *
	 * @param string $email Email to validate.
	 * @return boolean TRUE if email is valid or FALSE if not.
	 */
	function is_valid_email($email) {
		if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)) {
			list($username, $domain) = split('@', $email);
			if (!checkdnsrr($domain, 'MX')) {
				return FALSE;
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Converts email addresses characters to HTML entities to block spam bots.
	 *
	 * @param string $emailaddy Email address.
	 * @param int $mailto Optional. Range from 0 to 1. Used for encoding.
	 * @return string Converted email address.
	 */
	function antispambot($emailaddy, $mailto = 0) {
		$emailNOSPAMaddy = '';
		srand((float) microtime() * 1000000);
		for ($i = 0; $i < strlen($emailaddy); $i = $i + 1) {
			$j = floor(rand(0, 1 + $mailto));
			if ($j == 0) {
				$emailNOSPAMaddy .= '&#' . ord(substr($emailaddy, $i, 1)) . ';';
			} elseif ($j == 1) {
				$emailNOSPAMaddy .= substr($emailaddy, $i, 1);
			} elseif ($j == 2) {
				$emailNOSPAMaddy .= '%' . zeroise(dechex(ord(substr($emailaddy, $i, 1))), 2);
			}
		}
		$emailNOSPAMaddy = str_replace('@', '&#64;', $emailNOSPAMaddy);
		return $emailNOSPAMaddy;
	}

	/**
	 * Add leading zeros when necessary.
	 *
	 * If you set the threshold to '4' and the number is '10', then you will get
	 * back '0010'. If you set the threshold to '4' and the number is '5000', then you
	 * will get back '5000'.
	 *
	 * Uses sprintf to append the amount of zeros based on the $threshold parameter
	 * and the size of the number. If the number is large enough, then no zeros will
	 * be appended.
	 *
	 * @param mixed $number Number to append zeros to if not greater than threshold.
	 * @param int $threshold Digit places number needs to be to not have zeros added.
	 * @return string Adds leading zeros to number if needed.
	 */
	function zeroise($number, $threshold) {
		return sprintf('%0' . $threshold . 's', $number);
	}

	/**
	 * Get file size in human readable format.
	 *
	 * @param string $file Filepath
	 * @param int $digits Digits to display (default: 2)
	 * @return string|bool Size (KB, MB, GB, TB) or boolean
	 */
	function get_filesize($file, $digits = 2) {
		if (is_file($file)) {
			$filePath = $file;
			if (!realpath($filePath)) {
				$filePath = $_SERVER["DOCUMENT_ROOT"] . $filePath;
			}
			$fileSize = filesize($filePath);
			$sizes = array("TB", "GB", "MB", "KB", "B");
			$total = count($sizes);
			while ($total-- && $fileSize > 1024) {
				$fileSize /= 1024;
			}
			return round($fileSize, $digits) . " " . $sizes[$total];
		}
		return false;
	}
	
	/**
	 * Bytes to human readable format.
	 *
	 * @param int $fileSize File size in bytes
	 * @param int $digits Digits to display (default: 2)
	 * @return string Size (KB, MB, GB, TB)
	 */
	function bytes2human($fileSize, $digits = 2) {
			$sizes = array("TB", "GB", "MB", "KB", "B");
			$total = count($sizes);
			while ($total-- && $fileSize > 1024) {
				$fileSize /= 1024;
			}
			return round($fileSize, $digits) . " " . $sizes[$total];
	}

	/**
	 * Unzip zip file.
	 *
	 * @param string $file Path to zip file
	 * @param string $destination Destination directory for unzipped files, directory MUST exist.
	 * @return boolean TRUE if files were extracted succesfully, FALSE if something failed.
	 */
	function unzip_file($file, $destination) {
		$zip = new ZipArchive();
		if ($zip -> open($file) !== TRUE) {
			return FALSE;
		}
		$zip -> extractTo($destination);
		$zip -> close();
		return TRUE;
	}
	
	/**
	 * Humanize String
	 * 
	 * @param string $string String to me humanized.
	 */
	function humanize_string($string){
		return utf8_encode(ucwords(strtolower(utf8_decode($string))));
	}
}
?>
