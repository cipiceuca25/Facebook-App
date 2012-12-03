<?php
/**
 * A Date class holds some common date functions
 * 
 */
class Fancrank_Util_Date extends Zend_Date
{
	/*
	 * 
	 * @param string month
	 * @param string year
	 * @return a string representation of a giving date in following format: yyyy-MM-dd 
	 */
	public static function lastdayOfTheMonth($month = '', $year = '') {
		$month = empty($month) ? date('m') : $month;
		$year = empty($year) ? date('Y') : $year;
		$result = strtotime("{$year}-{$month}-01");
		$result = strtotime('-1 second', strtotime('+1 month', $result));
		return date('Y-m-d', $result);
	}
	
	/*
	 * 
	 * @param string month
	 * @param string year
	 * @return a string representation of a giving date in following format: yyyy-MM-dd 
	 */
	public static function firstdayOfTheMonth($month = '', $year = '') {
		$month = empty($month) ? date('m') : $month;
		$year = empty($year) ? date('Y') : $year;
		$result = strtotime("{$year}-{$month}-01");
		return date('Y-m-d', $result);
	}

	public static function firstdayOfLastMonth() {
		return date('y-m-01', strtotime('last month'));
	}
	
	public static function lastdayOfLastMonth() {
		return date('y-m-t', strtotime('last month'));
	}
	
	public static function firstdayOfNextMonth() {
		return date('y-m-01', strtotime('next month'));
	}
	
}
?>