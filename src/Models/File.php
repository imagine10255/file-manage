<?php

namespace Imagine10255\FileManage\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 檔案模型
 * Class File
 *
 * @package Fatansy\System\Models
 * @property int $id 檔案ID
 * @property string $related_table 關聯資料表
 * @property int $related_id 關聯資料序號
 * @property string $related_prefix 前綴(Json)
 * @property string $real_name 真實檔案名稱
 * @property string $path 基礎檔案路徑
 * @property string $mime_type 副檔案類型
 * @property int $size 檔案大小
 * @property string $original_name 顯示名稱
 * @property string $outline 檔案簡介
 * @property bool $sort 檔案順序
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereMimeType($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereOriginalName($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereOutline($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File wherePath($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereRealName($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereRelatedId($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereRelatedPrefix($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereRelatedTable($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereSize($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereSort($value)
 * @method static \Illuminate\Database\Query\Builder|\Fatansy\System\Models\File whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class File extends Model
{

    /**
     * {@inheritDoc}
     */
    protected $table = 'system_files';

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $hidden = [];

    /**
     * 回傳檔案路徑
     * @return string
     */
    protected function fullPathFile()
    {
        return $this->path.DIRECTORY_SEPARATOR.$this->real_name;
    }

}
