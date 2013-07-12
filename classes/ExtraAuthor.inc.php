<?php

/**
 * @file classes/ExtraAuthor.inc.php
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExtraAuthor
 *
 * @brief Add extras functionalities to Article author metadata class.
 */


import('classes.article.Author');

class ExtraAuthor extends Author {
	/**
	 * Constructor.
	 */
	function ExtraAuthor() {
		parent::Author();
	}

	//
	// Get/set methods
	//

	/**
	 * Get Affiliation Organization Name.
	 * @return string
	 */
	function getAffOrganizationName() {
		return $this->getData('aff-orgname', $locale);
	}

	/**
	 * Set author affiliation organization name.
	 * @param $orgname string
	 * @param $locale string
	 */
	function setAffOrganizationName($orgname, $locale) {
		return $this->setData('aff-orgname', $orgname, $locale);
	}
	
	/**
	 * Get Affiliation Division 1 Name.
	 * @return string
	 */
	function getAffDivision1Name() {
		return $this->getData('aff-orgdiv1', $locale);
	}

	/**
	 * Set author affiliation division 1 name.
	 * @param $orgdiv string
	 * @param $locale string
	 */
	function setAffDivision1Name($orgdiv, $locale) {
		return $this->setData('aff-orgdiv1', $orgdiv, $locale);
	}
	
	/**
	 * Get Affiliation Division 2 Name.
	 * @return string
	 */
	function getAffDivision2Name() {
		return $this->getData('aff-orgdiv2', $locale);
	}

	/**
	 * Set author affiliation division 2 name.
	 * @param $orgdiv string
	 * @param $locale string
	 */
	function setAffDivision2Name($orgdiv, $locale) {
		return $this->setData('aff-orgdiv2', $orgdiv, $locale);
	}
	
	/**
	 * Get Affiliation Division 3 Name.
	 * @return string
	 */
	function getAffDivision3Name() {
		return $this->getData('aff-orgdiv3', $locale);
	}

	/**
	 * Set author affiliation division 3 name.
	 * @param $orgdiv string
	 * @param $locale string
	 */
	function setAffDivision3Name($orgdiv, $locale) {
		return $this->setData('aff-orgdiv3', $orgdiv, $locale);
	}
	
	/**
	 * Get Affiliation City.
	 * @return string
	 */
	function getAffCity() {
		return $this->getData('aff-city', $locale);
	}

	/**
	 * Set author affiliation city.
	 * @param $city string
	 * @param $locale string
	 */
	function setAffCity($city, $locale) {
		return $this->setData('aff-city', $city, $locale);
	}
	
	/**
	 * Get Affiliation Country.
	 * @return string
	 */
	function getAffCountry() {
		return $this->getData('aff-country', $locale);
	}

	/**
	 * Set author affiliation country.
	 * @param $country string
	 * @param $locale string
	 */
	function setAffCountry($country, $locale) {
		return $this->setData('aff-country', $country, $locale);
	}
	
	/**
	 * Get Affiliation State.
	 * @return string
	 */
	function getAffState() {
		return $this->getData('aff-state', $locale);
	}

	/**
	 * Set author affiliation state.
	 * @param $state string
	 * @param $locale string
	 */
	function setAffState($state, $locale) {
		return $this->setData('aff-state', $state, $locale);
	}
	
	/**
	 * Get Affiliation Zipcode.
	 * @return string
	 */
	function getAffZipcode() {
		return $this->getData('aff-zipcode', $locale);
	}

	/**
	 * Set author affiliation zipcode.
	 * @param $zipcode string
	 * @param $locale string
	 */
	function setAffZipcode($zipcode, $locale) {
		return $this->setData('aff-zipcode', $zipcode, $locale);
	}
	
	/**
	 * Get Affiliation Email.
	 * @return string
	 */
	function getAffEmail() {
		return $this->getData('aff-email', $locale);
	}

	/**
	 * Set author affiliation zipcode.
	 * @param $email string
	 * @param $locale string
	 */
	function setAffEmail($email, $locale) {
		return $this->setData('aff-email', $email, $locale);
	}
}

?>
