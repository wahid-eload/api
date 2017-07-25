# Wahid eLoad API Client
This php client version 2 (and above) implements the following Wahid eLoad API. In order to use the client see the documentation provided http://weconnect.com.pk/apidoc . Following is the detail of Wahid eLoad API in case you need to implement our API differently.

# Wahid eLoad API
All Wahid eLoad API access is over HTTPS, and accessed from the https://www.weconnet.com.pk/api/v2 
All Requests need to POST follow required parameters along with any paramemters needed for request

| Parameter | Value |
|:--------:|:---------------:|
| user     | Your register number |
| request  | request number |
| pin      | your 4 to 6 digit Wahid PIN |

For authentication, you need to Include Following headers in your HTTPS request

| Header | Value
|:-----:|:--------|
| WahidToken | MD5 of a unique string |e a unique 
| Authorization | MD5 of string **apikey**=*api-key*;**token**=*WahidToekn* |

# Example
Lets assume your register number is ```3001234567```, your Wahid PIN is ```012345``` and API key is ```abcdef1234567890```. For each request we need to have unique ```WahidToken```. Easiest is to use the EPOCH time ( https://www.epochconverter.com/ ). Lets assume current EPOCH time is 1500928932 so our ```WahidToken``` will be *md5(1500928932)=*```4c65f3d505f44512900ed14cae71b2da``` and our ```Authorization``` is *md5(apikey=abcdef1234567890;token=4c65f3d505f44512900ed14cae71b2da)*=```6d9dc22ec4feebfe37dcfb1d87f1cccf```

You also need to download https://github.com/wahid-eload/api/blob/master/weconnect/api/CA-cert.pem 

Now to make API request using curl client we can do the following

## Balance Checking
```curl --cacert CA-cert.pem -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=2' 'https://www.weconnect.com.pk/api/v2'```

## Pre-paid load request
### Request pre-paid Mobilink load of Rs 100 for number 3007654321
```curl --cacert CA-cert.pem -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=1&number=3007654321&compnay=30&amount=100' 'https://www.weconnect.com.pk/api/v2'```

### Request post-paid Telenor load of Rs 1000 for number 3127654321
```curl --cacert CA-cert.pem -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=9&number=3127654321&compnay=34&amount=1000' 'https://www.weconnect.com.pk/api/v2'```

### Request Ufone SuperCard599 for number 3301234567
```curl --cacert CA-cert.pem -H 'Authorization: 6d9dc22ec4feebfe37dcfb1d87f1cccf' -H 'WahidToken: 4c65f3d505f44512900ed14cae71b2da' --data 'user=3001234567&pin=012345&request=8&rtype=4&number=3301234567&compnay=33' 'https://www.weconnect.com.pk/api/v2'```

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
