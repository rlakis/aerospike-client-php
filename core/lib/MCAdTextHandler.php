<?php

class AdTextFormatter {

    private $debug;
    private $inputText;
    public $text;


    function __construct($inText="") {
        $this->debug = FALSE;
        $this->inputText = $inText;
    }


    function setDebug($mode=TRUE) {
        $this->debug = $mode;
    }

    function setText($inText) {
        $this->inputText = $inText;
    }
    
    private function replace($pattern, $replacement, $comment='') {
        if ($this->debug) {
            echo $comment, "\n";
            if(is_array($pattern)){
                echo 'Pattern: '.  implode('|', $pattern),"\n";                
            }else{
                echo 'Pattern: '.$pattern,"\n";                
            }
            if(is_array($replacement)){
                echo 'Replacement: '.  implode('|', $replacement),"\n";                
            }else{
                echo 'Replacement: '.$replacement,"\n";               
            }
            
            echo $this->text, "\n";
            echo '----------------->', "\n";
        }
        $this->text = preg_replace($pattern, $replacement, $this->text);
        if ($this->debug) {
            echo $this->text, "\n\n";
        }
    }


    function format() {
        $matches = preg_split('/\x{200b}/u', $this->inputText, -1, PREG_SPLIT_NO_EMPTY);
        
        //preg_match('/(.*)(\x{200b})(.*)/u', $this->inputText, $matches);
   
        $mc = count($matches);
        
        //error_log('Separators: '.$mc);
        
        $this->text = $mc>1 ? $matches[0] : $this->inputText;
        
        //error_log(var_export($matches, TRUE));
        

        $this->replace('/\x{0660}/u', '0');
        $this->replace('/\x{0661}/u', '1');
        $this->replace('/\x{0662}/u', '2');
        $this->replace('/\x{0663}/u', '3');
        $this->replace('/\x{0664}/u', '4');
        $this->replace('/\x{0665}/u', '5');
        $this->replace('/\x{0666}/u', '6');
        $this->replace('/\x{0667}/u', '7');
        $this->replace('/\x{0668}/u', '8');
        $this->replace('/\x{0669}/u', '9');

        
        $this->replace('/\x{0640}+/u', 'ـ', 'Remove Dupplicate Arabic Maddah');
        $this->replace('/([\x{0600}-\x{0699}])\x{0640}([\x{0600}-\x{0699}])/u', '$1$2', 'Remove Arabic Maddah');
        $this->replace('/(\x{0629})([\x{0600}-\x{0699}]|[a-zA-Z0-9])/u', '$1 $2', 'Taa Marboutah followed by character');
        $this->replace('/(\x{0627}|\x{0622}|\x{0623}|\x{0625})(\x{0627}|\x{0622}|\x{0623}|\x{0625}+)/u', '$1', 'Repeated Alef');
        $this->replace('/([a-zA-Z0-9])([\x{0600}-\x{0699}])/u', '$1 $2', 'Concatenated Latin Arabic');
        $this->replace('/([\x{0600}-\x{0699}])([a-zA-Z]|\$|الجديد)/u', '$1 $2', 'Concatenaed Arabic Latin');
        $this->replace('/([\x{0600}-\x{0699}]{2,})([0-9])/u', '$1 $2', 'Concatenaed Arabic Latin');
        $this->replace('/(\d+)\s+(م|م٢|sq m|m|m2)(\s+)/u', '$1$2 $3', 'Space/Area');
        $this->replace('/(\d+)\s+(متر\s+مربع|متر|م2)/u', '$1م', 'Space Arabic Meter');
        $this->replace('/(\d+)م\s+2(\s+)/u', '$1م');
        $this->replace("/(\b\d+)(\s+|)ممربع\b/u", '$1م');
        $this->replace("/(\d+)\s+(\م\b)/u", "$1$2");
        $this->replace('/(\d+)(\s+|)(meters|meter|m2|sqm|sq\.m)/u', '$1m', 'Space Latin Meter');
        $this->replace('/(\d+)\s+\%/u', '$1%', 'Concatenate percentage to number');
        $this->replace('/\s+،/u', '،', 'Arabic comma space before');
        $this->replace('/\s+,/u', ',', 'Latin comma space before');
        $this->replace('/([a-zA-Z]),([a-zA-Z])/u', '$1, $2');
        $this->replace('/([a-zA-Z])\s+\:/u', '$1:');
        $this->replace('/\s+(و)\s+([\x{0600}-\x{0699}]{2,})/u', ' $1$2');
        $this->replace('/\s+(كما)(يوجد)/u', ' $1 $2');

        $this->replace(
            array(
                '/\b(?:ال|و|وال|)خدم\b/u',
                '/\b(?:ال|و|وال|)(?:خادمة\b|خدامة\b|خدّامة)\b/u',
                '/\b(?:ال|و|وال|)(?:خادم\b|خدام\b|خدّام)\b/u',
                '/\b(?:ال|و|وال|)(?:خادمات|خدّامات)\b/u'
            ), 
            array(
                'عمالة منزلية',
                'عاملة منزلية',
                'عامل منزلي',
                'عاملات منزليات'
            )
        );
        $this->replace('/للأيجار|للجار/u', 'للايجار');
        $this->replace('/([\x{0600}-\x{0699}]{3,})(شقه|شقة|عمار|بناي)/u', '$1 $2', '');

        $this->replace('/\s+(سعر|كبير|للايجار)([\x{0600}-\x{0699}]{2,})/u', ' $1 $2');
        
        $this->replace('/(ﻻ)/u', 'لا', 'Lam Alef normalization');
        $this->replace('/\s+(لتواصل|لاتصال|لإتصال|لايجار|لبيع)/u', ' ل$1', 'The lilitisal');
        $this->replace('/(للايجار|للإيجار|ايجار|للبيع|مطلوب)(ب|[\x{0600}-\x{0699}]{2,})/u', '$1 $2');
        $this->replace('/(امكا|إمكا)\s+(ن)/u', '$1$2');

        $this->replace('/\s+(ابو|أبو|عبد|و)\s+([\x{0600}-\x{0699}])/u', ' $1$2');
        
        $this->replace('/\s+(\$)(\s+|)(\d{1,3}(,\d{1,3})*)/u', ' $1$3', 'USD Price');
        $this->replace('/\s+(\d{1,3}(,\d{1,3})*)(\s+|)(\$)/', ' $4$1', 'USD Price');
        $this->replace('/\s+(\d+)(\s+|)(\$)/', ' $3$1', 'USD Price');
        $this->replace('/\s+(\$)(\s+|)(\d+)/', ' $1$3', 'USD Price');
        $this->replace('/\s+(QR|qar)(\s+|)(\d+)/', ' QR $3');
        
        $this->replace('/\s+/u', ' ', '');
        $this->replace('/\.$/', '', 'Remove end period');
        $this->replace('/^\d{1,2}\.(\s+|)/', '');
        
        $this->replace('/\s+(?:(?:\/(?:\s|))موبايل|هاتف|واتساب).*$/u', '');
        
        $this->text = trim($this->text);
        //error_log($this->text);
        $this->replace('/\s+(وشكرا|شكرا|Thanks|للاتصال\: email​|\/ موبايل \+ واتساب)$/u', '');

        if ($mc>1) {
            $unicodeChar = '\u200b';
            $this->text .=  json_decode('"'.$unicodeChar.'"').$matches[$mc-1];
        }


    }

}


