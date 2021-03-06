<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    protected $table = "courses";
    protected $fillable = [
        'sort', 'type', 'active', 'user_id', 'title', 'description', 'content', 'cover_pic',
        'is_shared', 'visit_num', 'share_num'
    ];
    protected $dateFormat = 'U';
//    protected $dates = ['created_at', 'updated_at', 'disabled_at'];

    public function getDates()
    {
        return array('created_at','updated_at');
//        return array(); // 原形返回；
    }


    // 管理员
    function user()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    // 内容
    function contents()
    {
        return $this->hasMany('App\Models\Content','course_id','id');
    }

    // 评论
    function communications()
    {
        return $this->hasMany('App\Models\Communication','course_id','id');
    }

    // 评论
    function comments()
    {
        return $this->hasMany('App\Models\Communication','course_id','id');
    }

    // 收藏
    function collections()
    {
        return $this->hasMany('App\Models\Pivot_User_Collection','course_id','id');
    }

    // 其他人的
    function others()
    {
        return $this->hasMany('App\Models\Pivot_User_Course','course_id','id');
    }

    // 与我相关的话题
    function pivot_collection_course_users()
    {
        return $this->belongsToMany('App\User','pivot_user_collection','course_id','user_id');
    }

    // 与我相关的话题
    function pivot_collection_chapter_users()
    {
        return $this->belongsToMany('App\User','pivot_user_collection','content_id','user_id');
    }

    /**
     * 获得此人的所有标签。
     */
    public function tags()
    {
        return $this->morphToMany('App\Models\Tag', 'taggable');
    }




}
