<?php
$title='Mourjan Classifieds';
echo '<!DOCTYPE html><HTML lang="en"><head>',"\n";
echo '<meta charset="utf-8">', "\n";
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">', "\n";
echo '<title>', $title, '</title>', "\n";
if(0){
pushStyle("fa-pro-5.5.0/css/fontawesome.css");
pushStyle("fa-pro-5.5.0/css/solid.css");
}
pushStyle("flags/flags.css");
//pushFont("fa-pro-5.5.0/webfonts/fa-solid-900.woff2");
?>
<style type="text/css"></style>

<style type="text/css">
@font-face {
  font-family: system;
  font-style: normal;
  font-weight: 300;
  src: local(".SFNSText-Light"), local(".HelveticaNeueDeskInterface-Light"), 
      local(".LucidaGrandeUI"), local("Ubuntu Light"), local("Segoe UI Light"), 
      local("Roboto-Light"), local("DroidSans"), local("Tahoma");
}

body{font-family: -apple-system, "system";font-style: normal;font-size:1em;background-color: #efeff4;
}
:root {
    --mourjanC:rgba(10,61,98,1);
    --goldC:rgba(252,194,0,1);
    --turqoiseFC:rgba(26,188,156,1);
    --greenFC:rgba(39,174,96, 1);
    --blueFC:rgba(41, 128, 185, 1);
    --midnightFC:rgba(44,62,80,1);
    --purpleFC:rgba(142,68,173, 1);
    --orangeFC:rgba(243,156,18, 1);
    --redFC:rgba(192, 57, 43, 1);
    --silverFC:rgba(189,195,199,1);
    --grayFC:rgba(127,140,141,1);
    --yellowFC:rgba(255,255,101.1);
    --waterlemonFC:rgba(255,107,129,1);	
}
*{ box-sizing: border-box; }
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
    padding: 0px;
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
    background-color: #fff;
    color: var(--midnightFC);
    font-size: 1.1em;
    line-height: 40px;
}
.menu li:hover { background-color:rgba(200, 200, 200, 0.2); }
.menu li:before {}
.menu li>i {
    margin-right: 8px;
    font-size: 1.2em;
    //text-align: center;
    max-width: 32px;
}

.menu li>span {
    float: right;
    font-size: 0.8em;
    font-weight: bolder;
    color: var(--grayFC);
    
}
.menu li>span:after {
    content: "\00a0";
    padding-right: 4px;
    margin-left: 8px;    
    background-position: right center;
    background-size: 1em;
    background-repeat: no-repeat;
    position: relative;
    display: inline-block;
    height: 100%;
    max-width: 32px;
    background-color: #8E8E93;
    -webkit-mask: url(fa-pro-5.5.0/svgs/light/angle-right.svg) no-repeat 50% 50%;
}

.icn-1 {
    background-color:var(--blueFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/landmark.svg) no-repeat 50% 50%;
}

.icn-2 {
    background-color:var(--redFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/car.svg) no-repeat 50% 50%;
}
.icn-3 {
    background-color:var(--greenFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/business-time.svg) no-repeat 50% 50%;
}
.icn-4 {
    background-color:var(--purpleFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/handshake.svg) no-repeat 50% 50%;
}
.icn-99 {
    background-color:var(--orangeFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/drum.svg) no-repeat 50% 50%;
}
.icn-81 {
    background-color:var(--greenFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/badge-check.svg) no-repeat 50% 50%;
}
.icn-82 {
    background-color:var(--turqoiseFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/keyboard.svg) no-repeat 50% 50%;
}
.icn-83 {
    background-color:var(--turqoiseFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/info.svg) no-repeat 50% 50%;
    max-height: 32px;
}
.icn-84 {
    background-color:var(--goldC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/coins.svg) no-repeat 50% 50%;
}
.icn-85 {
    background-color:var(--blueFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/signature.svg) no-repeat 50% 50%;
}
.icn-88 {
    background-color:var(--waterlemonFC);
    -webkit-mask: url(fa-pro-5.5.0/svgs/solid/paper-plane.svg) no-repeat 50% 50%;
}

.icon {
    background-size: contain;
    background-position: 50%;
    background-repeat: no-repeat;
    position: relative;
    display: inline-block;
    width: 1.33333333em;
    line-height: 1em;
    font-size: 1.5em;
}
.ri-1:before{content:"\f66f";color:var(--blueFC,blue);}
.ri-2:before{content:"\f1b9";color:var(--redFC,red);}
.ri-3:before{content:"\f64a";color:var(--greenFC,green);}
.ri-4:before{content:"\f2b5";color:var(--purpleFC);}
.ri-99:before{content:"\f569";color:var(--orangeFC);}
.ri-81:before{content:"\f336";color:var(--greenFC);}
.ri-82:before{content:"\f641";color:var(--turqoiseFC);}
.ri-83:before{content:"\f129";color:var(--turqoiseFC);}
.ri-84:before{content:"\f51e";color:var(--goldC);}
.ri-85:before{content:"\f56c";color:var(--blueFC);}
.ri-88:before{content:"\f1d8";color:var(--waterlemonFC);}

@media only screen and (max-width: 768px) {
    [class*="col-"] {
        width: 100%;
    }
    body {
        margin: 0;
    }
}

</style>
<?php
echo '</head><body>', "\n";
?>
<div class="header"><h1>Mourjan Emirates</h1></div>

<div class="row">
    <div class="col-4 menu">
        <ul>
            <li><i class="flag-icon icn-1"></i>Real estate<SPAN>51,033</SPAN></li>
            <li><i class="flag-icon icn-2"></i>Cars<span>1,750</span></li>
            <li><i class="flag-icon icn-3"></i>Jobs<SPAN>25,376</SPAN></li>
            <li><i class="flag-icon icn-4"></i>Services<span>8,874</span></li>
            <li><i class="flag-icon icn-99"></i>Miscellanious<span>3,128</span></li>
        </ul>
        <ul>
            <li><i class="flag-icon flag-icon-ae"></i>Emirates<span>89,888</span></li>
        </ul>
        <ul>
            <li><i class="flag-icon icn-82"></i>Place your ad for free</li>
            <li><i class="flag-icon icn-84"></i>My Balance is 872 coins</li>
        </ul>
        <ul>
            <li><i class="flag-icon icn-88"></i>Contact us</li>
            <li><i class="flag-icon icn-83"></i>About us</li>
        </ul>
        <ul>
            <li><i class="flag-icon icn-85"></i>Terms of use</li>
            <li><i class="flag-icon icn-81"></i>Privacy policy</li>
        </ul>
    </div>
    
    <div class="col-8">
        <h2>The City</h2>
        <p>Chania is the capital of the Chania region on the island of Crete. The city can be divided in two parts, the old town and the modern city.</p>
        <p>Resize the browser window to see how the content respond to the resizing.</p>
    </div>
</div>
<?php
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

