<?php
\Config::instance()->incLayoutFile('Page');

use Core\Lib\MCUser;

class UserPage extends Page {
    public int $uid=0;
    public int $balance=0;
    
    public PageSide $side;

    function __construct() {
        parent::__construct();    
        
        
        if (!$this->user->isLoggedIn()) {
            $this->user->redirectTo($this->router->getLanguagePath('/signin/'));
        }
               
        if ($this->router->config->isMaintenanceMode()) {
            $this->user->redirectTo($this->router->getLanguagePath('/maintenance/'));
        }
        
        if ($this->user()->level()===5) {
            $this->user->redirectTo($this->router->getLanguagePath('/blocked/'));
        }         
        elseif($this->user->info['level']===6) {
            $this->user->redirectTo($this->router->getLanguagePath('/suspended/'));
        }
        
        if ($this->user()->level()===9) {
            $this->uid=$this->getGetInt('u');
        }
        
        if ($this->uid>0 && $this->uid!==$this->user->id()) {
            $this->balance=$this->user->getProfileOfUID($this->uid)->getBalance();
        }
        else {
            $this->balance=$this->user->getBalance(); 
        }
        $this->side=new PageSide($this);
    }
    
    
    public function welcome() : void {
        ?><div class=welcome><?php
        $name=\trim($this->user->getProfileOfUID($this->uid)->getFullName());
        $last_name=(\strpos($name, ' ')===false)?'':\preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name=\trim(\preg_replace('#'.$last_name.'#', '', $name ));
        ?><div id=start><div class=fw-300 style="color:var(--mdc50);margin-bottom:8px">Hello <?=$first_name?>!</div><div class="fw-300">Your current balance of</div><div class="fw-700">mourjan <span style="color:var(--premium)">PREMIUM</span> <span class="fw-300">is:</span></div></div><?php
        $adjusted_size='';
        if ($this->balance<100) {
            $adjusted_size=' two';
        }
        else if ($this->balance<10) {
            $adjusted_size=' one';
        }
        ?><div id=end><div class="flex va-center fw-300"><span class="empty-coin<?=$adjusted_size?>"><?=$this->balance?></span>DAYS LEFT</div><?php
        ?><a class="va-center fw-700" style="align-self:flex-end;font-size:16px;margin-top:-32px" href="<?=$this->router->getLanguagePath('/gold/').'#how-to'?>"><span style="color:var(--mdc70)">Buy more days </span><span class="inline-flex fw-300" style="font-size:30px;border:1px solid var(--mdc70);border-radius:50%;width:32px;height:32px;justify-content:center;align-items:center;margin-inline-start:12px">+</span></a></div><?php
        ?></div><?php        
    }
    
    
    
}


class PageSide {
    private UserPage $page;
    private array $buffer=[];
    
    function __construct(UserPage $page) {
        $this->page=$page;
        $this->buffer['open']='<div class=side>';        
    }
    
    
    
