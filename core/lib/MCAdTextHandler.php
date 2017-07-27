<?php
 
require 'deps/autoload.php';
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberUtil;

class AdTextFormatter {

    private $debug;
    private $inputText;
    public $text;
    
    public $rootId;
    public $sectionId;
    public $purposeId;
    
    private $countryCode;
    private $cities;
    private $publicationId;
    
    private $phoneUtil;
    
    public $attrs;
    
    function __construct($inText='', $publicationId=-1, $countryXX='', $pubTo=[]) {
        $this->debug = FALSE;
        $this->inputText = $inText;
        $this->publicationId = $publicationId;
        $this->countryCode = trim($countryXX);
        $this->cities = $pubTo;
        if (empty($this->countryCode) && is_array($this->cities) && count($this->cities)>0) {
            $this->countryCode = MCPhoneNumber::$countryOf[$this->cities[0]];
        }
        $this->phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
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

        //if ($this->countryCode=='LB' && $this->rootId==1) {
        //    syslog(LOG_INFO, json_encode($pattern));
        //}
        
        $this->text = preg_replace($pattern, $replacement, $this->text);

        if (preg_last_error()!==PREG_NO_ERROR) {
            if(is_array($pattern)){
                $pattern=  implode('|', $pattern);
            }
            if(is_array($replacement)){
                $replacement =  implode('|', $replacement);
            }
            $this->logPregError("Pattern {$pattern} / Replacement {$replacement}");
        }
        if ($this->debug) {
            echo $this->text, "\n\n";
        }
    }


