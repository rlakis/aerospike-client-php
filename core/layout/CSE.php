<?php
require_once 'Page.php';

class CSE extends Page{

    function CSE($router){
        $router->params['q']='';
        parent::Page($router);
        $this->title='Custom Search';
        $this->description=$this->lang['description'];
        $this->render();
    }   

    function body() {
        $colSpread='col2w';
        ?><div class="<?= $colSpread ?>"><?php 
         
        $this->CSEHeader();
        $this->CSEResults();
        ?></div><?php
        $this->footer();
    }

    function CSEResults(){
        ?><div id="cse-search-results"></div>
<script type="text/javascript" src="http://www.google.ae/coop/cse/brand?form=cse-search-box&lang=en"></script>
<div id="cse-search-results"></div>
<script type="text/javascript">
var googleSearchIframeName = "cse-search-results";
var googleSearchFormName = "cse-search-box";
var googleSearchFrameWidth = 970;
var googleSearchDomain = "www.google.ae";
var googleSearchPath = "/cse";
</script>
<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>

            <?php
    }

    function CSEHeader(){
        ?><div class='sum rc m_b'><div class="brd"><?php
        //echo "<a href='{$this->urlRouter->getURL($countryId)}'>{$countryName}</a> <span>{$this->lang['sep']}</span> ";
        ?><h1>Custom Search Test</h1></div>
        <form action="http://dev.mourjan.com/cse/en/" id="cse-search-box">
<div>
<input type="hidden" name="cx" value="partner-pub-2427907534283641:5761286675" />
<input type="hidden" name="cof" value="FORID:10" />
<input type="hidden" name="ie" value="UTF-8" />
<input type="text" name="q" size="55" class="q rc" />
<input type="submit" name="sa" value="<?= $this->lang['search'] ?>" class="rc bt" />
</div>
</form>

<script type="text/javascript" src="http://www.google.ae/coop/cse/brand?form=cse-search-box&lang=en"></script>
<br />
        </div><?php
    }
    
}
?>
