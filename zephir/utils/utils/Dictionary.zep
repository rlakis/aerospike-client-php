namespace Utils;

class Dictionary {
	private static inst;

	protected countries { get };
	protected cities { get };
	protected roots { get };
	protected sections { get };
	protected purposes { get };
	
	protected pageRoots { get };


	private function __construct() {
	}


	public static function instance() -> <Dictionary> {
		if Dictionary::inst !== null {
			return Dictionary::inst;
		}

		//var i = new Dictionary();
		let Dictionary::inst = new Dictionary();
		return Dictionary::inst;
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


	public function isSectionExists(int kSectionId) -> bool {
		return isset(this->sections[kSectionId]);
	}

	public function getSection(int kSectionId) -> array {
		return this->isSectionExists(kSectionId) ? this->sections[kSectionId] : [];
	}


	public function getSectionRootId(int kSectionId) -> int {
		return this->isSectionExists(kSectionId) ? intval(this->sections[kSectionId][4]) : 0;
	}

}