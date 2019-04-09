namespace Mourjan;

class Dictionary extends Singleton {
	protected countries { get };
	protected cities { get };
	protected roots { get };
	protected sections { get };
	protected purposes { get };
	
	protected pageRoots { get };

	public static function instance() -> <Dictionary> {
		return static::getInstance();
	}

	public function setCountries(array kCountries) -> void {
		let this->countries = kCountries;
	}


	public function setCities(array kCities) -> void {
		let this->cities = kCities;
	}


	public function setRoots(array kRoots) -> void {
		let this->roots = kRoots;
	}


	public function setSections(array kSections) -> void {
		let this->sections = kSections;
	}


	public function setPurposes(array kPurposes) -> void {
		let this->purposes = kPurposes;
	}


	public function pageRoots() -> array {
		return this->pageRoots;
	}

	
	public function setPageRoots(array kPageRoots) -> void {
		let this->pageRoots = kPageRoots;
	}


	public function isCountryExists(int kCountryId) -> bool {
		return isset(this->countries[kCountryId]);
	}


	public function isCityExists(int kCityId) -> bool {
		return isset(this->cities[kCityId]);
	}


	public function isSectionExists(int kSectionId) -> bool {
		return isset(this->sections[kSectionId]);
	}


	public function getCity(int kCityId) -> array {
		return this->isCityExists(kCityId) ? this->cities[kCityId] : [];
	}


	public function getCityCountryId(int kCityId) -> int {
		return this->isCityExists(kCityId) ? intval(this->cities[kCityId][4]) : 0;
	}


	public function getSection(int kSectionId) -> array {
		return this->isSectionExists(kSectionId) ? this->sections[kSectionId] : [];
	}


	public function getSectionRootId(int kSectionId) -> int {
		return this->isSectionExists(kSectionId) ? intval(this->sections[kSectionId][4]) : 0;
	}

}