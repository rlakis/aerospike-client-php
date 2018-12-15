<?php
include_once get_cfg_var('mourjan.path').'/deps/autoload.php';
include_once get_cfg_var("mourjan.path") . '/config/cfg.php';
include_once $config['dir']. '/core/model/Router.php';

use Core\Model\Router;

$router = new Router($config);
$router->siteLanguage = filter_input(INPUT_GET, 'l', FILTER_SANITIZE_URL, ["options"=>['default'=>'ar']]);
$router->countryId = 2;
$router->cityId = 0;
$router->module = 'home';
$router->cache();

$title='Mourjan Classifieds';
echo '<!DOCTYPE html><HTML lang="en"><head>',"\n";
echo '<meta charset="utf-8">', "\n";
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">', "\n";
echo '<title>', $title, '</title>', "\n";
if(0){
    pushStyle("fa-pro-5.5.0/css/fontawesome.css");
    pushStyle("fa-pro-5.5.0/css/solid.css");
    pushStyle("flags/flags.css");
}


?>
<link rel='preconnect' href='https://pagead2.googlesyndication.com' />

<style>
@charset "UTF-8"; 
@font-face {
  font-family: system;
  font-style: normal;
  font-weight: 300;
  src: local(".SFNSText-Light"), local(".HelveticaNeueDeskInterface-Light"), 
      local(".LucidaGrandeUI"), local("Ubuntu Light"), local("Segoe UI Light"), 
      local("Roboto-Light"), local("DroidSans"), local("Tahoma");
}
html * {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
* { box-sizing: border-box; }
body{font-family: -apple-system, "system";font-style: normal;font-size:1em;background-color: whitesmoke;}
body{margin: 0; background-color: rgb(238, 238, 238); }
.float-left{ float: left; }
.float-right{ float: right; }
body[dir="rtl"] .float-left{ float: right; }
body[dir="rtl"] .float-right{ float: left; }
h4{font-size: 1.3em;}
li,dd,dt{line-height: 48px;}
:root { --mourjanC:rgba(10,61,98,1); 
        --midnight:rgba(44,62,80,1); 
        --color-1:steelblue;
        --color-2:firebrick;
        --color-3:limegreen;
        --color-4:darkorchid;
        --color-99:orange;
}

.wrapper {
    position: relative;
    top: 0;
    height: 100vh;
}

.col-1, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-10, .col-11, .col-12 {width: 100%; padding: 0; float: left}
body[dir="rtl"] [class*="col-"] { float: right; }

.row{
    margin-right: 0;
    margin-left: 0;
}
.row::after {
    content: "";
    clear: both;
    display: table;
}
.header {
    background-color: var(--mourjanC);
    color: #ffffff;
    padding: 0;
    text-align: center;
    min-height: 60px;
}
.header>h1{
    -webkit-margin-before:0.1em;
    -webkit-margin-after:0.1em;
    font-size: 1.8em;
}
.header .banner {
    background: url(https://www.mourjan.com/css/5.4.7/i/wbl.jpg) no-repeat;
    height: 200px;
}
.footer {

}
ul {
    list-style-type: none;
    list-style-position: inside;
}
li>i {margin: 0 4px; max-width: 32px;}

.menu ul {
    margin-bottom: 12px;
    margin-top: 8px;
    padding: 0;
}
.menu li {
    padding: 8px;
    margin-bottom: 1px;
    background-color: white;
    color: var(--midnight);
    font-size: 1.1em;
    line-height: 40px;
}
.menu li:hover { background-color:rgba(200, 200, 200, 0.2); color: var(--mourjanC);}
/*.menu li:before {}
.menu li>i { margin: 0 4px; max-width: 32px; }*/

.menu a {
    text-decoration: none;
    color: var(--midnight);
}
.menu a>span { font-size: small; font-weight: bolder; color:dimgray; }
.menu a>span:after {
    content: "\00a0";
    padding: 0 3px;
    margin: 0 4px;
    position: relative;
    display: inline-block;
    height: 100%;
    max-width: 32px;
    background-color: #8E8E93;
    -webkit-mask: url(fa-pro-5.5.0/svgs/light/angle-right.svg) no-repeat 50% 50%;
}

body[dir="rtl"] .menu a>span:after {
    -webkit-mask: url(fa-pro-5.5.0/svgs/light/angle-left.svg) no-repeat 50% 50%;
}
.menu a>span.ellipsis:after{
    -webkit-mask: url(fa-pro-5.5.0/svgs/light/ellipsis-v.svg) no-repeat 50% 50% !important;
}


.icn {
  position: relative;
  display: inline-block;
  width:32px;
  height:32px;
  margin: 0 6px;
}
.icn:before { content: "\00a0"; }
.icnsmall {
    width:20px;
    height: 20px;
    vertical-align: middle;
    margin: 0;
}
.icn-1 {
    background-color:var(--color-1);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/landmark.svg) no-repeat 50% 50%;
}

.icn-2 {
    background-color:firebrick;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/car.svg) no-repeat 50% 50%;
}
.icn-3 {
    background-color:limegreen;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/business-time.svg) no-repeat 50% 50%;
}
.icn-4 {
    background-color:darkorchid;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/handshake.svg) no-repeat 50% 50%;
}
.icn-99 {
    background-color:orange;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/drum.svg) no-repeat 50% 50%;
}
.icn-81 {
    background-color:forestgreen;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/badge-check.svg) no-repeat 50% 50%;
}
.icn-82 {
    background-color:turquoise;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/keyboard.svg) no-repeat 50% 50%;
}
.icn-83 {
    background-color:mediumaquamarine;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/info.svg) no-repeat 50% 50%;
    max-height: 32px;
}
.icn-84 {
    background-color:gold;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/coins.svg) no-repeat 50% 50%;
}
.icn-85 {
    background-color:royalblue;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/signature.svg) no-repeat 50% 50%;
}
.icn-88 {
    background-color:hotpink;
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/paper-plane.svg) no-repeat 50% 50%;
}
.icn-bars {
    background-color:white;
    -webkit-mask: url(fa-pro-5.5.0/svgs/regular/bars.svg) no-repeat 50% 50%;
}
.icn-search {
    background-color:var(--mourjanC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/regular/search.svg) no-repeat 50% 50%;
}
.icn-globe {
    background-color: navy;
    -webkit-mask: url(fa-pro-5.5.0/svgs/regular/globe.svg) no-repeat 50% 50%;
}