    function logPregError($toAppend) {
        $msg = '';

        switch (preg_last_error()) {
            case PREG_NO_ERROR:
                return;
            case PREG_INTERNAL_ERROR:
                $msg = 'PREG_INTERNAL_ERROR';
                break;
            case PREG_BACKTRACK_LIMIT_ERROR:
                $msg = 'PREG_BACKTRACK_LIMIT_ERROR';
                break;
            case PREG_RECURSION_LIMIT_ERROR:
                $msg = 'PREG_RECURSION_LIMIT_ERROR';
                break;
            case PREG_BAD_UTF8_ERROR:
                $msg = 'PREG_BAD_UTF8_ERROR';
                break;
            case PREG_BAD_UTF8_OFFSET_ERROR:
                $msg = 'PREG_BAD_UTF8_OFFSET_ERROR';
                break;
            default:
                $msg = 'UNKNOWN PREG ERROR';
                break;
        }

        if ($msg) {
            $msg.=$toAppend.' ---> ['.$this->text.']';
            syslog(LOG_INFO, $msg);
        }
    }


    
    function format($mourjanAD=0) {
        $this->attrs = [ 'phones'=>['n'=>[], 't'=>[]] ];
        if (empty($this->inputText))
        {
            $this->text='';
            return;
        }
        $matches = preg_split('/\x{200b}/u', $this->inputText, -1, PREG_SPLIT_NO_EMPTY);        
   
        $mc = count($matches);
        
        $this->text = $mc>1 ? trim($matches[0]) : trim($this->inputText);
        
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

        $this->replace('/^\s*\d+\s*=\s*/', '');
        
        $this->replace('/\x{0640}+/u', 'ـ', 'Remove Dupplicate Arabic Maddah');
        $this->replace('/([\x{0600}-\x{0699}])\x{0640}([\x{0600}-\x{0699}])/u', '$1$2', 'Remove Arabic Maddah');

        //$this->replace('/([\x{0600}-\x{0699}]\.)(\d+)/u', '$1 $2');
        
        $this->replace('/^(ترميز العقار:|الترميز|ref)(\s+)(\d+ - \d{2,})\b(.*)/iu', '$4 - $1 $3', 'User reference');
        $this->replace('/^([a-z]{1,2}\d{2,})(:|\-|)(.*)/iu','$3 - $1', 'User reference');

        //$this->replace('/^(mr\d{2,})\b(.*)/iu', '$2 - $1', 'User reference');
        $this->replace('/^(listing number|كود الوحدة|كود|متسلسل)(\s+|:\s+|)(\d+)(\.|)(.*)/iu', '$5 - $1$2$3', 'User reference');
        $this->replace('/^(ref(\.|))(\s)(\d+)\s(.*)/i', '$5 - $1 $4');
        $this->replace('/^(رقم العرض)(\s)(\d+)(\))(.*)/u', '$5 - $1 $3');
        $this->replace('/^(\d+)\s+(شق|لل)(.*)/iu', '$2$3');
   
        $this->replace('/(\x{0629})([\x{0600}-\x{0699}]|[a-zA-Z0-9])/u', '$1 $2', 'Taa Marboutah followed by character');     
        $this->replace('/(\x{0627}|\x{0622}|\x{0623}|\x{0625})(\x{0627}|\x{0622}|\x{0623}|\x{0625}+)/u', '$1', 'Repeated Alef');
        
        $this->replace('/([a-zA-Z0-9])([\x{0600}-\x{0699}])/u', '$1 $2', 'Concatenated Latin Arabic');
        $this->replace('/([\x{0600}-\x{0699}])([a-zA-Z]|\$|الجديد)/u', '$1 $2', 'Concatenaed Arabic Latin');
        
        $this->replace('/([\x{0600}-\x{0699}]{2,})([0-9])/u', '$1 $2', 'Concatenaed Arabic Latin');

        if ($this->rootId==1) {
            $this->normalizeRealStateArea();
        }

        if ($this->countryCode=='KW') {
            $this->replace("/^ع\d+\s/u", "");
        }
        $this->replace('/(\d+)\s+\%/u', '$1%', 'Concatenate percentage to number');
        $this->replace('/\s+،/u', '،', 'Arabic comma space before');
        $this->replace('/\s+,/u', ',', 'Latin comma space before');
        $this->replace('/([a-zA-Z]),([a-zA-Z])/u', '$1, $2');
        $this->replace('/([a-zA-Z])\s+\:/u', '$1:');
        $this->replace('/\s+(و)\s+([\x{0600}-\x{0699}]{2,})/u', ' $1$2');
  

        $this->replace('/(\b\d+)(,)(\s+)(\d{2,}\b)(.|\s)/u', '$1$2$4$5');
        $this->replace('/([\x{0600}-\x{0699}]{2,})(\.)(\d{2,})/u', '$1$2 $3');
        
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

                  
        $this->replace('/\s+(\$)(\s+|)(\d{1,3}(,\d{1,3})*)/u', ' $1$3', 'USD Price');
        $this->replace('/\s+(\d{1,3}(,\d{1,3})*)(\s+|)(\$)/', ' $4$1', 'USD Price');
        $this->replace('/\s+(\d+)(\s+|)(\$)/', ' $3$1', 'USD Price');
        $this->replace('/\s+(\$)(\s+|)(\d+)/', ' $1$3', 'USD Price');
        
        $this->replace('/\s+(QR|qar)(\s+|)(\d+)/i', ' QR $3');

        $this->replace('/\b(05[0-9])(\s+|)(\/)(\s+|)(\d{7,9})\b/', '$1$5', 'UAE Mobile');
        $this->replace('/\b(0\d)(\s+\/|\/)(\s+|)(\d{6,7})\b/', '$1$4', 'EG Phone');
        
        $this->replace('/(\*)([^-\s])/u', '$1 $2');
        $this->replace('/([^-\s])(\*)/u', '$1 $2');
        $this->replace('/\s+(ت)\s+/u', ' ');
        $this->replace('/\"(\s+|)\"/u', ' ');
        $this->replace('/\s+/u', ' ', '');

       
        //$this->replace('/\.$/', '', 'Remove end period');
        $this->replace('/^\d{1,2}(\.|\s-)(\s+|)/', '');
        $this->replace('/^(ورحمة الله وبركاته)/u', '');
        $this->replace('/^\(\d+\)|السلام عليكم/u', '');
        $this->replace('/\s*(ب)(\d{7,})/u', ' $2');

        $this->replace('/(call us on)(\W|)$/i', '');
        $this->replace('/\s+(?:(?:\/(?:\s|))موبايل|هاتف|واتساب|واتس اب|تليفون).*$/u', '');
        
        $this->text = trim($this->text);
        $this->replace('/^(-|\.)/', '');
        $this->replace('/\s+(-|\.|ج)(\s+|)$/u', '');
        //error_log($this->text);
        $this->replace('/\s+(وشكرا|شكرا|Thanks|للاتصال\: email​|\/ موبايل \+ واتساب)$/u', '');


        // here
        $this->replace('/(\d+)(000)(\s+|)(الف)\b/u', '$1$2', 'duplicate thousand');
        
        $this->removeInTextTelephone();
        
        $this->splitConcatenatedWords();
        $this->concatenateWords();
        $this->correctWords();

        $this->replace('/^\s*(\p{P}|\s)+/u', '');
        $this->replace('/(\p{P}|\s)+\s*$/u', '');
        
        $this->extractAttributes();


        if ($mc>1) {
            $unicodeChar = '\u200b';
            $this->text .=  json_decode('"'.$unicodeChar.'"').$matches[$mc-1];
            $nums=[];
            
            if (preg_match_all('/\+(\d{9,})/', $matches[$mc-1], $nums)) {
                if (count($nums)>1 && is_array($nums[1])) {
                    foreach ($nums[1] as $num) {
                        $t = $this->phoneUtil->parse("+{$num}", 'LB', NULL, TRUE);
                        if ($this->phoneUtil->isValidNumber($t)) {
                            $num=$num+0;
                            if (!in_array($num, $this->attrs['phones']['n'])) {
                                $this->attrs['phones']['n'][] = $num;
                                $this->attrs['phones']['t'][] = $this->phoneUtil->getNumberType($t);
                            }
                        }
                        
                    }
                }
            }
            $mails=[];
            //syslog(LOG_INFO,$matches[$mc-1]);
            if (preg_match_all('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}\b/i', $matches[$mc-1], $mails)) {
                //syslog(LOG_INFO, json_encode($mails));
                $this->attrs['mails'] = [];
                foreach ($mails[0] as $mail) {
                    if (!in_array($mail, $this->attrs['mails']))
                        $this->attrs['mails'][] = $mail;                    
                }
            }
            
            //syslog(LOG_INFO, json_encode($this->attrs));
            
        }


    }
    


