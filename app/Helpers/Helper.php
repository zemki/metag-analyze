<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

class Helper
{

    /**
     * Generate random string with a given set of chars
     * @param $length
     * @param string $keyspace
     * @return string
     * @throws \Exception
     */
    public static function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$%()=-*.,')
    {
          $pieces = [];
          $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
          return implode('', $pieces);
    }

    /**
     * Save and encrypt an image with custom extension
     * @param  [type] $entity [description]
     * @param  [type] $name   [description]
     * @param  [type] $type   [description]
     * @return String         path where the image is saved
     */
    public static function encrypt_image($entity, $name, $image, $type)
    {

                $name =  $t['name'];
                $arr = explode(",", $t['base64'], 2);
                $base64firstpart = $arr[0];


                // open file a image resource
                \Image::make($image)->fit(100, 100)->save(storage_path('app/tokens/'.$name));
                $path = storage_path('app/tokens/'.$name);

                $encryptedContent = encrypt($base64firstpart.",".base64_encode(file_get_contents($path)));

                // Store the encrypted Content
                Storage::put('tokens/'.$name.'.mtoken', $encryptedContent);
                File::delete($path);
    }

    /**
     * return extension of base64 image
     * @param  String $uri base64 image uri
     * @return String      image extension
     */
    public static function extension($uri)
    {
        $img = explode(',', $uri);
        $ini =substr($img[0], 11);
        $type = explode(';', $ini);
        return $type[0];
    }
}
