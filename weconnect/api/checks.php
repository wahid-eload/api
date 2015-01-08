<?php
  $err=0;  
  print "Checking curl ..... ";
  if  (in_array ('curl',get_loaded_extensions())){ 
     print "OK\n";
  }
  else
  {
    print "FAIL\n";
    print "Please install php5-curl. On Ubuntu, you can install it by running the following commands as root.\n";
    print "sudo apt-get install php5-curl\nsudo service apache2 restart";
    $err=1;
  }
  print "Checking SimpleXML ..... ";
  if  (in_array ('SimpleXML',get_loaded_extensions())){ 
     print "OK\n";
  }
  else
  {
    print "FAIL\n";
    print "Please install php5-cli. On Ubuntu, you can install it by running the following commands as root.\n";
    $err=1;
  }
  exit ($err)
?>

