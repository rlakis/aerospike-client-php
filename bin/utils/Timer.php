<?php

class ElapseTime {
        private $_total = 0;
        private $_start = 0;
        private $_stop = 0;

        public function start(){
            $this->_start = microtime(TRUE);
        }

        public function stop(){
            $this->_stop = microtime(TRUE);
            $this->_total = $this->_total + $this->_stop - $this->_start;
        }

        public function get_elapse(){
            return sprintf("%.6f",($this->_stop - $this->_start)*1000.0);
        }

        public function get_total_elapse(){
            return sprintf("%.6f", $this->_total*1000.0);
        }


}

?>