.icn-ae { background: url(flags/4x3/ae.svg) center no-repeat; }
.icn-sa { background: url(flags/4x3/sa.svg) center no-repeat; }
.icn-kw { background: url(flags/4x3/kw.svg) center no-repeat; }
.icn-bh { background: url(flags/4x3/bh.svg) center no-repeat; }
.icn-om { background: url(flags/4x3/om.svg) center no-repeat; }
.icn-qa { background: url(flags/4x3/qa.svg) center no-repeat; }
.icn-iq { background: url(flags/4x3/iq.svg) center no-repeat; }
.icn-ye { background: url(flags/4x3/ye.svg) center no-repeat; }
.icn-lb { background: url(flags/4x3/lb.svg) center no-repeat; }
.icn-jo { background: url(flags/4x3/jo.svg) center no-repeat; }
.icn-sy { background: url(flags/4x3/sy.svg) center no-repeat; }
.icn-eg { background: url(flags/4x3/eg.svg) center no-repeat; }
.icn-ma { background: url(flags/4x3/ma.svg) center no-repeat; }
.icn-tn { background: url(flags/4x3/tn.svg) center no-repeat; }
.icn-dz { background: url(flags/4x3/dz.svg) center no-repeat; }
.icn-sd { background: url(flags/4x3/sd.svg) center no-repeat; }
.icn-ly { background: url(flags/4x3/ly.svg) center no-repeat; }


.topnav {overflow: hidden; background-color: var(--mourjanC);}