    public function build() : void {
        $this->buffer['close']='</div>';
        echo \implode('', $this->buffer);
    }
    
    
    public function setClassCSS(string $classList) : PageSide {
        $this->buffer['open']="<div class='{$classList}'>";
        return $this;
    }
    
    
    public function avatar() : PageSide {
        $profile=$this->page->user->getProfileOfUID($this->page->uid);        
        $num=$this->page->phoneUtil->parse("+{$profile->getMobileNumber()}", 'LB');
        $name=empty($profile->getFullName())?'No Name':$profile->getFullName();
        $b ='<div class="avatar ff-cols ha-center va-center mb-64">';
        $b.="<img class=ifilter style='width:105px;' src={$this->page->router->config->cssURL}/1.0/assets/avatar.svg />";
        $b.="<div style='font-size:24px;font-weight:700;margin:20px 0 8px'>{$name}</div>";
        if ($num && $this->page->phoneUtil->isValidNumber($num)) {
            $b.="<div class=fw-300 style='color:#fd636a;font-size:18px'>{$this->page->phoneUtil->format($num, \libphonenumber\PhoneNumberFormat::INTERNATIONAL)}</div>";
        }
        
        $type=$profile->getPublisherStatus(); 
        if ($profile->isSuspended()) {
            $time=MCSessionHandler::checkSuspendedMobile($profile->getMobileNumber());
            $hours=0;
            $lang=$this->page->router->language;
            if ($time>0) {
                $hours=$time/3600;
                if (\ceil($hours)>1) {
                    $hours=\ceil($hours);
                    if ($lang==='ar') {
                        if ($hours==2) {
                            $hours='ساعتين';
                        }
                        elseif ($hours>2 && $hours<11) {
                            $hours=$hours.' ساعات';
                        }
                        else {
                            $hours=$hours.' ساعة';
                        }
                    }
                    else {
                        $hours=$hours.' hours';
                    }
                }
                else {
                    $hours=\ceil($time/60);
                    if ($lang==='ar') {
                        if ($hours==1) {
                            $hours='دقيقة';
                        }
                        elseif ($hours==2) {
                            $hours='دقيقتين';
                        }
                        elseif ($hours>2 && $hours<11) {
                            $hours=$hours.' دقائق';
                        }
                        else {
                            $hours=$hours.' دقيقة';
                        }
                    }
                    else {
                        $hours.=($hours>1)?' minutes':' minute';
                    }
                }
            }
            $b.='<span class="alert alert-warning" style="align-self:center;width:auto"><span class="wait"></span>'.$hours.'</span>';
        }
        
        if ($this->page->uid>0 && $this->page->uid!==$this->page->user->id() && $this->page->user->level()===9) {
            $b.="<span id=filters class='alert w100'>{$this->page->lang['user_type_label']}&nbsp;<select onchange=\"d.setUserType(this,{$this->page->uid})\">";
            $b.="<option value=0>{$this->page->lang['user_type_option_0']}</option>";
            $b.="<option value=1".($type==1?' selected':'').">{$this->page->lang['user_type_option_1']}</option>";
            $b.='<option value=2'.($type==2?' selected':'').">{$this->page->lang['user_type_option_2']}</option></select></span>";                    
        }
        $b.='</div>';                        
        $this->buffer['avatar']=$b;
        return $this;
    }
    
    
    public function menu() : PageSide {
        $router=$this->page->router;
        $b= '<ul class=mb-64>';
        $b.="<li><a".($router->module==='myads'?' class=on':'')." href={$router->getLanguagePath('/myads/')}".($this->page->uid>0?'?u='.$this->page->uid:'')."><img class=ifilter src={$router->config->cssURL}/1.0/assets/myads.svg>{$this->page->lang['myAds']}</a></li>";
        $b.="<li><a".($router->module==='statement'?' class=on':'')." href={$router->getLanguagePath('/statement/')}".($this->page->uid>0?'?u='.$this->page->uid:'')."><img class=ifilter src={$router->config->cssURL}/1.0/assets/m-coin.svg>{$this->page->lang['myBalance']}</a></li>";
        $b.="<li><a href=#><img src={$router->config->cssURL}/1.0/assets/liked.svg>Favorites</a></li>";
        $b.="<li><a href=#><img src={$router->config->cssURL}/1.0/assets/starred.svg>Saved Items</a></li>";
        $b.="<li><a href={$router->getLanguagePath('/account/')}".($this->page->uid>0?'?u='.$this->page->uid:'')."><img src={$router->config->cssURL}/1.0/assets/accinfo.svg>{$this->page->lang['myAccount']}</a></li>";
        $b.="<li><a href=#><img src={$router->config->cssURL}/1.0/assets/paymethod.svg>Payment Methods</a></li>";
        $b.="<li><a href=#><img src={$router->config->cssURL}/1.0/assets/help.svg>Need Help</a></li>";
        $b.="<li><a href=/web/lib/hybridauth/?logout={$this->page->user->info['provider']}><img src={$router->config->cssURL}/1.0/assets/signout.svg>{$this->page->lang['signout']}</a></li>";
        $b.='</ul>';
        $this->buffer['menu']=$b;
        return $this;
    }
    
    
    public function addBlock(string $key, string $html) : PageSide {
        $this->buffer[$key]=$html;
        return $this;
    }
    
    
    
    
}
