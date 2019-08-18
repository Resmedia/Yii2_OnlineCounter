<?php
/**
 * Created by PhpStorm.
 * User: ResMedia
 * Date: 17.08.19
 * Time: 22:50
 *
 * @name OnlineCounter \yii\jui\Widget
 * @description This script considers the number of IP's that have visited the site in a certain time.
 * @param $pastTime integer Time in seconds to count visits, default 60 sec
 * @param $urlToData string Url to folder, default is /web/uploads/data
 * @param $urlToFile string Url to File default is /web/uploads/data/online.dat
 * @return string Count of users on site for the time
 *
 * Put it <?= OnlineCounter::widget() ?> to some where on layout
 */

namespace frontend\widgets;

use Yii;
use yii\bootstrap\Widget;
use yii\web\ForbiddenHttpException;

class OnlineCounter extends Widget
{
    public $urlToData = null;
    public $urlToFile = null;
    public $pastTime = 60;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        // Look folder an make if missing
        if (!is_dir($this->urlToData ?: Yii::getAlias('@frontend') . '/web/uploads/data')) {
            mkdir($this->urlToData ?: Yii::getAlias('@frontend') . '/web/uploads/data', 0755);
        }

        // Look DAT file an make if missing
        if (!$this->urlToFile ?: Yii::getAlias('@frontend') . '/web/uploads/data/online.dat') {
            touch($this->urlToFile ?: Yii::getAlias('@frontend') . '/web/uploads/data/online.dat');
            chmod($this->urlToFile ?: Yii::getAlias('@frontend') . '/web/uploads/data/online.dat', 0666);
        }

        $dataFile = Yii::getAlias('@frontend') . '/web/uploads/data/online.dat';
        $time = time();
        $pastTime = time() - $this->pastTime;
        $onlineArray = [];

        // Looking on user environments
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $newIP = getenv('HTTP_X_FORWARDED_FOR');
        } else {
            $newIP = getenv('REMOTE_ADDR');
        }

        // Try to read from file
        $readData = fopen($dataFile, "r");

        if (!$readData) {
            throw new ForbiddenHttpException('Read DATA Online forbidden');
        }

        // Put it to array
        $dataArray = file($dataFile);

        // close file
        fclose($readData);

        // Look count of items in file
        $data = count($dataArray);

        if ($data) {
            foreach ($dataArray as $key => $value) {
                list($dataIP, $lastTime) = explode('::', "$dataArray[$key]");

                // Check for the existence of an element
                if (isset($dataIP) && isset($lastTime)) {

                    // If there no new IP, add it to the array
                    if(!self::findIp($newIP, $dataArray)) {
                        array_push($onlineArray, "$newIP::$time\r\n");
                    }

                    // If the date is current and old IP is not present, add the old element to the new array
                    if(($lastTime > $pastTime) && !self::findIp($dataIP, $onlineArray)) {
                        array_push($onlineArray, $value);
                    }
                }
            }
        } else {
            // If array null write new user
            $onlineArray[] = "$newIP::$time\r\n";
        }

        // Open file to write
        $writeData = fopen($dataFile, "w");

        if (!$writeData) {
            throw new ForbiddenHttpException('Write DATA Online forbidden');
        }

        // receive exclusive lock or record
        flock($writeData, LOCK_EX);

        // Then put it to file
        foreach ($onlineArray as $str) {
            // wright to file new user
            fwrite($writeData, "$str");
        }

        // unlocking
        flock($writeData, LOCK_UN);

        // Close file
        fclose($writeData);

        // Read data
        $readData = fopen($dataFile, "r");

        if (!$readData) {
            throw new ForbiddenHttpException('Read DATA Online forbidden');
        }

        // Put all items to array
        $dataArray = file($dataFile);

        // Close
        fclose($readData);

        // Take online
        $online = count($dataArray);

        return 'На сайте ' . $online . ' ' . self::declension($online, ['человек', 'человека', 'человек']);
    }

    /**
     * @name findIp
     * @description Find IP in array of DATA items
     * @param $ip string IP element
     * @param $arr array of DATA items
     * @param $bool bool Default returns value
     * @return boolean
    */
    private function findIp(string $ip, array $arr, bool $bool = false): bool
    {
        foreach ($arr as $key => $value) {
            list($dataIP, $lastTime) = explode('::', "$arr[$key]");
            if($ip == $dataIP) {
                $bool = $ip == $dataIP;
            }
        }

        return $bool;
    }
    
    private static function declension($num, $words)
    {
        $num = $num % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1: {
                return($words[0]);
            }
            case 2: case 3: case 4: {
            return($words[1]);
        }
            default: {
                return($words[2]);
            }
        }

    }
}