    function normalizeRealStateArea() {
        if ($this->rootId==1) {
            $this->replace('/\s(\d+)\s+(م)(نوم|نمره|غرف)/u', ' $1$2 $3');
            $this->replace('/(\d+)(\s+|)(ممربع|متر مربع|امتار|أمتار|متار|م٢|م2|م متر|م\.م\.|م\. م\.)\b/u', '$1م', 'Space Arabic Meter');
            $this->replace('/(\d+)(\s+|)(متر|م\s+2|م)\b/u', '$1م', 'Space Arabic Meter');
            $this->replace('/\b(\d+)(\s+|)(الف متر مربع|الف متر)/u', '$1000م');
            
            $this->replace('/(\d+)(\s+|)(meters|meter|m2|sqm|sq\.\s+mr|sq\.m|square meters|square meter|sqmr|sm|sq m)\b/iu', '$1m', 'Space Latin Meter');
            $this->replace('/(\d+)(\s+|)(m2|m)\b/iu', '$1m', 'Space/Area');
            $this->replace('/(\d+)(bedrooms|bedroom|bed|rooms|room|bathroom)(\s+)/iu', '$1 $2 $3');
        }
    }


    function splitConcatenatedWords() {
        $this->replace('/(د)(د[\x{0600}-\x{0699}]{3,})/u', '$1 $2');
        $this->replace('/(البحر)(مبا)/u', '$1 $2');
        $this->replace('/([\x{0600}-\x{0699}]{2,})(ء)(وال|ال)/u', '$1$2 $3');
        $this->replace('/([\x{0600}-\x{0699}]{3,})(ال)([\x{0600}-\x{0699}]{4,})/u', '$1 $2$3');
        $this->replace('/(ستديو)([\x{0600}-\x{0699}])/u', '$1 $2');
        $this->replace('/\s+(كما)(يوجد)/u', ' $1 $2');
        $this->replace('/\b(يتوفر|يوجد)(لدينا|است|شق|في)/u', '$1 $2');
        $this->replace('/(غاز)(ودش)/u', '$1 $2');
        $this->replace('/(سعر|ماستر)(نها|صال)/u', '$1 $2');
        $this->replace('/(جديد)([\x{0600}-\x{0699}]{2,})/u', '$1 $2');
        $this->replace('/(فى|على)(ال)/u', '$1 $2');
        $this->replace('/([\x{0600}-\x{0699}]{3,})(شارع)/u', '$1 $2');

        //$this->replace('/\b(\d{3,})(\s+|)(م)((?!ليون)[\x{0600}-\x{0699}]{3,})\/u', '$1$3 $4');
        
        $this->replace('/\s+(ه|ة)\b/u', '$1');
        $this->replace('/\s+(&)([\x{0600}-\x{0699}])/u', ' و$2');
    }

    
    function concatenateWords() {
        $this->replace('/\s(\d+)(\s)(000)(\s)/', ' $1$3 ');
        $this->replace('/(تر)\s+(كيب)\b/u', '$1$2');
        $this->replace('/\s+(ابو|أبو|عبد|و)\s+([\x{0600}-\x{0699}])/u', ' $1$2');
    }
    
    
    function correctWords() {
        $this->replace('/\bلبيع\b/u', 'للبيع');
        $this->replace('/\bلليجار\b/u', 'للايجار');
        $this->replace('/\bألف\b/u', 'الف');
    }
    
    
    function removeInTextTelephone() {
        $telephones=[];
        try {
            $this->replace('/(call\s+)(\d{2,})\s+(\d{2,})\s+(\d{2,})\s+(\d{2,})(\s+|)/ui', '$1 $2$3$4$5 ');
            //syslog(LOG_INFO, $this->text);
            if ($this->countryCode=='LB') {
                $this->replace('/\b(\d{2})(\s+|)(\/|\.|,)(\s+|)(\d{6})/', '$1$5');
                $this->replace('/\s(\d{6})([-])(\d{2})/', ' $3$1');
            }

            $len = mb_strlen($this->text);
            $len = floor($len/($len>64?3.0:2.0));

            $prefix = mb_substr($this->text, 0, $len);
            $suffix = mb_substr($this->text, $len);

            if (preg_match_all('/\b(\d{8,})(\b|$)/u', $suffix, $matches, PREG_OFFSET_CAPTURE|PREG_PATTERN_ORDER)) {

                //syslog(LOG_INFO, json_encode($matches));

                if (count($matches)>1) {                   
                    $mcPhone = new MCPhoneNumber();
   
                    foreach ($matches[1] as $match) {
                        $num = trim($match[0]);
                        if (!empty($num) && is_numeric($num)) {
                            try {
                                $mcPhone->parse($num, $this->countryCode, $this->cities);

                                if ($mcPhone->valid) {
                                    
                                    $pattern = '/\b('.$num.')/u';
                                    if ($this->publicationId==1)
                                        $suffix = preg_replace($pattern, '', $suffix);
                                    //else 
                                        
                                    if (!in_array($mcPhone->format(), $this->attrs['phones']['n'])) {
                                        $this->attrs['phones']['n'][] = $mcPhone->format();
                                        $this->attrs['phones']['t'][] = $mcPhone->type;
                                    }
                                }

                            } catch (Exception $ep) {
                                syslog(LOG_INFO, $this->countryCode.'] - ['.$num.'] '.$ep->getMessage());
                            }
                        }
                    }
                    $this->text = $prefix.$suffix;

                }

            } else {
                $msg = '';
                switch (preg_last_error()) {
                    case PREG_NO_ERROR:
                        break;
                    case PREG_INTERNAL_ERROR:
                        $msg = 'PREG_INTERNAL_ERROR';
                        break;
                    case PREG_BACKTRACK_LIMIT_ERROR:
                        $msg = 'PREG_BACKTRACK_LIMIT_ERROR';
                        break;
                    case PREG_RECURSION_LIMIT_ERROR:
                        $msg = 'PREG_RECURSION_LIMIT_ERROR';
                        break;
                    case PREG_BAD_UTF8_ERROR:
                        $msg = 'PREG_BAD_UTF8_ERROR';
                        break;
                    case PREG_BAD_UTF8_OFFSET_ERROR:
                        $msg = 'PREG_BAD_UTF8_OFFSET_ERROR';
                        break;
                    default:
                        $msg = 'UNKNOWN PREG ERROR';
                        break;
                }
                if ($msg) {
                    $msg.=': mb_len '.mb_strlen($this->text).' / offset '.$len.' ---> ['.$this->text.']';
                    syslog(LOG_INFO, $msg);
                }
            }
        } catch (Exception $ex) {
            syslog(LOG_INFO, $ex->getTraceAsString());
        }
    }
    
    
    function extractAttributes() {
        switch ($this->rootId) {
            case 1: // Real Estate
                $this->findSpace();
                $this->findPrice();
                //$this->findPlace();
                break;
            
            case 2: // Cars
                break;
            
            case 3: // Jobs
                break;
            
            case 4: // Services
                break;
            
            case 99: // Miscellaneous
                break;

            default:
                break;
        }
    }
    
    
    private function findPrice() {
        $prices=[];
        $tmp = preg_replace('/\:/', '', $this->text);

        $tmp = preg_replace('/(\s*)(\d+)(\s*)الف/u', '$1$2Z0Z$3', $tmp);
        $tmp = preg_replace('/(\s*)(\d+)\.(\d{3})(\s*|$)/', '$1$2$3$4', $tmp);
        $tmp = preg_replace('/(\s*)(\d{1,3}),(\d{3}),(\d{3})\.00\s*/', '$1$2$3$4', $tmp);
        $tmp = preg_replace('/(\s*)(\d{1,3}),(\d{3})(\s*)/', '$1$2$3$4', $tmp);
        $tmp = preg_replace('/(\s*)(\d{1,3})\.(\d{3})\.(\d{3})\s*/', '$1$2$3$4', $tmp);

        //syslog(LOG_INFO, $tmp);
        $tmp = preg_replace('/Z0Z/u', '000', $tmp);
        //syslog(LOG_INFO, $tmp);
        
        if (preg_match_all('/(السعر)\s*(\d+\.\d+)\s*مليون/iu', $tmp, $prices, PREG_SET_ORDER)) {
            $this->attrs['price'] = $prices[0][2] * 1000000;
            return;
        }

        if (preg_match_all('/(المطلوب|مطلوب)\s(\d+)[,](\d{3})[,](\d{3})(\s*|$)/u', $tmp, $prices, PREG_SET_ORDER)) {
            $str = $prices[0][2].$prices[0][3].$prices[0][4];
            $this->attrs['price'] = (int)$str;
            return;
        }

        if (preg_match_all('/(بسعر|مطلوب|السعر|price aed|price bhd|\$)\s*(\d+)(\s*|$)/iu', $tmp, $prices, PREG_SET_ORDER)) {
            //syslog(LOG_INFO, json_encode($prices));
            $this->attrs['price'] = $prices[0][2] + 0;
            return;
        }
    }
    
    
    private function findSpace() {
        $spaces = [];        
        if (preg_match_all('/(مساحة واسعة)(\s+)(\d+)(\s+|)[×x](\s+|)(\d+)/iu', $this->text, $spaces, PREG_SET_ORDER)) {
            $this->attrs['space'] = $spaces[0][3] * $spaces[0][6];
            return;
        }
        /*
        if (preg_match_all('/\b(\d+)(\s+|)[×x](\s+|)(\d+)(م|m)/iu', $this->text, $spaces, PREG_SET_ORDER)) {
            $this->attrs['space'] = $spaces[0][1] * $spaces[0][4];
            return;
        }
        */
        if (preg_match_all('/\b\s*(\d+|\d+\.\d+)\s*(دنمات|دونمات|دنم|دونم)/u', $this->text, $spaces, PREG_SET_ORDER)) {
            $this->attrs['space'] = $spaces[0][1] * 1000;
            return;
        }

        if (preg_match_all('/\b(\d{2,})(\s|)(m|م|قدم|feet|sqft|sq\.\sft|قدام)\b/iu', $this->text, $spaces, PREG_SET_ORDER)) {
            //syslog(LOG_INFO, json_encode($spaces));
            if ($spaces[0][3]!='m' && $spaces[0][3]!='م') {
                $spaces[0][1] = $spaces[0][1] * 0.3048;
            }
            
            $this->attrs['space'] = $spaces[0][1]+0;
            
        }
    }

