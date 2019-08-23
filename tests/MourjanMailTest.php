<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once dirname(__DIR__).'/config/cfg.php';
Config::instance()->incModelFile('Router');
include_once Config::instance()->baseDir.'/bin/utils/MourjanMail.php';

final class MourjanMailTest extends TestCase {
    
    public function testMourjanPath() : void {
        $this->assertEquals(\get_cfg_var('mourjan.path'), dirname(__DIR__));
    }
    
    
    public function testIncludeMourjanMailClass() : void {
        
        $mm=new MourjanMail;
        $this->assertInstanceOf(MourjanMail::class, $mm);
    }
    
    
    public function testSendNoReplyAccount() : void {
        $mm=new MourjanMail;
        $mm->Username='noreply@mourjan.com';
        $mm->setFrom('noreply@mourjan.com', 'Mourjan.com Notification');
        $mm->AddAddress('admin@berysoft.com', 'Berysoft Admin');
        $mm->isHTML(true);    
        $mm->Subject='test no-reply send';
        $mm->Body='This is the HTML message body <b>in bold!</b>';
        $mm->AltBody='This is the body in plain text for non-HTML mail clients';
        
        $this->assertEquals($mm->Send(), true);
    }
    
    
    public function testSendFromAccountAccount() : void {
        $mm=new MourjanMail;
        $mm->Username='account@mourjan.com';
        $mm->setFrom('account@mourjan.com', 'Mourjan.com Account Handler');
        $mm->AddAddress('admin@berysoft.com', 'Berysoft Admin');
        $mm->isHTML(true);    
        $mm->Subject='test account@mourjan.com send';
        $mm->Body='This is the HTML message body <b>in bold!</b>';
        $mm->AltBody='This is the body in plain text for non-HTML mail clients';
        
        $this->assertEquals($mm->Send(), true);
    }
    
}