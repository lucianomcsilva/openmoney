<?php
class TOAuthClient
{
   private $version = '1.0';
   
   private $consumerKey;
   private $consumerSecret;
   
   private $nonce;
   private $timestamp;
      
   private $apiUrl;

   private $token;
   private $tokenSecret;
   
   private $tokenVerifier;
   
   private $signatureMethod ='HMAC-SHA1';
   
   private $params;
   //---------------------------------------------------------------------------  
   public function __construct($consumerKey, $consumerSecret)
   {
      $this->consumerKey = $consumerKey;
      $this->consumerSecret = $consumerSecret;
      $this->params = array();
   }
   //---------------------------------------------------------------------------  
   public function setApiUrl($Url)
   {
      $this->apiUrl = $Url;
   }    
   //---------------------------------------------------------------------------  
   public function setNonceTimestamp($Nonce = null, $Timestamp = null)
   {
      //for test purpose
      $this->nonce      = $Nonce;
      $this->timestamp  = $Timestamp;
   }       
   //---------------------------------------------------------------------------  
   public function setToken($Token = null, $Secret = null)
   {
      $this->token = $Token;
      $this->tokenSecret = $Secret;

   } 
   //---------------------------------------------------------------------------  
   public function encode($string)
   {
      return rawurlencode(utf8_encode($string));
   }
   //---------------------------------------------------------------------------  
   public function generateNonce()
   {
      if(isset($this->nonce)) // for unit testing
      return $this->nonce;
      
      return md5(uniqid(rand(), true));
   }
   //---------------------------------------------------------------------------    
   public function normalizeUrl($url = null)
   {
      $urlParts = parse_url($url);
      $scheme = strtolower($urlParts['scheme']);
      $host   = strtolower($urlParts['host']);
      //$port = intval($urlParts['port']);
      
      $retval = "{$scheme}://{$host}";
      $retval .= $urlParts['path'];
      if(!empty($urlParts['query']))
      {
         $retval .= "?{$urlParts['query']}";
      }
      
      return $retval;
   }
   //---------------------------------------------------------------------------     
   public function AddParam($Key, $Value)
   {
      $this->params[$Key] = $Value;
   }
   //---------------------------------------------------------------------------     
   public function getHeader()
   {
      $oauth = array();
      $oauth['oauth_consumer_key']     = $this->consumerKey;
      $oauth['oauth_signature_method'] = $this->signatureMethod;
      $oauth['oauth_timestamp']        = !isset($this->timestamp) ? time() : $this->timestamp; // for unit test
      $this->nonce = $this->generateNonce();
      $oauth['oauth_nonce']            = $this->nonce; 
      
      
      $oauth['oauth_version']          = $this->version;
      if(isset($this->token))
        $oauth['oauth_token']            = $this->token;
                                
      $encodedParams = array();
      $encodedParams = array_merge($oauth, (array)$this->params);

      
      $concatenatedParams = '';
      foreach($encodedParams as $k => $v)
      {
         $v = $this->encode($v);
         $concatenatedParams .= "{$k}=\"{$v}\",";
      }
      $concatenatedParams = substr($concatenatedParams, 0, -1);    
         
      return "Authorization: OAuth realm=\"Example\",{$concatenatedParams},oauth_signature=\"".$this->encode($this->signedString())."\"";
   }
   
   //---------------------------------------------------------------------------  
   public function concatenateParams()
   {      
      $oauth = array();
      $this->nonce = $this->generateNonce();
      $oauth['oauth_consumer_key']     = $this->consumerKey;
      $oauth['oauth_nonce']            = $this->nonce;
      $oauth['oauth_timestamp']        = !isset($this->timestamp) ? time() : $this->timestamp; // for unit test
      $oauth['oauth_signature_method'] = $this->signatureMethod;
      $oauth['oauth_version']          = $this->version;
      if(isset($this->token))
        $oauth['oauth_token']            = $this->token;
                                
      $encodedParams = array();
      $encodedParams = array_merge($oauth, (array)$this->params);
             
      // encoding
      array_walk($encodedParams, array($this, 'encode'));

      // sorting
      ksort($encodedParams);
      
      $concatenatedParams = '';
      foreach($encodedParams as $k => $v)
      {
         $v = $this->encode($v);
         $concatenatedParams .= "{$k}={$v}&";
      }
      $concatenatedParams = $this->encode(substr($concatenatedParams, 0, -1));     
      
      return $concatenatedParams;      
   }
   //---------------------------------------------------------------------------
   public function baseSignatureString()
   {   
      $method              = "POST";
      $normalizedUrl       = $this->encode($this->normalizeUrl($this->apiUrl));
      $concatenatedParams  = $this->concatenateParams();
      
      $signatureBaseString = "{$method}&{$normalizedUrl}&{$concatenatedParams}";
      return $signatureBaseString; 
   }   
   //---------------------------------------------------------------------------        
   public function signedString()
   {
        $key = $this->encode($this->consumerSecret) . '&' . $this->encode($this->tokenSecret);
        $retval = base64_encode(hash_hmac('sha1', $this->baseSignatureString(), $key, true)); 
        return $retval;     
   } 
   //---------------------------------------------------------------------------
   public function __toString()
   {
      $oauth = array();
      $oauth['oauth_consumer_key']     = $this->consumerKey;
      $oauth['oauth_nonce']            = $this->nonce;
      $oauth['oauth_timestamp']        = !isset($this->timestamp) ? time() : $this->timestamp; // for unit test
      $oauth['oauth_signature_method'] = $this->signatureMethod;
      $oauth['oauth_version']          = $this->version;
      if(isset($this->token))
        $oauth['oauth_token']          = $this->token;
      if(isset($this->tokenSecret))
        $oauth['oauth_token_secret']   = $this->tokenSecret;
                
      $html = "<ul>";
      foreach ($oauth as $key=>$value) {
         $html .= "<li>{$key} :: {$value}</li>";   
      }
      foreach ($this->params as $key => $value) {
         $html .= "<li>{$key} :: {$value}</li>";
      }
      $key = $this->encode($this->consumerSecret) . '&' . $this->encode($this->tokenSecret);
      $html .= "<li>Key :: {$key}</li>";

      $html .= "</ul>";
      return $html;
   }
   //--------------------------------------------------------------------------- 
}
?>