    private function findPlace() {
        $places = [];
        $str = preg_replace('/\b(بطن|بيت|بركة|بعد|بلكونة|بسعر|بالكامل|بدون|بعض|بالتقسيط|بناية|باوفر|بالحديقه|بتسهيلات|بناء|بينهم|بنايات|برج)\b/u', '', $this->text);
        $str = preg_replace('/\b(شارع)\s+(مفتوح|رئيسي|وسكة)\b/u', '', $str);
        if (preg_match_all('/(للبيع|للايجار|شقة)(.*)(\s|\b)(في منطقة|بمنطقة|بحي|بمدينة|شارع)(\s)([\x{0600}-\x{0699}]{2,20})(\s|\b)(.*)/u', $str, $places, PREG_SET_ORDER)) {
            $this->attrs['place'] = trim($places[0][6]);
            return;
        }

        if (preg_match_all('/(للبيع|للايجار|شقة)(.*)(\s|\b)(ب)([\x{0600}-\x{0699}]{2,20})(\s|\b)(.*)/u', $str, $places, PREG_SET_ORDER)) {

            if ($places[0][5]=='رج')
                $this->attrs['place']='برج';
            else
                $this->attrs['place'] = trim($places[0][5]);

            return;
        }
        if (preg_match_all('/(للبيع|للايجار|شقة)(.*)(\s)(في\b)([\x{0600}-\x{0699}]{2,20})(\s|\b)(.*)/u', $str, $places, PREG_SET_ORDER)) {
            $this->attrs['place'] = trim($places[0][5]);
            return;
        }

    }
}

