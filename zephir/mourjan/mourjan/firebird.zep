namespace Mourjan;
use Mourjan\Dictionary;

class Firebird extends Singleton {
	private readOnly = true { get };
	private dbh;

	public static function instance() -> <Firebird> {
		return static::getInstance();
	}

	protected function __construct() -> void {
		error_log( "Firebird.__construct db user: " . Config::instance()->get("db_user") . " / " . Config::instance()->getFbURI() );

		error_log( var_export(Dictionary::instance(), true) );
	}


	public function setReadOnly(bool value) {
		if (this->readOnly!==value) {

		}
		
		if (value === true) {

		}
	}


	private function connect() -> void {
		if (this->dbh!==null) { return; }
		var uri, user, password, options;
		let uri = Config::instance()->getFbURI();
		if (uri) {
			let options = [
					\PDO::ATTR_PERSISTENT:			true,
                    \PDO::ATTR_AUTOCOMMIT:			false,
                    \PDO::ATTR_EMULATE_PREPARES:	false,
                    \PDO::ATTR_STRINGIFY_FETCHES:	false,
                    \PDO::ATTR_TIMEOUT:				5,
                    \PDO::ATTR_ERRMODE:				\PDO::ERRMODE_EXCEPTION,

                    \PDO::FB_ATTR_COMMIT_RETAINING:	false,
                    \PDO::FB_ATTR_READONLY:			this->readOnly
			];

			if (this->readOnly) {
				let options[\PDO::FB_TRANS_ISOLATION_LEVEL]=\PDO::FB_TRANS_CONCURRENCY;
				let options[\PDO::FB_ATTR_TIMEOUT]=0;
			}
			else {
				let options[\PDO::FB_TRANS_ISOLATION_LEVEL]=\PDO::FB_TRANS_COMMITTED;
				let options[\PDO::FB_ATTR_TIMEOUT]=8;
			}
			let user = Config::instance()->get("db_user");
			let password = Config::instance()->get("db_pass");
        	let this->bdh = new \PDO(uri, user, password, options);
 		}
    }


	public function inTransaction() -> bool {
        if (this->dbh===NULL) {
            return false;
        }
        
        return this->dbh->inTransaction();
    }
    
    
}