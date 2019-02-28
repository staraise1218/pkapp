<?php
/**
 * 文件上传类
 */

namespace app\api\logic;
use think\Db;
use think\Controller;
use think\Image;

class FileLogic extends Controller {

	// 多文件上传
	public function uploadMultiFile($field='file', $uploadPath){
		if (!file_exists($uploadPath)){
            mkdir($uploadPath, 0777, true);
        }

		$image = array();
		$files = $this->request->file($field);
        foreach ($files as $file) {
	        $info = $file->move($uploadPath, true);
	        if($info){
                $parentDir = '/'.date('Ymd'); // 系统默认在上传目录下创建了日期目录
                $fullPath = '/'.$uploadPath.$parentDir.'/'.$info->getFilename();
                $image[] = $fullPath;

                $suffix = substr(strrchr($fullPath, '.'), 1);
                if(in_array($suffix, array('jpeg', 'jpg', 'gif', 'png', 'bmp'))){
                    // 图片压缩
                    $origin_path = '.'.$fullPath;
                    $ImageObj = \think\Image::open($origin_path);
                    $ImageObj->save($origin_path, null, 60);
                }
            }   
        }
        return array('status' => '1', 'image'=>$image);
	}

	// 单一文件上传
	public function uploadSingleFile($field='file', $uploadPath){
		if (!file_exists($uploadPath)){
            mkdir($uploadPath, 0777, true);
        }

		$file = $this->request->file($field);
        $info = $file->move($uploadPath, true);
        if($info){
            $parentDir = '/'.date('Ymd'); // 系统默认在上传目录下创建了日期目录
            $fullPath = '/'.$uploadPath.$parentDir.'/'.$info->getFilename();

            $suffix = substr(strrchr($fullPath, '.'), 1);
            if(in_array($suffix, array('jpeg', 'jpg', 'gif', 'png', 'bmp'))){
                // 图片压缩
                $origin_path = '.'.$fullPath;
                $ImageObj = \think\Image::open($origin_path);
                $ImageObj->save($origin_path, null, 60);
            }


            return array('status'=>1, 'fullPath'=>$fullPath);
        } else {
        	return array('status' => -1);
        }
	}

    public function video2thumb($video_url){
        vendor('Alchemy.BinaryDriver.Exception.ExceptionInterface');
        vendor('Alchemy.BinaryDriver.Exception.ExecutableNotFoundException');
        vendor('Alchemy.BinaryDriver.ProcessRunnerInterface');
        vendor('Alchemy.BinaryDriver.Listeners.ListenerInterface');
        vendor('Alchemy.BinaryDriver.Listeners.Listeners');
        vendor('Alchemy.BinaryDriver.ProcessRunner');
        vendor('Alchemy.BinaryDriver.ProcessBuilderFactoryInterface');
        vendor('Alchemy.BinaryDriver.ProcessBuilderFactory');
        vendor('Alchemy.BinaryDriver.ConfigurationInterface');
        vendor('Alchemy.BinaryDriver.Configuration');
        vendor('Alchemy.BinaryDriver.EventEmitterInterface');
        vendor('Alchemy.BinaryDriver.ProcessRunnerAwareInterface');
        vendor('Alchemy.BinaryDriver.ProcessBuilderFactoryAwareInterface');
        vendor('Alchemy.BinaryDriver.ConfigurationAwareInterface');
        vendor('Alchemy.BinaryDriver.BinaryInterface');
        vendor('Alchemy.BinaryDriver.AbstractBinary');
        vendor('Doctrine.Common.Cache.MultiPutCache');
        vendor('Doctrine.Common.Cache.MultiGetCache');
        vendor('Doctrine.Common.Cache.ClearableCache');
        vendor('Doctrine.Common.Cache.FlushableCache');
        vendor('Doctrine.Common.Cache.Cache');
        vendor('Doctrine.Common.Cache.CacheProvider');
        vendor('Doctrine.Common.Cache.ArrayCache');
         // 创建视频缩略图
         $ffmpeg = \FFMpeg\FFMpeg::create(array(
             // 'ffmpeg.binaries'  => VENDOR_PATH.'php-ffmpeg/bin/ffmpeg.exe',
             // 'ffprobe.binaries' => VENDOR_PATH.'php-ffmpeg/bin/ffprobe.exe',
             'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
             'ffprobe.binaries' => '/usr/bin/ffprobe',
         ));

         $videoObj = $ffmpeg->open(ROOT_PATH.$video_url);
         $pos = strpos($video_url, '.');
         $video_thumb = substr($video_url, 0, $pos).'.jpg';
         $videoObj->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(0))
                ->save(ROOT_PATH.$video_thumb);

        return $video_thumb;
    }

    public function thumb($filepath, $width=150, $height=150, $type=1){
        $origin_path = '.'.$filepath;
        $ImageObj = \think\Image::open($origin_path);
        $ImageObj->save($origin_path);
    }
}