<?php

class WeConnect {
  private $self = array ();
  public function __construct($debug=false)
  {
    $this->self['debug']   = $debug;
    $this->self['raw']     = '';
    $this->self['dir']     = dirname(__FILE__).DIRECTORY_SEPARATOR;
    $this->self['cookies'] = '';
  }

  public function debug($flag){$this->self['debug']=$flag;}

  public function getRaw () {return $this->self['raw'];}

  public function request($name, $post=array(), $detail=0, $test=0)
  {
    $this->self['raw'] = '';
    $post['request']   = $name;
    $post['detail']    = $detail;
    $post['test']      = $test;
    return $this->send($post);
  }

  /***************************************************************/
  private function send($post)
  {
    include $this->self['dir']."settings.php";
    $uri          = "secure";
    $post['pin']  = $weconnect_pin;
    $this->self['cookies'] = '';
    if (! $weconnect_use_cert)
    {
      $post['user']  = $weconnect_user;
      $uri           = "api";
      $res           = new SimpleXMLElement($this->use_curl("https://${weconenct_host}/${uri}/token.php?user=${weconnect_user}", $post));
      $token         = "";
      if (isset($res->token)){$token = $res->token[0]['value'];}
      $str_keys      = "apikey=${weconnect_apikey};token=${token}";
      $keys          = array_keys($post);
      sort($keys);
      foreach ($keys as $k){$str_keys="${str_keys};${k}=".$post[$k];}
      $post['signature']=md5($str_keys);
    }
    $url      = "https://${weconenct_host}/${uri}/?request=".$post['request']."&user=${weconnect_user}";
    $res      = $this->use_curl($url, $post);
    if ($this->self['debug']){print "$res";}
    $this->self['raw'] = $res;
    return new SimpleXMLElement($res);
  }

  private function use_curl($url, $post)
  {
    include $this->self['dir']."curl-settings.php";
    $str_post = '';
    foreach ($post as $k => $v){$str_post="${str_post}&${k}=${v}";}
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,            $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $weconnect_server_verify);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $weconnect_verify_host);
    curl_setopt($curl, CURLOPT_CAINFO,         $this->self['dir']."${weconnect_ssl_ca}");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_HEADER,         true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,     ltrim($str_post, "&"));
    curl_setopt($curl, CURLOPT_COOKIE,         $this->self['cookies']);
    if ($weconnect_use_cert)
    {
      include $this->self['dir']."cert-settings.php";
      curl_setopt($curl, CURLOPT_SSLCERT,   $weconnect_ssl_cert);
      curl_setopt($curl, CURLOPT_SSLKEY,    $weconnect_ssl_key);
      curl_setopt($curl, CURLOPT_KEYPASSWD, $weconnect_ssl_pass);
    }
    $res    = curl_exec($curl);
    if (curl_errno($curl) != 0)
    {
      $res=$this->local_error(curl_errno($curl), curl_error($curl));
      curl_close($curl);
      return $res;
    }
    $hsize  = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($res, 0, $hsize);
    $res    = substr($res, $hsize);
    curl_close($curl);
    preg_match_all('|Set-Cookie: (.*);|U', $header, $ms);
    $this->self['cookies'] = implode('; ', $ms[1]);
    return $res;
  }

  private function local_error($code, $msg)
  {
    return "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n<response>\n  <error value=\"$msg\" code=\"$code\"/>\n<status  value=\"Local Communication Error\" code=\"9998\"/></response>\n";
  }
}
?>
