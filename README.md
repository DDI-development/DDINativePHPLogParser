php-log-parser
==============
You can print a report about the usage of an Apache web server, based on the access logs.
To do that we built a simple page including a form that will allow you to upload a log file.

When the file is uploaded, the script will analyze its contents and print the following:
- number of hits / day (for each day)
- number of unique visitors / day (for each day)
- average number of hits / visitor (for the whole period)

Log format:
192.168.1.1 - - [16/Feb/2014:06:44:00 +0000] "GET /images/icon.png HTTP/1.1" 200 1331 "http://www.example.com/index.html" "Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11B554a Safari/9537.53"


There is not use of Javascript or CSS, just PHP/HTML page (in core PHP, no framework).
