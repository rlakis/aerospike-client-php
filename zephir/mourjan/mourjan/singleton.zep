namespace Mourjan;

abstract class Singleton {
    protected static instances = [];
    
    protected function __construct() -> void {}
    
    protected static function getInstance() -> <Singleton> {
    	var class_name = get_called_class();
    	
        if (! isset(Singleton::instances[ class_name ]) ) {
        	error_log("return from cache Singleton class: " . class_name );
            let Singleton::instances[class_name] = new {class_name}();
        }
        return Singleton::instances[class_name];
    }
    
    
    private function __clone() {}
    private function __wakeup() {}
}