/*
if (php_sapi_name()=='cli') {
    $data = [
        "نيسان صنى يابانى موديل 2005 1.8 ماشيه 279000 ملكيه 4 شهور بحاله جيده مطلوب 11200​ ",
    "Apartments for sale ,near Alolo Hyper Area : 147 meters Price : 65000 negotiable Tel : 34492456​ / Mobile + Whatsapp: +97334492456",
    "للبيع بنايه في البسيتين مكونه من 20 شقه المساحه 278م2 الدخل 2450 السعر 280 الف دينار",
    "M.S.Hهي خدمة تغليف ونقل الاثاث 66930060 باستخدام مواد عاليه الجوده والمتانة من الموقع.فقط عليكم اختيارالموعد المناسب لكم ليقوم المتخصصون بيعملهم في فك ونقل وتغليف جميع انواع الاثاث ",
    "ارض تجارية تقع شرق خط السريع ( طريق الحرمين ) مقابل قاعة الرفيدي، تبلغ مساحتها: 2880 متر مربع. ",
    "ﺍﻟﺴﻼﻡ ﻋﻠﻴﻜﻢ ﻭﺭﺣﻤﻪ ﺍﻟﻠﻪ ﻭﺑﺮﻛﺎﺗﻪ ﺍﻧﺎ ﻋﺒﺪ ﺍﻟﺮﻭﻭﻑ ﻣﺤﻤﺪ ﺍﻟﺤﺎﺝ ﺍﻟﻌﺒﺪﻟﻲ ﻣﻦ ﺍﻟﻴﻤﻦ ﺻﻨﻌﺎ ﻟﺪﻳﺎ ﺧﺒﺮﻩ ﻓﻲ ﺍﻟﻤﺒﻴﻌﺎﺕ ﻭﺍﻟﺘﺴﻮﻳﻖ ﻣﻨﺬﻭ 11 ﺳﻨﻪ ﻓﻦ ﺍﻟﺘﻌﺎﻣﻞ ﻓﻲ ﺍﻟﺘﺴﻮﻳﻖ ﻭﺍﺑﺘﻜﺎﺭ ﺍﺳﺎﻟﻴﺐ ﺟﺪﻳﺪﻩ ﻓﻲ ﺗﺘﻄﻮﻳﺮ ﺍﻟﻌﻤﻞ ﺍﻟﻤﻮﺳﺴﻲ ﺧﺒﺮ ﻓﻲ ﺑﻴﻊ ﻭﺷﺮﺍ ﺍﻛﺴﺴﻮﺍﺭﺍﺕ ﺍﻟﺠﻮﺍﻝ ﺧﺒﺮﻩ ﻃﻮﻳﻠﻪ ﻭﺍﻟﻤﻮﺍﺩ ﺍﻟﻐﺬﺍﻳﻴﻪ ﻭﺍﻟﺪﻋﺎﻳﻪ ﻭﺍﻻ ﻋﻼﻥ ﺍﺳﺘﺨﺪﺍﻡ ﺍﻟﻔﻮﺍﺗﻴﺮ ﻭﺟﻤﻊ ﺍﻟﺤﺴﺎﺑﺎﺕ ﺑﺪﻭﻥ ﺍﻟﺮﺟﻮﻉ ﺍﻻ ﻟﻪ ﺍﻟﺤﺎﺳﺒﻪ ﻋﻠﻲ ﺍﻱ ﺷﺮﻛﻪ ﺗﺤﺘﺎﺝ ﺍﻻ ﻣﻨﺪﻭﺏ ﻧﺎﺟﺢ ﺗﻘﺪﻡ ﻛﺎﻓﻪ ﻃﻠﺒﺎﺗﻲ ﻭﺭﻭﺍﺗﺐ ﻭﻋﻤﻮﻻت ﻭﻋﻨﺪﻱ",
    "الحازميه مارتقلا ط2 سوبر دولوكس 4 ماستر صالونان وسفره مطبخ غ خادمه مع حمامها شوفاج شومينه طاقه شمسيه view ديكور كاف موقفان مفروشه بالكامل جديده $835000​ / موبايل: <span class='pn'>+96170463026</span> او <span class='pn'>+96176463026</span>"
    ];

    $i = 0;

    $formatter = new AdTextFormatter($data[$i]);
    $formatter->setDebug();
    $formatter->format();
}
*/

