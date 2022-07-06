# Wahid eLoad API Client
This php client version 2 (and above) implements the following Wahid eLoad API. In order to use the client see the documentation provided http://weconnect.com.pk/apidoc . Following is the detail of Wahid eLoad API in case you need to implement our API differently.

# Wahid eLoad API
All Wahid eLoad API access is over HTTPS, and accessed from the https://www.weconnect.com.pk/api/v2 
All Requests need to POST following required parameters along with any paramemters needed for request

| Parameter | Value |
|:--------:|:---------------:|
| user     | Your register number e.g 3901234567 |
| request  | request number e.g 1 |
| pin      | your 4 to 6 digit Wahid PIN e.g 012345 |

For authentication, you need to include following headers in your HTTPS request

| Header | Value
|:-----:|:--------|
| WahidToken | MD5 of a unique string |e a unique 
| Authorization | MD5 of string **apikey**=*api-key*;**token**=*WahidToekn* |

# Example
Lets assume your register number is ```3001234567```, your Wahid PIN is ```012345``` and API key is ```abcdef1234567890```. For each request we need to have unique ```WahidToken```. Easiest is to use the EPOCH time ( https://www.epochconverter.com/ ). Lets assume current EPOCH time is 1500928932 so our ```WahidToken``` will be *md5(1500928932)=*```4c65f3d505f44512900ed14cae71b2da``` and our ```Authorization``` is *md5(apikey=abcdef1234567890;token=4c65f3d505f44512900ed14cae71b2da)*=```6d9dc22ec4feebfe37dcfb1d87f1cccf```

Now to make API request using curl client we can do the following

## Balance Checking
```curl -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=2' 'https://www.weconnect.com.pk/api/v2'```

## Pre-paid load request
### Request pre-paid Mobilink load of Rs 100 for number 3007654321
```curl -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=2&mtype=1&number=3007654321&compnay=30&amount=100' 'https://www.weconnect.com.pk/api/v2'```

### Request post-paid Telenor load of Rs 1000 for number 3127654321
```curl -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=2&mtype=9&number=3127654321&compnay=34&amount=1000' 'https://www.weconnect.com.pk/api/v2'```

### Request Ufone SuperCard599 for number 3301234567
```curl -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=2&mtype=4&amount=599&number=3301234567&compnay=33' 'https://www.weconnect.com.pk/api/v2'```

All the Wahid eLoad requests, their required parameters and company codes are defined here http://weconnect.com.pk/apicodes

# API Response
In response of your request you will get an XML reply of following format
```
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<response>
  <requestor wahid_id="0001" register_number="3001234567"/>
  <request name="weconnect" command="2"/>
  <message value="Your Current Balance: Rs 100 25/7/17 2:8 TID:31887882"/>
  <tid     value="31887882"/>
  <webid   value="875803"/>
  <balance value="250"/>
  <status  value="success" code="1"/>
</response>
```

In case of success, ```status``` value is set to ```success``` (code=1) and in case of failed request ```status``` is set to ```error``` (code=0). Exact reason of error is set in the ```error``` tag. For example

```
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<response>
  <requestor wahid_id="0001" register_number="3001234567"/>
  <request name="weconnect" command="2"/>
  <error value="ERROR: Sorry, Your Pin or Financial pin did not match." code="9999"/>
  <webid   value="875804"/>
  <status  value="error" code="0"/>
</response>
```

# Example C# CSharpe
```
using System;
using RestSharp;
using System.Collections.Generic;
					
public class Program
{
  public static string CreateMD5(string input)
  {
    using (System.Security.Cryptography.MD5 md5 = System.Security.Cryptography.MD5.Create())
    {
      byte[] inputBytes = System.Text.Encoding.ASCII.GetBytes(input);
      byte[] hashBytes = md5.ComputeHash(inputBytes);
      System.Text.StringBuilder sb = new System.Text.StringBuilder();
      for (int i = 0; i < hashBytes.Length; i++){sb.Append(hashBytes[i].ToString("x2"));}
      return sb.ToString();
    }
  }
  public static string WeConenct(Dictionary<string, string> data)
  {
    var user = "3001234567";
    var pin = "012345";
    TimeSpan t = DateTime.UtcNow - new DateTime(1970, 1, 1);
    var token = t.TotalSeconds.ToString();
    var apikey="abcdef1234567890";		
    var WahidToken = CreateMD5(token);
    var Authorization = CreateMD5("apikey="+ apikey + ";token="+WahidToken);
    var client = new RestClient("https://www.weconnect.com.pk/api/v2.php");
    client.Timeout = 120000; //120 sec
    var request = new RestRequest(Method.POST);
    request.AddHeader("Authorization", Authorization);
    request.AddHeader("WahidToken", WahidToken);
    request.AddParameter("user",     user, ParameterType.GetOrPost);
    request.AddParameter("pin",      pin,     ParameterType.GetOrPost);
    foreach( KeyValuePair<string, string> item in data )
    {
      request.AddParameter(item.Key,  item.Value, ParameterType.GetOrPost);
    }
    IRestResponse response = client.Execute(request);
    Console.WriteLine(response.Content);
    return response.Content;
  }

  public static void Main()
  {
    Dictionary<string, string> data = new Dictionary<string, string>();
    data.Add("request", "35");
    WeConenct(data);
    data.Clear();
    data.Add("request","2");
    data.Add("number", "3007654321");
    data.Add("amount","100");
    data.Add("company","30");
    data.Add("mtype","1");
    WeConenct(data);
  }
}
```
