<?php

namespace Mourjan;

class Config {
    public $serverId;
    public $host;
    public $baseDir;
    public $cssDir;
    public $jsURL;
    public $baseURL;
    public $cssURL;
    public $imgURL;
    public $assetsURL;
    public $adImgURL;
    public $imgLibURL;
    public $modules;
    
    public static function instance() : Config {}
    public function init(array $parameters) : void {}
    public function setValue(string $key, $value) : void {}        
    public function get(string $key) {}      
    public function enabledUsers() : bool {}        
    public function enabledAds() : bool {}       
    public function disableAds() : void {}        
    public function isMaintenanceMode() : bool {}
    public function modelFile(string $file) : string {}
    public function libFile(string $file) : string {}
    public function layoutFile(string $file) : string {}
    public function getFbURI() : string {}
    
    /* php functions */
    public function incModelFile(string $file) : Config {}    
    public function incLibFile(string $file) : Config {}    
    public function incLayoutFile(string $file) : Config {}
}


class Dictionary {
    protected $countries;
    protected $cities;
    protected $roots;
    protected $sections;
    protected $purposes;	
    protected $pageRoots;

    public static function instance() : Dictionary {}
    public function setCountries(array $kCountries) : void {}
    public function setCities(array $kCities) : void {}
    public function setRoots(array $kRoots) : void {}
    public function setSections(array $kSections) : void {}
    public function setPurposes(array $kPurposes) : void {}
    public function pageRoots() : array {}
    public function setPageRoots(array $kPageRoots) : void {}
    public function isCountryExists(int $kCountryId) : bool {}
    public function isCityExists(int $kCityId) : bool {}
    public function isSectionExists(int $kSectionId) : bool {}
    public function getCity(int $kCityId) : array {}
    public function getCityCountryId(int $kCityId) : int {}
    public function getSection(int $kSectionId) : array {}
    public function getSectionRootId(int $kSectionId) : int {}
}