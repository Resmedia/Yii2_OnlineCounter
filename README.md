# Yii2 OnlineCounter V 0.1

Online counter of visitors for a certain time without using database
Counts new users, cleans up-to-date time data

Add php file to widgets add params 

```
$pastTime integer Time in seconds to count visits, default 60 sec
$urlToData string Url to folder, default is /web/uploads/data
$urlToFile string Url to File default is /web/uploads/data/online.dat
```

Put it `<?= OnlineCounter::widget() ?>` to some were on layout

P.S. If someone simplifies all this, i will be glad to add or change this code
