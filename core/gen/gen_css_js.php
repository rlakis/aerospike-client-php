<?php
$jsReadPath='/home/www/mourjan/web/js/1.0.0/';
$jsMobileReadPath='/home/www/mourjan/web/js/2.0.0/';
$jsWritePath='/home/www/mourjan/web/js/release/';

function processContent($content){
    $content=preg_replace("/([^:\"\'])\/\/.*\n/", '$1', $content);
    $content=preg_replace("/[\n\t\r]/", '', $content);                
    $content=preg_replace('/\s+/', ' ', $content);
    $content=preg_replace('/(;|=|,|if|\(|\)|\{|\}|\?|\||\&|:)\s/', '$1', $content);
    $content=preg_replace('/\s(;|=|,|\(|\)|\{|\}|\?|\||\&|:)/', '$1', $content);
    $content=preg_replace('/;\}/', '}', $content);
    $content=preg_replace('/\/\*.*?\*\//', '', $content); 
    return $content;
}

function compileCode($content){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Expect:',
        'Content-type: application/x-www-form-urlencoded',
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
    curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
    //curl_setopt($ch, CURLOPT_HEADER, true); // for debugging response header
    //curl_setopt($ch, CURLINFO_HEADER_OUT, true); // for debugging request header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true);
    // settings
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        'output_format=json'
        .'&output_info=compiled_code'
        .'&output_info=warnings'
        .'&output_info=errors'
        .'&output_info=statistics'
        .'&compilation_level=SIMPLE_OPTIMIZATIONS'
        .'&warning_level=verbose'
        //.'&output_file_name=default.js'
        //.'&code_url='
        .'&js_code=' . urlencode($content)
    );
    curl_setopt($ch, CURLOPT_URL, 'http://closure-compiler.appspot.com/compile');
    $response = curl_exec($ch);
    //$response = curl_getinfo($ch, CURLINFO_HEADER_OUT) . $response; // for debugging request header
    $response = json_decode($response, true);
    $key = array_keys($response);
    //var_dump($key);
    if(isset($response['compiledCode']))
        return $response['compiledCode'];
    else {
        var_dump($response['serverErrors']);
        return $content;
    }
}

if ($handle = opendir($jsReadPath)) {
    echo 'Generating JS release files', "\n";
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if (preg_match('/\.js$/', $entry)){
                echo "\tProcessing {$entry}\n";
                $content=file_get_contents($jsReadPath.$entry);               
                $content=  processContent($content);
                //$content=compileCode($content);
                file_put_contents($jsWritePath.$entry, $content);
            }
        }
    }
    closedir($handle);
    echo 'JS files generated', "\n\n";
}

if ($handle = opendir($jsMobileReadPath)) {
    echo 'Generating Mobile JS release files', "\n";
    $genContent = file_get_contents($jsMobileReadPath.'m_gen.js');  
    $genContent=  processContent($genContent);
    
    $files = array(
        array('m_srh.js','m_search.js'),
        array('m_ads.js','m_fullads.js'),
        array('m_acc.js','m_account.js'),
        array('m_cnt.js','m_contact.js'),
        array('m_post.js','m_fullpost.js'),
        array('m_gen.js','m_gen.js'),
        array('m_pwd.js','m_password.js'),
    );
    foreach ($files as $pair){
        $entry = $pair[0];
        echo "\tProcessing {$entry}\n";
        $content=file_get_contents($jsMobileReadPath.$entry);               
        $content=  processContent($content);
        /*if($entry!='m_gen.js'){
            $content=$genContent.$content;
        }*/
        //$content=compileCode($content);
        file_put_contents($jsWritePath.$pair[0], $content);
    }
    /*while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if (preg_match('/\.js$/', $entry)){
                echo "\tProcessing {$entry}\n";
                $content=file_get_contents($jsMobileReadPath.$entry);               
                $content=  processContent($content);
                if($entry!='m_gen.js'){
                    $content=$genContent.$content;
                }
                file_put_contents($jsWritePath.$entry, $content);
            }
        }
    }*/
    closedir($handle);
    echo 'Mobile JS files generated', "\n\n";
}
//exit(0);
echo 'Generating CSS release files', "\n";
$cssReadPath='/home/www/mourjan/web/css/5.4.3/';
$cssMobilePath='/home/www/mourjan/web/css/1.0.2/';
$cssWritePath='/home/www/mourjan/web/css/release/';
$cssMobileWritePath='/home/www/mourjan/web/css/release-mobile/';


$redis = new Redis();
$redis->connect('p1.mourjan.com', 6379, 1, NULL, 100); 

if ($handle = opendir($cssReadPath)) {
    
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if (preg_match('/\.css$/', $entry)){
                if (!preg_match('/_1\.css$/', $entry)) {
                    echo "\tProcessing {$entry}";
                    $content=file_get_contents($cssReadPath.$entry);             
                    $content=preg_replace("/[\n\t\r]/", '', $content);               
                    $content=preg_replace('/\s+/', ' ', $content);
                    $content=preg_replace('/(;|:|,|\(|\{|\})\s/', '$1', $content);
                    $content=preg_replace('/\s(;|:|,|\)|\{|\})/', '$1', $content);
                    $content=preg_replace('/;\}/', '}', $content);
                    $content=preg_replace('/\/\*.*?\*\//', '', $content);
                    file_put_contents($cssWritePath.$entry, $content);
                    
                    
                    if($redis->set('v1:'.$entry, $content)){
                        echo ' cached to redis';
                    }else{
                        echo ' failed to cache to redis';
                    }
                    echo "\n";
                }
            }elseif(is_dir($cssReadPath.$entry) && $entry!='m') {
                $dest=$cssWritePath.$entry;
                if($dest!='' && $dest!='/' && is_dir($dest)){
                    system('rm -rf '.$dest);
                }
                echo "\t\tCopying Images Directory {$entry}\n";
                system('cp -rf '.$cssReadPath.$entry.' '.$cssWritePath.$entry);
            }
        }
    }    
    closedir($handle);
    echo 'CSS files generated', "\n\n";
}
if ($handle = opendir($cssMobilePath)) {    
    
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if (preg_match('/\.css$/', $entry)){
                if (!preg_match('/_1\.css$/', $entry)) {
                    echo "\tProcessing {$entry}";
                    $content=file_get_contents($cssMobilePath.$entry);             
                    $content=preg_replace("/[\n\t\r]/", '', $content);               
                    $content=preg_replace('/\s+/', ' ', $content);
                    $content=preg_replace('/(;|:|,|\(|\{|\})\s/', '$1', $content);
                    $content=preg_replace('/\s(;|:|,|\)|\{|\})/', '$1', $content);
                    $content=preg_replace('/;\}/', '}', $content);
                    $content=preg_replace('/\/\*.*?\*\//', '', $content);
                    file_put_contents($cssMobileWritePath.$entry, $content);
                    
                    
                    if($redis->set('v1:m'.$entry, $content)){
                        echo ' cached to redis';
                    }else{
                        echo ' failed to cache to redis';
                    }
                    echo "\n";
                }
            }elseif(is_dir($cssMobilePath.$entry) && $entry!='m') {
                $dest=$cssMobileWritePath.$entry;
                if($dest!='' && $dest!='/' && is_dir($dest)){
                    system('rm -rf '.$dest);
                }
                echo "\t\tCopying Images Directory {$entry}\n";
                system('cp -rf '.$cssMobilePath.$entry.' '.$cssMobileWritePath.$entry);
            }
        }
    }    
    closedir($handle);
    echo 'CSS files generated', "\n\n";
}
$redis->close();
?>