if (php_sapi_name()=='cli') {
    $data = [
    "شقق تمليك ، بالقرب من الولو hyper ,المساحة : 147متر ، 65000 BD قابلة للتفاوض​ / موبايل + واتساب: +97334492456 وشكرا.",
    "Apartments for sale ,near Alolo Hyper Area : 147 meters Price : 65000 negotiable Tel : 34492456​ / Mobile + Whatsapp: +97334492456",
    "للبيع بنايه في البسيتين مكونه من 20 شقه المساحه 278م2 الدخل 2450 السعر 280 الف دينار",
    "M.S.Hهي خدمة تغليف ونقل الاثاث 66930060 باستخدام مواد عاليه الجوده والمتانة من الموقع.فقط عليكم اختيارالموعد المناسب لكم ليقوم المتخصصون بيعملهم في فك ونقل وتغليف جميع انواع الاثاث ",
    "ارض تجارية تقع شرق خط السريع ( طريق الحرمين ) مقابل قاعة الرفيدي، تبلغ مساحتها: 2880 متر مربع. ",
    "ﺍﻟﺴﻼﻡ ﻋﻠﻴﻜﻢ ﻭﺭﺣﻤﻪ ﺍﻟﻠﻪ ﻭﺑﺮﻛﺎﺗﻪ ﺍﻧﺎ ﻋﺒﺪ ﺍﻟﺮﻭﻭﻑ ﻣﺤﻤﺪ ﺍﻟﺤﺎﺝ ﺍﻟﻌﺒﺪﻟﻲ ﻣﻦ ﺍﻟﻴﻤﻦ ﺻﻨﻌﺎ ﻟﺪﻳﺎ ﺧﺒﺮﻩ ﻓﻲ ﺍﻟﻤﺒﻴﻌﺎﺕ ﻭﺍﻟﺘﺴﻮﻳﻖ ﻣﻨﺬﻭ 11 ﺳﻨﻪ ﻓﻦ ﺍﻟﺘﻌﺎﻣﻞ ﻓﻲ ﺍﻟﺘﺴﻮﻳﻖ ﻭﺍﺑﺘﻜﺎﺭ ﺍﺳﺎﻟﻴﺐ ﺟﺪﻳﺪﻩ ﻓﻲ ﺗﺘﻄﻮﻳﺮ ﺍﻟﻌﻤﻞ ﺍﻟﻤﻮﺳﺴﻲ ﺧﺒﺮ ﻓﻲ ﺑﻴﻊ ﻭﺷﺮﺍ ﺍﻛﺴﺴﻮﺍﺭﺍﺕ ﺍﻟﺠﻮﺍﻝ ﺧﺒﺮﻩ ﻃﻮﻳﻠﻪ ﻭﺍﻟﻤﻮﺍﺩ ﺍﻟﻐﺬﺍﻳﻴﻪ ﻭﺍﻟﺪﻋﺎﻳﻪ ﻭﺍﻻ ﻋﻼﻥ ﺍﺳﺘﺨﺪﺍﻡ ﺍﻟﻔﻮﺍﺗﻴﺮ ﻭﺟﻤﻊ ﺍﻟﺤﺴﺎﺑﺎﺕ ﺑﺪﻭﻥ ﺍﻟﺮﺟﻮﻉ ﺍﻻ ﻟﻪ ﺍﻟﺤﺎﺳﺒﻪ ﻋﻠﻲ ﺍﻱ ﺷﺮﻛﻪ ﺗﺤﺘﺎﺝ ﺍﻻ ﻣﻨﺪﻭﺏ ﻧﺎﺟﺢ ﺗﻘﺪﻡ ﻛﺎﻓﻪ ﻃﻠﺒﺎﺗﻲ ﻭﺭﻭﺍﺗﺐ ﻭﻋﻤﻮﻻت ﻭﻋﻨﺪﻱ",
    "لﻻيجار استديو كبير تكيف اسبلت مطبخ كبير تكيف اسبلت حمام 2500 شامل دوار الديوان"
    ];

    $i = 6;

    $formatter = new AdTextFormatter($data[$i]);
    $formatter->setDebug();
    $formatter->format();
}

?>