class MCPhoneNumber {
    public static $countryOf = [
        1=>'LB', 
        4=>'AE', 6=>'AE', 14=>'AE', 333=>'AE', 436=>'AE', 812=>'AE', 815=>'AE', 2609=>'AE',
        5=>'BH',
        7=>'SA', 8=>'SA', 9=>'SA', 12=>'SA', 26=>'SA', 27=>'SA', 28=>'SA', 29=>'SA', 1255=>'SA', 
        10=>'EG', 11=>'EG', 22=>'EG', 23=>'EG',
        13=>'SY', 21=>'SY',
        15=>'KW', 24=>'KW', 25=>'KW',
        16=>'JO', 17=>'JO', 18=>'JO', 19=>'JO',
        20=>'QA',
        1200=>'SD',
        1210=>'TN',
        1224=>'YE',
        1226=>'DZ',
        180=>'IQ', 1239=>'IQ',
        1230=>'LY',
        1026=>'MA',
        761=>'OM'        
        ];
    
    private $util;

    public $number;
    public $type;
    public $valid;

    
    function __construct($number='', $country='') {
        $this->util = \libphonenumber\PhoneNumberUtil::getInstance();
        if (!empty($number) && !empty($country)) {
            $this->parse($number, $country);
        }

    }


    function parse($number, $country, $cities) {
        $this->number = $this->util->parse($number, $country, null, true);
        $this->valid = $this->util->isValidNumber($this->number);

        if (!$this->valid) {
            //syslog(LOG_INFO, json_encode($cities));
            foreach ($cities as $cityId) {
                //syslog(LOG_INFO, $cityId . ' ==> '. self::$countryOf[$cityId]);
                $this->number = $this->util->parse($number, self::$countryOf[$cityId], null, true);
                $this->valid = $this->util->isValidNumber($this->number);
                if ($this->valid) break;
            }
        }
        
        $this->type = $this->valid ? $this->util->getNumberType($this->number) : -1;
        
    }
    
    function format() {
        return $this->util->format($this->number, \libphonenumber\PhoneNumberFormat::E164)+0;
    }

}



class UDFJson {
    protected static $_messages = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );

    private $object;

    public static function encode($value, $options = 0) {
        $result = json_encode($value, $options);

        if($result)  {
            return $result;
        }

        throw new RuntimeException(static::$_messages[json_last_error()]);
    }


    public static function decode($json, $assoc = false) {
        $result = json_decode($json, $assoc);

        if($result) {
            return $result;
        }

        throw new RuntimeException(static::$_messages[json_last_error()]);
    }


    function decode1($jsonString) {
        $jsonString = trim($jsonString);
        if (strlen($jsonString)>2 && $jsonString[0]=='{') {
            $jsonString = preg_replace('/}(?=[^}]*$).*/u', '}', $jsonString);
        }

        $this->object = json_decode($jsonString);
    }
}

?>