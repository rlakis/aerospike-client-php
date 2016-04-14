<?php
include $config['dir']. '/core/layout/Page.php';
require_once( $config['dir'].'/web/lib/hybridauth/Hybrid/Auth.php' );

class OAuth extends Page{

    function OAuth($router){
        parent::Page($router);
        $this->render();
    }

    function render(){
        $pass=true;
        try{
            $hybridauth = new Hybrid_Auth( $this->urlRouter->cfg['dir'].'/web/lib/hybridauth/config.php' );
        }
        catch( Exception $e ){
            $pass=false;
            trigger_error($e);
            $this->systemError();
        }
        if ($pass){
            $provider  = @ $_GET["provider"];
            $return_to = @ $_GET["return_to"];
            if( ! empty( $provider ) && $hybridauth->isConnectedWith( $provider ) ){
                $return_to = $return_to . ( strpos( $return_to, '?' ) ? '&' : '?' ) . "connected_with=" . $provider ;
                ?><script language="javascript">
                    if(  window.opener ){
                        /*try { window.opener.parent.$.colorbox.close(); } catch(err) {}*/
                        window.opener.parent.location.href = "<?php echo $return_to; ?>";
                    }
                    window.self.close();
                </script><?php
            }elseif( !empty( $provider ) ){
                $params = array();

                if( $provider == "OpenID" ){
                    $params["openid_identifier"] = @ $_REQUEST["openid_identifier"];
                }

                if( isset( $_REQUEST["redirect_to_idp"] ) ){
                    $adapter = $hybridauth->authenticate( $provider, $params );
                }
                else{
                    ?><center><br /><br /><br /><img height="64" width="64" src="<?= $this->urlRouter->cfg['url_css'] ?>/i/loading.gif" /><br /><br /><h3><?= 'Please wait while we contact <b>'.ucfirst( strtolower( strip_tags( $provider ) ) ).'</b>' ; ?></h3></center>
                    <script>setTimeout('window.location.href = window.location.href + "&redirect_to_idp=1";',100)</script>
                    <?php
                }
            }else  parent::render();
        }
    }

    function systemError(){
        ?><style>
        a{text-decoration:none}
        a:hover{text-decoration:underline}
        </style>
<center><br /><img height="64" width="64" src="<?= $this->urlRouter->cfg['url_css'] ?>/i/s/i/alert.png" />
<br /><h3><?= $this->lang['authError'] ?></h3></center>
<?php
    }

    function _header(){
        $country_code="";
        if ($this->urlRouter->countryId && array_key_exists($this->urlRouter->countryId, $this->urlRouter->countries)) {
            $country_code = '-'.$this->urlRouter->countries[$this->urlRouter->countryId][3];
        }
        $return_to = (isset($_GET["return_to"]) ? $_GET["return_to"]:'');
        ?><!doctype html><html lang="<?= $this->urlRouter->siteLanguage . $country_code ?>"><head><?php
        echo '<title>',  $this->lang['signin'] , ' ', $this->lang['title_suffix'], '</title>';
        echo '<meta name="robots" content="noindex, nofollow" />';
        $this->load_css();
        echo '<link rel=\'stylesheet\' type=\'text/css\' href=\'', $this->urlRouter->cfg['url_css'], '/', 'imgs.css\' />';
        ?><script src="<?= $this->urlRouter->cfg['url_jquery'] ?>/jquery.min.js"></script><script>var idp = null;$(function() {$(".idps span").click(function(){idp=this.className;switch( idp ){case "google": case "twitter" : case "yahoo" : case "facebook": /* case "aol" :*/ case "linkedin" : case 'live':start_auth( "?provider=" + idp );break;case "openid" :$("#idps").hide();$("#idos").show();break;default: break;}});$("#obt").click(function(){oi = un = $( "#otx" ).val();if( ! un ){return false;}start_auth( "?provider=openid&openid_identifier=" + escape( oi ) );});$("#bls").click(function(){$("#idos").hide();$("#idps").show();return false;});$("#sbls").click(function(){$("#idhs").hide();$("#idps").show();return false});});function start_auth( params ){start_url = params + "&return_to=<?php echo urlencode( $return_to ); ?>" + "&_ts=" + (new Date()).getTime();window.open(start_url,"hybridauth_social_sing_on","location=0,status=0,scrollbars=0,width=800,height=500");}</script></head><?php
    }

    function _body(){
        ?><body class="auth"><?php
        $hide=false;
        if (!$this->user->info['id'] && isset ($this->user->info['name']) && isset ($this->user->info['provider'])) {
            $hide=true;
            ?><div id="idhs" class="idos idhs"><?php
        ?><ul><?php
            ?><li><h4><span class="<?= $this->user->info['provider'] ?>"></span><?= $this->user->info['provider'] ?></h4></li><?php
            ?><li><input type="button" id="spbt" onclick='<?= $this->user->info['provider']=='openid'? '$("#idhs").hide();$("#idos").show()' : 'start_auth( "?provider='.$this->user->info['provider'].'")' ?>' value="<?= $this->lang['signAs'].$this->user->info['name'] ?>" class="bt rc" /></li><?php
            ?><li><input type="button" id="sbls" value="<?= $this->lang['switchSP'] ?>" class="bta" /></li><?php
        ?></ul><?php
	?></div><?php
        }
	?><div id="idps" class="idps"<?= $hide? ' style="display:none"':''?>><?php
        ?><ul><?php
            ?><li><span class="facebook"></span>Facebook</li><?php
            ?><li><span class="twitter"></span>Twitter</li><?php
            ?><li><span class="linkedin"></span>Linkedin</li><?php
            ?><li><span class="google"></span>Google</li><?php
            ?><li><span class="yahoo"></span>Yahoo</li><?php
            ?><li><span class="live"></span>Hotmail</li><?php
            /* ?><li><span class="aol"></span>AOL</li><?php */ 
            ?><li><span class="openid"></span>OpenId</li><?php
        ?></ul><?php
	?></div><?php
    ?><div id="idos" class="idos oid" style="display:none"><?php
        ?><ul><li><h2><span class="openid"></span><?= $this->lang['oid_enter_url'] ?></h2></li><?php
        ?><li><input type="text" name="otx" id="openidun" /></li><?php
        ?><li><input type="button" id="bls" value="<?= $this->lang['back'] ?>" class="bta" /><?php
        ?><input type="button" id="obt" value="<?= $this->lang['signin'] ?>" class="bt rc" /></li><?php
        ?></ul><?php
    ?></div><?php
    ?><p><?= $this->lang['disclaimer'] ?></p><?php
    ?></body><?php
    }

}
?>