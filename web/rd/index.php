<?php
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

<style type="text/css">
@charset "UTF-8"; 
@font-face {
  font-family: system;
  font-style: normal;
  font-weight: 300;
  src: local(".SFNSText-Light"), local(".HelveticaNeueDeskInterface-Light"), 
      local(".LucidaGrandeUI"), local("Ubuntu Light"), local("Segoe UI Light"), 
      local("Roboto-Light"), local("DroidSans"), local("Tahoma");
}

body{font-family: -apple-system, "system";font-style: normal;font-size:1em;background-color: whitesmoke;}
.float-left{ float: left; }
.float-right{ float: right; }
body[dir="rtl"] .float-left{ float: right; }
body[dir="rtl"] .float-right{ float: left; }
            
:root {
    --mourjanC:rgba(10,61,98,1);
    --midnight:rgba(44,62,80,1);
}
*{ 
    box-sizing: border-box; 
}

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
    float: left;
    padding: 8px;
}
body[dir="rtl"] [class*="col-"] {
    float: right;
}
.row::after {
    content: "";
    clear: both;
    display: table;
}
.header {
    background-color: var(--mourjanC);
    color: #ffffff;
    padding: 10px 0 10px 0;
    text-align: center;
    height: 60px;
}
.header>h1{
    margin-block-start: 0.1em;
    margin-block-end: 0.1em;
    -webkit-margin-before:0.1em;
    -webkit-margin-after:0.1em;
    font-size: 1.8em;
}
.footer {

}
.menu ul {
    list-style-type: none;
    list-style-position: inside;
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
.menu li:before {}
.menu li>i { margin: 0 4px; max-width: 32px; }

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
  width: 1.33333333em;
  max-height: 40px;
  margin: 0 6px;
}
.icn:before {
  content: "\00a0";
}
.icn-1 {
    background-color:steelblue;
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

.icn-ae {
    background-repeat: no-repeat;
    background-position: center;
    background-image: url(flags/4x3/ae.svg);
}



.topnav {
  overflow: hidden;
  background-color: var(--mourjanC);
}

.topnav a {
  float: left;
  display: block;
  color: #f2f2f2;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
  font-size: 17px;
}

.topnav a:hover {
  background-color: #ddd;
  color: black;
}

.active {
  background-color: #4CAF50;
  color: white;
}

.topnav .icon {
  display: none;
}

@media screen and (max-width: 600px) {
  .topnav a:not(:first-child) {display: none;}
  .topnav a.icon {    
    display: block;
  }
}

@media screen and (max-width: 600px) {
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
}

@media only screen and (max-width: 768px) {
    [class*="col-"] {
        width: 100%;
        padding: 0;
    }
    body {
        margin: 0;
        background-color: rgb(238, 238, 238);
    }
}

</style>

<?php
echo '</head><body dir="', $router->isArabic() ? 'rtl':'ltr','">', "\n";

$post_label = $router->isArabic() ? "أضف اعلانك مجاناً" : "Post your ad for free";
$balance_label = $router->isArabic() ? "رصيد حسابي 872 ذهبية" : "My Balance is 872 coins";
$contact_label = $router->isArabic() ? "إتصل بنا" : "Contact us";
$about_label = $router->isArabic() ? "من نحن" : "About";
$terms_label = $router->isArabic() ? "شروط الاستخدام" : "Terms of use";
$privacy_label = $router->isArabic() ? "سياسة الخصوصية" : "Privacy policy";
?>
<div class="header">
    
    
    <div class="topnav">
        <div class="float-left">
        <a href="#home" class="active">Home</a>
        <a href="#news">News</a>
        <a href="#contact">Contact</a>
        <a href="#about">About</a>
        </div>
        
        <a href="javascript:void(0);" class="icon float-right" onclick="myFunction()">
            <i class="icn icn-bars"></i>
        </a>
    </div>
</div>


<div class="row">
    <div class="col-4 menu">
        <ul>
        <?php
        foreach ($router->pageRoots as $id=>$root) {
            $count = $root['counter'];
            $link = $router->getURL($router->countryId, $router->cityId, $id);
            echo '<li><a href="', $link,'"><i class="icn icn-', $id, '"></i>', $root['name'], '<span class="float-right">', number_format($count, 0), '</span></a></li>';
        }
        ?>
        </ul>
        <ul>
        <?php
            echo '<li><a href="', '#','"><i class="icn icn-', $router->countries[$router->countryId]['uri'], '"></i>', 
                $router->countries[$router->countryId]['name'], '<span class="ellipsis float-right">', 
                number_format($router->countries[$router->countryId]['counter'], 0), '</span></a></li>';
        ?>           
        </ul>
        <ul>
            <li><i class="icn icn-82"></i><?php echo $post_label;?></li>
            <li><i class="icn icn-84"></i><?php echo $balance_label;?></li>
        </ul>
        <ul>
            <li><i class="icn icn-88"></i><?php echo $contact_label;?></li>
            <li><i class="icn icn-83"></i><?php echo $about_label;?></li>
        </ul>
        <ul>
            <li><i class="icn icn-85"></i><?php echo $terms_label;?></li>
            <li><i class="icn icn-81"></i><?php echo $privacy_label;?></li>
        </ul>
    </div>
    
    <div class="col-8">
        <h2>The City</h2>
        <p>Chania is the capital of the Chania region on the island of Crete. The city can be divided in two parts, the old town and the modern city.</p>
        <p>Resize the browser window to see how the content respond to the resizing.</p>
    </div>
</div>
<?php
        //var_dump($router->countries[$router->countryId]);
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

