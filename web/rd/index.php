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
<link rel='preconnect' href='https://pagead2.googlesyndication.com' /><style><?php
    include '/var/www/mourjan/web/css/includes/main.css';
?>

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
<header>        
    <nav class="navbar">   
        <div class="float-left"><a href="#" style="padding: 0;"><i class="ilogo"></i></a></div>     
        
        <div class="float-right">                
            <ul class="nav float-right">
                <li><a href="#"><i class="icn icnsmall icn-bell"></i></a></li>
                <li><a href="#"><i class="icn icnsmall icn-user"></i></a></li>
            </ul>            
        </div>
    </nav>
</header>

<div class="row">
    <div class="col-12">
    <div class="search">
        <form class="" onsubmit="if(document.getElementById('q').value)return true;return false;" action="/">
            <input id="q" name="q" class="searchTerm" type="search" placeholder="<?php echo $search_placeholder;?>">
            <button class="searchButton float-right" type="submit"><i class="icn icnsmall icn-search"></i></button>
        </form>
    </div>
        </div>
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
            echo '</div></div>';
        }    
    ?>
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


