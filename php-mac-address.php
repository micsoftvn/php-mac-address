<?php

/**
 * This class allows you to preform various operations with
 * Media Access Control (MAC addresses) on UNIX type systems.
 * 
 * @author Blake Gardner <blakegardner[at]cox.net>
 * @copyright Copyright (c) 2012, Blake Gardner
 * @license MIT License (see License.txt)
 */
class MAC_Address {

	/**
	 * Regular expression for matching and validating a MAC address
	 * @var string
	 */
	private static $valid_mac = "([0-9A-F]{2}[:-]){5}([0-9A-F]{2})";

	/**
	 * An array of valid MAC address characters
	 * @var array
	 */
	private static $mac_address_vals = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");

	/**
	 * Change the MAC address of the network interface specified
	 * @param string $interface Name of the interface e.g. eth0
	 * @param string $mac The new MAC address to be set to the interface
	 * @return bool Returns true on success else returns false
	 */
	public static function set_fake_mac_address($interface, $mac = NULL) {

		// if a valid mac address was not passed then generate one
		if (!self::validate_mac_address($mac)) {
			$mac = self::generate_mac_address();
		}

		// bring the interface down, set the new mac, bring it back up
		self::run_command("ifconfig {$interface} down");
		self::run_command("ifconfig {$interface} hw ether {$mac}");
		self::run_command("ifconfig {$interface} up");

		// run a test to see if the operation was a success
		if (self::get_current_mac_address() == $mac) {
			return TRUE;
		}

		// by default just return false
		return FALSE;
	}

	/**
	 * @return string generated MAC address
	 */
	public static function generate_mac_address() {
		$vals = self::$mac_address_vals;
		if (count($vals) >= 1) {
			$mac = array("00"); // set first two digits manually
			while (count($mac) < 6) {
				shuffle($vals);
				$mac[] = $vals[0] . $vals[1];
			}
			$mac = implode(":", $mac);
		}
		return $mac;
	}

	/**
	 * Make sure the provided MAC address is in the correct format
	 * @param string $mac
	 * @return bool TRUE if valid; otherwise FALSE
	 */
	public static function validate_mac_address($mac) {
		return (bool) preg_match("/^" . self::$valid_mac . "$/i", $mac);
	}

	/**
	 * Run the specified command and return it's output
	 * @param string $command
	 * @return string Output from command that was ran
	 */
	protected static function run_command($command) {
		return shell_exec($command);
	}

	/**
	 * Get the system's current MAC address
	 * @param string $interface The name of the interface e.g. eth0
	 * @return string|bool Systems current MAC address; otherwise FALSE on error
	 */
	public static function get_current_mac_address($interface) {
		$ifconfig = self::run_command("ifconfig {$interface}");
		preg_match("/" . self::$valid_mac . "/i", $ifconfig, $ifconfig);
		if (isset($ifconfig[0])) {
			return trim(strtoupper($ifconfig[0]));
		}
		return FALSE;
	}

}
