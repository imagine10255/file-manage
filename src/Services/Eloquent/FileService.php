<?php
namespace Imagine10255\FileManage\Services\Eloquent;

use Fatansy\System\Models\File;
use Illuminate\Support\Facades\Storage;

/**
 * Class File
 * @package App\Services
 */
class FileService
{
    /**
     * 判斷mineType是否為圖片
     * @param $mineType
     * @return bool
     */
    public function isImage($mineType)
    {
        $list = ['image/jpeg'];
        return in_array($mineType,$list) ? true:false;
    }

    /**
     * 判斷mineType是否為影片
     * @param $mineType
     * @return bool
     */
    public function isVideo($mineType)
    {
        $list = ['video/x-flv', 'video/mp4', 'application/x-mpegURL', 'video/MP2T', 'video/3gpp', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];
        return in_array($mineType,$list) ? true:false;
    }


    /**
     * 依照檔案類型輸出(若有下載參數則直接下載)
     * @param $id
     * @param $prefixed (依檔案前輟檔名區別 ex:smart_220011123456.jpg | thumb_220011123456.jpg)
     * @param bool $download
     * @return null
     */
    public function response($id,$prefixed=null,$download=false)
    {
        $file = File::find($id);
        if(!isset($file)){
            abort(404);
        }

        ob_clean();
        if($download===true){
            $new_file = Storage::get($file->path.DIRECTORY_SEPARATOR.$prefixed.$file->real_name);
            $response = response($new_file, 200)
                ->header('Content-Type', 'application/force-download')
                ->header('Content-Disposition', 'attachment;filename=' . $file->original_name);
        }else{
            $full_path = storage_path('app').DIRECTORY_SEPARATOR.$file->path.DIRECTORY_SEPARATOR.$prefixed.$file->real_name;
            $new_file = fopen($full_path, "r");
            switch($file->mime_type){
                case 'image/jpeg':
                    $tmp_file ='';
                    while (!feof($new_file) and (connection_status() == 0)) {
                        $tmp_file .= (fread($new_file, 8192));
                        flush();
                    }
                    @fclose($new_file);
                    $response = response($tmp_file, 200)->header('Content-Type','image/jpeg');
                    break;
                case 'image/png':
                    $tmp_file = \Illuminate\Support\Facades\File::get($full_path);
                    $response = response($tmp_file, 200)->header('Content-Type','image/png');
                    break;
                case 'video/x-flv':
                case 'video/mp4':
                case 'application/x-mpegURL':
                case 'video/MP2T':
                case 'video/3gpp':
                case 'video/quicktime':
                case 'video/x-msvideo':
                case 'video/x-ms-wmv':
                    $tmp_file ='';
                    while (!feof($new_file) and (connection_status() == 0)) {
                        $tmp_file .= (fread($new_file, 8192));
                        flush();
                    }
                    @fclose($new_file);

                    $response = response($tmp_file, 200)
                        ->header('Content-Type', 'application/octet-stream')
                        ->header('Content-Type', 'video/mpeg4');
                    break;
                default:
                    echo '無法取得資源類型，請確認檔案 MineType 是否正確';
                    return null;
            }
        }
        return $response;
    }

    /**
     * 刪除檔案
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteFile($id)
    {
        $file = File::find($id);
        $prefix = json_decode($file->related_prefix,true);
        $prefix[] = '';
        foreach($prefix as $pre){
            $result = Storage::delete(DIRECTORY_SEPARATOR.$file->path.DIRECTORY_SEPARATOR.$pre.$file->real_name);
        }
        return $file->delete();
    }

    /**
     * 刪除關聯資料的全部檔案
     * @param $related_table
     * @param $related_id
     * @return mixed
     */
    public function deleteDir($related_table,$related_id)
    {
        $data = File::where('related_table',$related_table)->where('related_id',$related_id);
        $first = $data->first();
        if(!empty($first) && $first->path){
            $result = Storage::deleteDirectory($first->path);
            return $data->delete();
        }else{
            return false;
        }
    }


    /**
     * 產生日期路徑
     * @param $path
     * @param $id
     * @return bool
     */
    public function createDateDir($path,$id=null)
    {
        $id = $id!=null ? DIRECTORY_SEPARATOR.$id : '';
        $path = $path . DIRECTORY_SEPARATOR . date('Y/m/d') . $id;
        $action = $this->checkDir($path, false, true);
        return $action ? storage_path('app' . DIRECTORY_SEPARATOR . $action) : false;
    }


    /**
     * 檢查資料夾是否存在
     * @param string $dir 檢查路徑
     * @param bool|false $file 是否包含 File Name(自動去除最後一個路徑)
     * @param bool|false $create 不存在是否自動產生資料夾(包含子層)
     * @return bool 返回是否存在的結果
     */
    public function checkDir($dir, $file = false, $create = false)
    {
        if ($file) {
            $arr = explode('/', $dir);
            unset($arr[count($arr) - 1]);
            $dir = implode('/', $arr);
        }

        if (!Storage::has($dir)) {
            if ($create && Storage::makeDirectory($dir)) {
                return $dir;
            } else {
                return null;
            }
        } else {
            return $dir;
        }
    }

}