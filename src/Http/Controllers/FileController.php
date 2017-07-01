<?php
namespace Imagine10255\FileManage\Http\Controllers;

use App\Http\Controllers\Controller;
use Imagine10255\FileManage\Models\File;
use Imagine10255\FileManage\Services\Eloquent\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * 檔案管理
 * Class SignController
 * @package Smart\Controller
 */
class FileController extends Controller
{
    protected $fileService;
    private $sid = '';
    private $column = '';
    private $type = '';
    private $table = '';
    private $path = '';

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }


    /**
     * Jquery-file-upload 檔案上傳後端功能
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $related_id = $request->input('related_id',null);
        $related_table = $request->input('related_table',null);

        if ($related_id && $related_table && $request->hasFile('file')) {
            //以自訂關聯表名的底線改為路徑
            $dir = str_replace('_','/',$related_table);

            //建立資料夾
            $path = $this->fileService->createDateDir($dir,$related_id);

            $dd = $request->file('file');
            //建立系統檔名
            $extend = '.' . $dd[0]->getClientOriginalExtension();
            $real_name = timeKey(6,true) . $extend;

            //檔案上傳
            $new_file = $dd[0]->move($path, $real_name);
            $save_path = str_replace(storage_path('app').DIRECTORY_SEPARATOR,'',$new_file->getPath());

            $related_prefix = null;
            //其他需求(縮圖)
            if($request->has('tools')){
                $tools = json_decode($request['tools'],true);


                if($this->fileService->isImage($new_file->getMimeType())){
                    //系統使用
                    $img = Image::make($new_file);
                    $img->fit(48,48);
                    $img->save(storage_path('app').DIRECTORY_SEPARATOR.$save_path.DIRECTORY_SEPARATOR.'smart_'.$real_name);
                    unset($img);

                    //加入到前綴列表
                    $related_prefix[] = 'smart_';

                    //客製化需要的縮圖
                    if(isset($tools['image_resize'])){
                        $image_resize = $tools['image_resize'];

                        foreach($image_resize as $prefix=>$row){

                            //除了空白以外(因空白為原圖縮小),加入到前綴列表
                            if(!empty($prefix)){
                                $related_prefix[] = $prefix;
                            }

                            $img = Image::make($new_file);
                            $width = isset($row[0]) ? $row[0]:null;
                            $height = isset($row[1]) ? $row[1]:null;

                            //長方形(寬大於高),則以寬為等比例
                            if($img->width() > $img->height()){
                                if ($width < $img->width() ){
                                    $height=$img->height()/$img->width()*$width;
                                }
                            }else{
                                if ($height < $img->height() ){
                                    $width=$img->width()/$img->height()*$height;
                                }
                            }

                            //自定義圖片大小
                            $img->resize($width, $height);
                            $img->save(storage_path('app').DIRECTORY_SEPARATOR.$save_path.DIRECTORY_SEPARATOR.$prefix.$real_name);
                            unset($img);
                        }
                    }
                }
            }

            //新增到資料庫
            $model = File::create([
                'related_table'=>$related_table,
                'related_id'=>$related_id,
                'related_prefix'=>json_encode($related_prefix),
                'real_name'=>$real_name,
                'path'=>$save_path,
                'mime_type'=>$new_file->getMimeType(),
                'size'=>$new_file->getSize(),
                'original_name'=>$dd[0]->getClientOriginalName(),
                'outline'=>' ',
                'sort'=>0
            ]);

            return response()->json(array('files'=>[$model]));
        } else {
            return response()->json($this->update_return(false));
        }
    }


    /**
     * 刪除單一檔案
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Request $request)
    {
        if($request->has('id')){
            if($this->fileService->deleteFile($request->get('id'))){
                return response('true');
            }else{
                return response('false');
            }
        }
        return response('false');
    }

    /**
     * Jquery Sortable
     * @return string
     * @throws Exception
     */
    public function post_sortable(){
        $class = Input::post('func');

        $this->sid = Input::post('sid');
        $this->column = Input::post('column');
        $this->table = call_user_func('Model\\'.$class.'::table');
        $sort = Input::post('sort');

        try
        {
            //取出json解析後插入回存
            DB::start_transaction();
            $row = \DB::query(\DB::select($this->column)->from($this->table)->where('sid',$this->sid).' for update')->execute()->current();
            $data = (array)json_decode($row[$this->column],true);
            $news_data = \Arr::custom_sort($data,$sort,'sort_');
            if(DB::update($this->table)->set(array($this->column=>json_encode($news_data)))->where('sid', $this->sid)->execute()){
                DB::commit_transaction();
                return true;
            }else{
                return json_encode(array('result'=>'error','msg'=>i18n('info.database_update_fail')));
            }
        }
        catch (Exception $e)
        {
            DB::rollback_transaction();
            throw $e;
        }
    }

    /**
     * JQuery Editable
     * @return bool|string
     * @throws Exception
     */
    public function post_editable()
    {
        $class = Input::get('func');

        $this->sid = Input::get('sid');
        $this->column = Input::get('column');
        $this->table = call_user_func('Model\\'.$class.'::table');

        $file = Input::post('pk');
        $name = Input::post('name');
        $value = Input::post('value');

        if($this->sid  && $this->column && $file && $name){
            try
            {
                //取出json解析後插入回存
                DB::start_transaction();
                $row = \DB::query(\DB::select($this->column)->from($this->table)->where('sid',$this->sid).' for update')->execute()->current();
                $arr = (array)json_decode($row[$this->column],true);
                $search = explode('.',Arr::search($arr,$file));
                $arr[$search[0]][$name] = $value;
//                sort($arr);
                if(DB::update($this->table)->set(array($this->column=>json_encode($arr)))->where('sid', $this->sid)->execute()){
                    DB::commit_transaction();
                    return true;
                }else{
                    return json_encode(array('result'=>'error','msg'=>i18n('info.database_update_fail')));
                }
            }
            catch (Exception $e)
            {
                DB::rollback_transaction();
                throw $e;
            }

        }
        return false;
    }

    /**
     * Crop Doing
     * @return bool
     */
    public function post_do_crop1()
    {
        $form = Arr::assoc_to_keyval(Input::post('form'),'name','value');

        $scale = explode('x',$form['scale']);

        $img_r = imagecreatefromjpeg(DOCROOT.$form['path']);
        $dst_r = ImageCreateTrueColor($scale[0], $scale[1]);

        imagecopyresampled($dst_r,$img_r,0,0
            ,$form['x'],$form['y']
            ,$scale[0],$scale[1]
            ,$form['w'],$form['h']);

        imagejpeg($dst_r, DOCROOT.$form['path'], 100);
        return true;
    }
    /**
     * Crop Doing
     * @return bool
     */
    public function post_do_crop()
    {
        $need = Input::post('need_size');
        $need_size = explode('x',$need);


        $name = sha1(uniqid(mt_rand(), true));
        // location to save cropped image
        $url = DOCROOT.$_POST['image'];

        $dst_x = 0;
        $dst_y = 0;

        $src_x = $_POST['x']; // crop Start x
        $src_y = $_POST['y']; // crop Srart y

        $src_w = $need_size[0]; // $src_x + $dst_w
        $src_h = $need_size[1]; // $src_y + $dst_h
        // set a specific size for the image
        // the default is to grab the width and height from the cropped imagee.
        $dst_w = $need_size[0];//240;
        $dst_h = $need_size[1];//240;
        // remove the base64 part
        $pos = strpos($_POST['image'],'?');
        if($pos==null){
            $base64 = DOCROOT.$_POST['image'];
        }else{
            $base64 = DOCROOT.substr($_POST['image'],0,$pos);
        }
        // if URL is a base64 string
        if (substr($base64, 0, 5) == 'data:') {
            // remove data from image
            $base64 = preg_replace('#^data:image/[^;]+;base64,#', '', $base64);
            $base64 = base64_decode($base64);
            // create image from string
            $source = imagecreatefromstring($base64);
        }
        else {
            // strip parameters from URL
            $base64 = strtok($base64, '?');
            list($height, $width, $type) = getimagesize($base64);
            // create image
            if ($type == 1)
                $source = imagecreatefromgif($base64);
            else if ($type == 2)
                $source = imagecreatefromjpeg($base64);
            else if ($type == 3) {
                $source = imagecreatefrompng($base64);

                // keep transparent background
                imagealphablending($_POST['image'], FALSE);
                imagesavealpha($_POST['image'], TRUE);

            }
            else die();
        }
        // resize image variable
        $image = imagecreatetruecolor($dst_w, $dst_h);
        // process cropping and resizing of image
        imagecopyresampled($image, $source, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        // save image
        imagejpeg($image, $base64, 100);
        // return URL
//        $validation = array (
//            'url'     => $url
//        );
//        echo json_encode($validation);
        return true;
    }


    /**
     * Crop Template
     * @return string
     */
    public function post_crop()
    {
        if(!Input::post('sid')) return "參數錯誤";

        $view = View::forge('smart/template/jquery-img-crop');
        $sid = Input::post('sid');
        $sizes = json_decode(Input::post('sizes'),true);
        $default_size = explode('x',$sizes[0]);

        $path = Input::post('path');
        $view->set('sid',$sid);
        $view->set('sizes',$sizes);
        $view->set('default_height',$default_size[1]);
        $view->set('default_width',$default_size[0]);
        $view->set('path',$path);
        return Response::forge($view);
    }



    /**
     * 取得檔案內容並下載
     * @param $id
     * @return FileController
     */
    public function getDownload(Request $request,$id)
    {
        $file = new FileService();
        return $file->response($id,$request->input('type',null),true);
    }

    /**
     * 取得檔案內容並輸出
     * @param $id
     * @return FileController
     */
    public function getOutput(Request $request,$id)
    {
        $file = new FileService();
        return $file->response($id,$request->input('prefixed',null));
    }

    public function getRelatedOutput(Request $request,$related_table,$related_id)
    {
        $data = File::where('related_table',$related_table)->where('related_id',$related_id)->first();
        $file = new FileService();
        return $file->response($data->id,$request->input('prefixed',null));
    }

    /**
     * 以關聯序號取得檔案資訊
     * @param $related_table
     * @param $related_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRelated($related_table,$related_id)
    {
        $file = File::where('related_table',$related_table)->where('related_id',$related_id)->get();
        return response()->json($file);
    }


}