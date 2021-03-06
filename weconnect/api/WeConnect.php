<?php

class WeConnect {
  private $self = array ();
  public function __construct($debug=false)
  {
    $this->self['debug']   = $debug;
    $this->self['raw']     = '';
    $this->self['dir']     = dirname(__FILE__).DIRECTORY_SEPARATOR;
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
    include $this->self['dir']."/settings.php";
    $post['pin']   = $weconnect_pin;
    $post['user']  = $weconnect_user;
    $uri           = "api/v2";
    $token         = md5(microtime());
    $str_keys      = "apikey=${weconnect_apikey};token=${token}";
    $url     = "https://${weconenct_host}/${uri}/?request=".$post['request']."&user=${weconnect_user}";
    $headers = array("Authorization: ".md5($str_keys), "WahidToken: $token");
    $res = $this->use_curl($url, $post, $headers);
    if ($this->self['debug']){print "$res";}
    $this->self['raw'] = $res;
    return new SimpleXMLElement($res);
  }

  private function use_curl($url, $post, $headers)
  {
    include $this->self['dir']."/curl-settings.php";
    $str_post = '';
    foreach ($post as $k => $v){$str_post="${str_post}&${k}=${v}";}
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,            $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $weconnect_server_verify);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $weconnect_verify_host);
    curl_setopt($curl, CURLOPT_CAINFO,         $this->self['dir']."${weconnect_ssl_ca}");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($curl, CURLOPT_HEADER,         false);
    curl_setopt($curl, CURLOPT_POSTFIELDS,     ltrim($str_post, "&"));
    curl_setopt($curl, CURLOPT_HTTPHEADER,     $headers);
    $res    = curl_exec($curl);
    if (curl_errno($curl) != 0)
    {
      $res=$this->local_error(curl_errno($curl), curl_error($curl));
      curl_close($curl);
      return $res;
    }
    curl_close($curl);
    return $res;
  }

  private function local_error($code, $msg)
  {
    return "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n<response>\n  <error value=\"$msg\" code=\"$code\"/>\n  <status  value=\"Local Communication Error\" code=\"9998\"/>\n</response>\n";
  }
}
?>
