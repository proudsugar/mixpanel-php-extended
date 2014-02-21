MixpanelExtended.php
=====================

Extensions for the Mixpanel PHP library. Provides cookie and reserved properties parsing. Most methods reflect their counterpart implementation in Mixpanel.js.

NOTE: This class can be used in production, but coverage is basic. More PHPUnit test cases are underway. Use at your own wisdom.

``` php
os(); // returns Operating System string.
```
``` php
device() // returns mobile device or empty string.
```
``` php
browser() // returns browser string or empty string.
```
``` php
referringDomain(); // returns referer domain.
```
``` php
searchInfo(); // returns $search_engine and mp_keyword based on referer.
```
``` php
campaignParams(); // returns utm_* google params based on referer.
```
``` php
getProperties(BOOL); // returns an array with all reserved properties available
```
– The Proudsugar Team.