.topnav .search-container button {
  padding: 6px 10px;
  margin: 8px 0;
  background: #ddd;
  font-size: 17px;
  border: none;
  cursor: pointer;
}
.topnav .search-container {
    margin-left: 16px;
    margin-right: 16px;
}
.topnav .search-container button:hover { background: #ccc; }
.topnav a {
  float: left;
  display: block;
  color: #f2f2f2;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
  font-size: 17px;
}

.topnav a:hover { background-color: #ddd; color: black; }

.active { background-color: #4CAF50; color: white; }

.topnav .icon { display: none; }
.topnav .search-container input[type=text] {
    padding: 6px;
    margin: 8px 0;
    font-size: 17px;
    border: none;
}


.navbar .navbar-form .form-group .form-control, .navbar .navbar-form .form-control {
    border-color: inherit;
    color: inherit;
    padding: 0;
    margin: 0;
    height: 40px;
    font-size: 14px;
    line-height: 1.428571429;
}


.navbar .navbar-form .form-group {
    margin: 0;
    padding: 0;
    display: inline-block;
}

.navbar .navbar-form {
    border-top: none;
    box-shadow: none;
    margin: 4px 0 0;
}

.balign {
    padding-top: 120px;
}

.form-control, .form-group .form-control {
    border: 0;
    
    background-image: linear-gradient(#9c27b0, #9c27b0), linear-gradient(#D2D2D2, #D2D2D2);
    background-size: 0 2px, 100% 1px;
    background-repeat: no-repeat;
    background-position: center bottom, center calc(100% - 1px);
    background-color: transparent; /*rgba(238, 238, 238, 1);*/
    transition: background 0s ease-out;
    float: none;
    box-shadow: none;
    border-radius: 0;
    font-weight: 400;
}

.form-group input[type=text] {
    width: 240px;
}
.form-group input:focus::-webkit-input-placeholder { color:transparent; }
.form-group input[type=text]:focus {
    outline: none;
    background-image: linear-gradient(#9c27b0, #9c27b0), linear-gradient(#D2D2D2, #D2D2D2);
    background-size: 100% 2px, 100% 1px;
    box-shadow: none;
    transition-duration: 0.3s; 
}

.navbar .btn {
    margin-top: 0;
    margin-bottom: 0;
}

.btn.btn-just-icon, .navbar .navbar-nav > li > a.btn.btn-just-icon {
    padding: 11px 11px;
}
.btn.btn-round, .navbar .navbar-nav > li > a.btn.btn-round {
    border-radius: 30px;
}

.btn.btn-white, .btn.btn-white:focus, .btn.btn-white:hover, .navbar .navbar-nav > li > a.btn.btn-white, .navbar .navbar-nav > li > a.btn.btn-white:focus, .navbar .navbar-nav > li > a.btn.btn-white:hover {
    background-color: rgb(238, 238, 238);
    color: #999999;
}


@media (min-width: 992px) {
    .navbar-form {
        margin-top: 21px;
        margin-bottom: 21px;
        padding-left: 5px;
        padding-right: 5px;
    }
}

/*
body[dir="rtl"] .logo {
    -webkit-mask: url(logo-d-ar.svg) no-repeat 50% 50%; 
    -webkit-mask-size: contain;
    background-color: white;
}
*/

.ilogo {
    height: 90px;
    width: 90px;
    display: inline-block;
    background: url(mlogo-1.svg) center center no-repeat; 
    background-size: contain !important;
    margin: 0 8px;
}

.rlogo{display: none;}
.card {
    display: inline-block;
    position: relative;
    width: 100%;
    margin: 25px 0;
    box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.14);
    border-radius: 6px;
    color: rgba(0,0,0, 0.87);
    background: #fff;
}
.card-header {
    height: 68px;
    text-align: center;
    margin: -20px 15px 0 15px; 
    padding: 15px;
    box-shadow: 0 4px 20px 0px rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(255, 152, 0, 0.4);
    border-radius: inherit;
}

.card .card-header { z-index: 3; }

.card .card-footer {
    margin: 0 20px 10px;
    padding-top: 10px;
    border-top: 1px solid #eeeeee;
}

.card-stats .card-content {
    text-align: right;
    padding-top: 10px;
}
.card .card-content {
    padding: 15px 20px;
    position: relative;
}

.card .card-header.card-header-icon, .card-content .card-title {
    padding-bottom: 24px;
}
.card .card-title {
    margin-top: 0;
    margin-bottom: 3px;
    font-weight: 300;
}
.card-header [class*="icn-"] { background-color: white; }

.card ul {
    padding: 0;
    margin-top: 0px;
    margin-bottom: 0px;
}

.card li { border-bottom: 1px solid #eeeeee; }
.card li:last-child { border:0 }
.card li:hover, dd:hover, dt:hover { background-color:rgba(200, 200, 200, 0.2); color: var(--mourjanC);}
.card li>a { text-decoration: none; color: var(--midnightC); }
.card a>span { color: dimgray; font-size: small; font-weight: bolder; }

.card.test li{
    border-bottom: 0px;
    display:flex;
    justify-content:space-between;
    align-items: center;
    cursor:pointer
}
.card .icn{
    flex:0 0 auto;
    /*margin:0 10px 0 0;*/
}
.card.test span{
    flex:1;
    padding: 0 8px;
    border-bottom:1px solid #eeeeee;
}
.card.test li:last-child span{
    border:0
}
/*
body[dir="rtl"] .icn {
    margin:0 0 0 10px !important;
}
*/
.card .dl {
    columns: 1;
    margin-top: 0;
    display: inline-block;
}
.card dt {
    display:flex;
    align-items: center;
    cursor:pointer;
    -webkit-column-break-before: always;
    page-break-before: always;
    break-inside: avoid;    
}
.card dd {
    cursor:pointer;
    -webkit-margin-start: 28px;
    margin-inline-start:28px;
}
.card dt>span {   
    flex:1;
    padding: 0 8px;
}

.card [class*="col-"] {
    padding-top: 0;
    padding-left: 0;
}

@media screen and (max-width: 768px) {
    .topnav a:not(:first-child) {display: none;}
    .topnav a.icon { display: block; }
    .logo {
        -webkit-mask: url(logo-m.svg) no-repeat 50% 50% !important;  
        -webkit-mask-size: contain !important;
        background-color: white !important;
    }
    .topnav.responsive {position: relative;}
    .topnav.responsive .icon {
        position: absolute;
        right: 0;
        top: 0;
    }
    .topnav.responsive a {
        float: none;
        display: block;
        text-align: left;
    }
    .topnav input[type=text] {
        float: none;
        display: none;
        text-align: left;
        width: 100%;
        margin: 0;
        padding: 14px;
    }
    .topnav input[type=text] {
        border: 1px solid #ccc;
    }
}


@media only screen and (min-width: 769px) {
    .col-1 {width: 8.33%;}
    .col-2 {width: 16.66%;}
    .col-3 {width: 25%;}
    .col-4 {width: 33.33%;}
    .col-5 {width: 41.66%;}
    .col-6 {width: 50%;}
    .col-7 {width: 58.33%;}
    .col-8 {width: 66.66%;}
    .col-9 {width: 75%;}
    .col-10 {width: 83.33%;}
    .col-11 {width: 91.66%;}
    .col-12 {width: 100%;}
    [class*="col-"] {
        padding: 8px;
    }
    .header {
        min-height: 200px;
    }
    .ilogo{
        width: 200px;
        height: 200px;
        margin: 0 16px;
        /*background: url(logo-d.svg) center 10% no-repeat; */
    }
    .rlogo {
        width: 240px;
        height: 200px;
        display: inline-block;
        background: url(men.svg) center center no-repeat; 
        background-size: contain !important;
        margin: 0 8px;
    }
    /*
    body[dir="rtl"] .ilogo {
        width: 270px;
        margin: 0;
        background: url(logo-d-ar.svg) center center no-repeat; 
    }*/
}

.adv{}

@media only screen and (min-width:1920px) {
    body { margin: 0px auto; width: 1920px; display: block;}
    .adv {height: 600px;}
}
/*
@media only screen and (min-width: 1900px) {
    .col-4 {width: 25%;}
    .col-6 {width: 33.33%;}
    .col-8 {width: 75%;}
    .col-12 {width: 66.66%;}
}*/
</style>

<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>


<?php
echo '</head><body dir="', $router->isArabic() ? 'rtl':'ltr','">', "\n";
echo '<div class="wrapper">';
$post_label = $router->isArabic() ? "أضف اعلانك مجاناً" : "Post your ad for free";
$balance_label = $router->isArabic() ? "رصيد حسابي 872 ذهبية" : "My Balance is 872 coins";

$contact_label = $router->isArabic() ? "إتصل بنا" : "Contact us";
$about_label = $router->isArabic() ? "من نحن" : "About";
$terms_label = $router->isArabic() ? "شروط الاستخدام" : "Terms of use";
$privacy_label = $router->isArabic() ? "سياسة الخصوصية" : "Privacy policy";

$search_placeholder = $router->isArabic() ? "ما الذي تبحث عنه..." : "What are looking for...";
$regions_label = $router->isArabic() ? "البلدان والمناطق" : "Countries & regions";
?>
<div class="header">        
    <div class="topnav">
        <div class="float-left"><a href="#" style="padding: 0;"><i class="ilogo"></i></a></div>                
        
        <!--<div class="float-right"><i class="rlogo"></i></div>-->
        
        <div class="navbar float-right">
            <form class="navbar-form" action="/action_page.php">
                <div class="form-group">
                    <input class="form-control" type="text" placeholder="<?php echo $search_placeholder;?>">
                    <span></span>
                    <span></span>
                </div>
                <button class="btn btn-white btn-round btn-just-icon" type="submit"><i class="icn icnsmall icn-search"></i></button>
            </form>
    </div>
        <!--
        <div class="float-left">
            
            <a href="#home" class="active">Home</a>
            <a href="#news">News</a>
            <a href="#contact">Contact</a>
            <a href="#about">About</a>        
        </div>-->
        <!--        
        <a href="javascript:void(0);" class="icon float-right" onclick="myFunction()">
            <i class="icn icn-bars"></i>
        </a>-->
    </div>
    
    <!--<div class="banner"></div>-->
</div>


<div class="row">
        <?php
        
        $sections = [];
        foreach ($router->pageRoots as $id=>$root) {
            $count = $root['counter'];
            $link = $router->getURL($router->countryId, $router->cityId, $id);
            //echo '<li><a href="', $link,'"><i class="icn icn-', $id, '"></i>', $root['name'], '<span class="float-right">', number_format($count, 0), '</span></a></li>';
            $sections[$id] = $router->db->getSectionsData($router->countryId, $router->cityId, $id, $router->siteLanguage, true);
        }
        /*
        echo '</ul><ul>';
        echo '<li><a href="', '#','"><i class="icn icn-', $router->countries[$router->countryId]['uri'], '"></i>', 
            $router->countries[$router->countryId]['name'], '<span class="ellipsis float-right">', 
            number_format($router->countries[$router->countryId]['counter'], 0), '</span></a></li>';
        echo '</ul><ul>';
        echo '<li><i class="icn icn-82"></i>', $post_label, '</li>';
        echo '<li><i class="icn icn-84"></i>', $balance_label, '</li>';
        echo '</ul><ul>';
        echo '<li><i class="icn icn-88"></i>', $contact_label, '</li>';
        echo '<li><i class="icn icn-83"></i>', $about_label, '</li>';
        echo '</ul><ul>';
        echo '<li><i class="icn icn-85"></i>', $terms_label, '</li>';
        echo '<li><i class="icn icn-81"></i>', $privacy_label, '</li>';
        echo '</ul>', '</div>';
        */
        ?>        
    
    <!--<div class="col-12">--><?php
        $count = count($sections);
        $odd = ($count % 2)==1;
        $j=0;       
        foreach ($sections as $root_id => $items) {
            if ($odd) {
                $j++;
                error_log($j);
                echo '<div class="col-', ($j==$count)?'8':'4', '"><div class="card">';
            }
            else {
                echo '<div class="col-4">', '<div class="card">';
            }
            echo '<div class="card-header float-left" style="background-color:var(--color-',$root_id,');"><i class="icn icn-', $root_id, '"></i></div>';
            echo '<div class="card-content">';
            echo '<h4 class="card-title">', $router->pageRoots[$root_id]['name'],'</h4>';
            echo '<ul>';
            $i=0;
            foreach ($items as $section_id => $section) {
                if ($section['counter']==0) { break; }
                echo '<li><a href="#">', $section['name'], '<span class="float-right">', number_format($section['counter'],0), '</span></a></li>';
                $i++;
                if ($i>=10) { break; }
            }
            echo '</ul>';
            echo '</div>';
            //echo '<div class="card-footer"></div>';
            echo '</div></div>';
        }    
    ?>
    <!--</div>-->
</div>
<?php
echo '<div class="row">';

echo '<div class="col-4">';
echo '<div class="card test">', '<div class="card-content">';
echo '<ul>';
echo '<li><i class="icn icnsmall icn-82"></i><span>', $post_label, '</span></li>';
echo '<li><i class="icn icnsmall icn-84"></i><span>', $balance_label, '</span></li>';
echo '<li><i class="icn icnsmall icn-88"></i><span>', $contact_label, '</span></li>';
echo '<li><i class="icn icnsmall icn-83"></i><span>', $about_label, '</span></li>';
echo '<li><i class="icn icnsmall icn-85"></i><span>', $terms_label, '</span></li>';
echo '<li><i class="icn icnsmall icn-81"></i><span>', $privacy_label, '</span></li>';
echo '</ul></div>' /* card content */, '</div>'; // card

?>
<div class="adv">
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-2427907534283641"
     data-ad-slot="7030570808"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
</div>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<?php
echo '</div>'; // col-4

echo '<div class="col-8">';

$cc=['ae'=>null, 'sa'=>null, 'kw'=>null, 'bh'=>null, 'qa'=>null, 'om'=>null, 'ye'=>null, 
    'lb'=>null, 'jo'=>null, 'iq'=>null, 'sy'=>null, 
    'eg'=>null, 'ma'=>null, 'tn'=>null, 'dz'=>null, 'sd'=>null, 'ly'=>null];
foreach ($router->countries as $id => $cn) {
    if (!isset($cc[$cn['uri']])) { $cc['uri']=null; }
    if ($cc[$cn['uri']]==null) {
        $cc[$cn['uri']] = "<dt><i class=\"icn icnsmall icn-{$cn['uri']}\"></i><span>{$cn['name']}</span></dt>\n";
    }
    foreach ($cn['cities'] as $city) {
        $cc[$cn['uri']].= "<dd>{$city['name']}</dd>\n";
    }
}
echo '<div class=card>', '<div class="card-header float-left" style="background-color:navy;"><i class="icn icn-globe"></i></div>', '<div class=card-content><h4 class=card-title>', $regions_label, '</h4>';
echo '<dl class="dl col-4">', $cc['ae'], $cc['bh'], $cc['qa'], $cc['kw'], '</dl>', "\n"; 
echo '<dl class="dl col-4">', $cc['sa'], $cc['om'], $cc['ye'], $cc['iq'], '</dl>', "\n"; 
echo '<dl class="dl col-4">', $cc['lb'], $cc['jo'], $cc['eg'], $cc['ma'], $cc['tn'], $cc['dz'], $cc['sd'], $cc['ly'],  $cc['sy'], '</dl>', "\n"; 
echo '</div>'; // card-content

echo '</div>' /* card */, '</div>'; // col-8

echo '</div>'; // row
echo '</div>'; // wrapper

echo '</body></html>', "\n";





function pushImage($uri) {
    header("Link: <{$uri}>; rel=preload; as=image", false);
    echo "<img src=\"{$uri}\">", "\n";
}


function pushStyle($uri) {
    header("Link: <{$uri}>; rel=preload; as=style", false);
    echo '<link rel="stylesheet" href="', $uri, '">',"\n";
}


function pushFont($uri) {
    header("Link: <{$uri}>; rel=preload; as=stylesheet; type=font/woff2", false);
    //echo '<link rel="stylesheet" href="', $uri, '">',"\n";
}



function logo($fill="#ffffff") {
    echo '<svg class="m1logo" viewBox="0 0 1024 1024" width="90" height="90" version="1.1" aria-hidden="true" fill="', $fill, '">';
    echo '<path d="M417.964,823.989V403.437c0-28.733-13.061-45.711-45.712-45.711c-33.958,0-96.648,23.508-137.137,45.711v420.552H47.042
		V187.937h139.749l19.591,48.323c78.364-37.875,180.236-61.384,253.375-61.384c56.161,0,94.037,22.203,116.241,61.384
		c71.832-35.262,167.176-61.384,254.682-61.384c108.403,0,146.278,80.975,146.278,203.746v445.367H788.886V403.437
		c0-28.733-13.061-45.711-45.712-45.711c-33.958,0-96.649,23.508-137.139,45.711v420.552H417.964z"/>';
    echo '</svg>';

}