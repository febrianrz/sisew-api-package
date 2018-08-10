<?php
namespace App\Http\Libraries;

class GambarLibrary {

    public static function getGambar117($realPath){
        return self::resize($realPath,117);
    }

    public static function getGambar300($realPath){
        return self::resize($realPath,300);
    }

    private static function resize($realPath, $size){
        try{
            $path = self::getPath($realPath);
            $filePath = storage_path()."/app/".$realPath;
            
            $new_filename = $size."_".$path->file;
            $new_filepath = storage_path()."/app/".$path->path.$new_filename;
            return $path->path.$path->file;
            // $info = pathinfo($filePath);
                // $resize = \Image::make($filePath);
                //calculate md5 hash of encoded image
                // $hash = md5($resize->__toString());
                // \Storage::put($path->path.$new_filename, $resize->__toString());
                return $path->path.$new_filename;
        } catch(\Exception $e){
            dd($e);
            return $e->getMessage();
        }
        
    }

    public static function getPath($realPath){
        $arr    =  explode("/",$realPath);
        $path   =  "";
        $file   = $arr[count($arr)-1];
        for($i=0;$i<count($arr)-1;$i++){
            $path .= $arr[$i];
            $path .= "/";
            
        }
        return (object) [
            'path'  => $path,
            'file'  => $file
        ];
    }

}