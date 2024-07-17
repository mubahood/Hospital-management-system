<?php

namespace App\Models;

use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'administrator_id',
        'src',
        'thumbnail',
        'parent_id',
        'size',
        'deleted_at',
        'type',
        'product_id',
    ];

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($m) {

            $src = Utils::docs_root() . "/storage/images/" . $m->src;

            if ($m->thumbnail != null) {
                if (strlen($m->thumbnail) > 2) {
                    $thumb = Utils::docs_root() . "/storage/images/" . $m->thumbnail;
                }
            }
            if (!isset($thumb)) {
                $thumb =  Utils::docs_root() . "/storage/images/thumb_" . $m->src;
            }

            if (file_exists($src)) {
                unlink($src);
            }
            if (file_exists($thumb)) {
                unlink($thumb);
            }
        });

        self::created(function ($m) {
            $m->create_thumbail();
        });
    }

    public function getSrcAttribute($src)
    {

        $source = Utils::docs_root() . "/storage/images/" . $src;
        if (!file_exists($source)) {
            return 'logo.png';
        }
        return $src;
    }
    public function getThumbnailAttribute($src)
    {

        $source = Utils::docs_root() . "/storage/images/" . $src;
        if (!file_exists($source)) {
            return 'logo.png';
        }
        return $src;
    }

    public function create_thumbail()
    {
        set_time_limit(-1);

        $src = $this->src;
        $source = Utils::docs_root() . "/storage/images/" . $this->src;
        if (!file_exists($source)) {
            $this->delete();
            return;
        }
        $target = Utils::docs_root() . "/storage/images/thumb_" . $this->src;

        Utils::create_thumbail([
            'source' => $source,
            'target' => $target
        ]);

        if (file_exists($target)) {
            $this->thumbnail = "thumb_" . $this->src;
            $this->save();
        }
    }

    
    public function getUpdatedAtTextAttribute(){
        return Carbon::parse($this->updated_at)->timestamp;
    } 
    protected $appends = ['updated_at_text']; 
}
