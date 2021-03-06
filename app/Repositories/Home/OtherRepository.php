<?php
namespace App\Repositories\Home;

use App\Models\Course;
use App\Models\Content;
use App\Models\Communication;
use App\Models\Pivot_User_Course;
use App\Models\Pivot_User_Collection;

use App\Repositories\Common\CommonRepository;

use Response, Auth, Validator, DB, Exception;
use QrCode;
use Symfony\Component\Console\Helper\Table;

class OtherRepository {

    private $model;
    public function __construct()
    {
    }

    public function index()
    {
        return view('home.index');
    }



    // 返回【收藏】【课程】列表数据
    public function collect_course_get_list_datatable($post_data)
    {
        $user = Auth::user();
        $query = Pivot_User_Collection::with([
                'course'=>function($query) { $query->with(['user']); }
            ])->where(['type'=>1,'user_id'=>$user->id,'content_id'=>0]);
        $total = $query->count();

        $draw  = isset($post_data['draw'])  ? $post_data['draw']  : 1;
        $skip  = isset($post_data['start'])  ? $post_data['start']  : 0;
        $limit = isset($post_data['length']) ? $post_data['length'] : 20;

        if(isset($post_data['order']))
        {
            $columns = $post_data['columns'];
            $order = $post_data['order'][0];
            $order_column = $order['column'];
            $order_dir = $order['dir'];

            $field = $columns[$order_column]["data"];
            $query->orderBy($field, $order_dir);
        }
        else $query->orderBy("id", "desc");

        if($limit == -1) $list = $query->get();
        else $list = $query->skip($skip)->take($limit)->get();

        foreach ($list as $k => $v)
        {
            $list[$k]->encode_id = encode($v->id);
            if($list[$k]->course)
            {
                $list[$k]->course->encode_id = encode($v->course->id);
                $list[$k]->course->user->encode_id = encode($v->course->user->id);
            }
        }
        return datatable_response($list, $draw, $total);
    }
    // 删除【收藏】【课程】
    public function collect_course_delete($post_data)
    {
        $user = Auth::user();
        $id = decode($post_data["id"]);
        if(intval($id) !== 0 && !$id) return response_error([],"该课程不存在，刷新页面试试");

        $collection = Pivot_User_Collection::find($id);
        if($collection->user_id != $user->id) return response_error([],"你没有操作权限");

        DB::beginTransaction();
        try
        {
            $course_id = $collection->course_id;
            $course = Course::find($course_id);
            if($course)
            {
                $course->decrement('collect_num');
            }

            $bool = $collection->delete();
            if(!$bool) throw new Exception("delete--collection--fail");

            DB::commit();
            return response_success([]);
        }
        catch (Exception $e)
        {
            DB::rollback();
            return response_fail([],'删除失败，请重试');
        }

    }



    // 返回【收藏】【章节】列表数据
    public function collect_chapter_get_list_datatable($post_data)
    {
        $user = Auth::user();
        $query = Pivot_User_Collection::with([
            'chapter'=>function($query) { $query->with(['user','course']); }
        ])->where(['type'=>1,'user_id'=>$user->id])->where('content_id','<>',0);
        $total = $query->count();

        $draw  = isset($post_data['draw'])  ? $post_data['draw']  : 1;
        $skip  = isset($post_data['start'])  ? $post_data['start']  : 0;
        $limit = isset($post_data['length']) ? $post_data['length'] : 20;

        if(isset($post_data['order']))
        {
            $columns = $post_data['columns'];
            $order = $post_data['order'][0];
            $order_column = $order['column'];
            $order_dir = $order['dir'];

            $field = $columns[$order_column]["data"];
            $query->orderBy($field, $order_dir);
        }
        else $query->orderBy("id", "desc");

        if($limit == -1) $list = $query->get();
        else $list = $query->skip($skip)->take($limit)->get();

        foreach ($list as $k => $v)
        {
            $list[$k]->encode_id = encode($v->id);
            if($list[$k]->chapter)
            {
                $list[$k]->chapter->encode_id = encode($v->chapter->id);
                $list[$k]->chapter->course->encode_id = encode($v->chapter->course->id);
                $list[$k]->chapter->user->encode_id = encode($v->chapter->user->id);
            }
        }
        return datatable_response($list, $draw, $total);
    }
    // 删除【收藏】【章节】
    public function collect_chapter_delete($post_data)
    {
        $user = Auth::user();
        $id = decode($post_data["id"]);
        if(intval($id) !== 0 && !$id) return response_error([],"该课程不存在，刷新页面试试");

        $collection = Pivot_User_Collection::find($id);
        if($collection->user_id != $user->id) return response_error([],"你没有操作权限");

        DB::beginTransaction();
        try
        {
            $content_id = $collection->content_id;
            $content = Content::find($content_id);
            if($content)
            {
                $content->decrement('collect_num');
            }

            $bool = $collection->delete();
            if(!$bool) throw new Exception("delete--collection--fail");

            DB::commit();
            return response_success([]);
        }
        catch (Exception $e)
        {
            DB::rollback();
            return response_fail([],'删除失败，请重试');
        }

    }




    // 返回【点赞】【课程】列表数据
    public function favor_course_get_list_datatable($post_data)
    {
        $user = Auth::user();
        $query = Pivot_User_Course::with([
            'course'=>function($query) { $query->with(['user']); }
        ])->where(['type'=>1,'user_id'=>$user->id,'content_id'=>0]);
        $total = $query->count();

        $draw  = isset($post_data['draw'])  ? $post_data['draw']  : 1;
        $skip  = isset($post_data['start'])  ? $post_data['start']  : 0;
        $limit = isset($post_data['length']) ? $post_data['length'] : 20;

        if(isset($post_data['order']))
        {
            $columns = $post_data['columns'];
            $order = $post_data['order'][0];
            $order_column = $order['column'];
            $order_dir = $order['dir'];

            $field = $columns[$order_column]["data"];
            $query->orderBy($field, $order_dir);
        }
        else $query->orderBy("id", "desc");

        if($limit == -1) $list = $query->get();
        else $list = $query->skip($skip)->take($limit)->get();

        foreach ($list as $k => $v)
        {
            $list[$k]->encode_id = encode($v->id);
            if($list[$k]->course)
            {
                $list[$k]->course->encode_id = encode($v->course->id);
                $list[$k]->course->user->encode_id = encode($v->course->user->id);
            }
        }
        return datatable_response($list, $draw, $total);
    }
    // 删除【点赞】【课程】
    public function favor_course_delete($post_data)
    {
        $user = Auth::user();
        $id = decode($post_data["id"]);
        if(intval($id) !== 0 && !$id) return response_error([],"该课程不存在，刷新页面试试");

        $other = Pivot_User_Course::find($id);
        if($other->user_id != $user->id) return response_error([],"你没有操作权限");

        DB::beginTransaction();
        try
        {
            $course_id = $other->course_id;
            $course = Course::find($course_id);
            if($course)
            {
                $course->decrement('favor_num');
            }

            $bool = $other->delete();
            if(!$bool) throw new Exception("delete--other--fail");

            DB::commit();
            return response_success([]);
        }
        catch (Exception $e)
        {
            DB::rollback();
            return response_fail([],'删除失败，请重试');
        }

    }


    // 返回【点赞】【课程】列表数据
    public function favor_chapter_get_list_datatable($post_data)
    {
        $user = Auth::user();
        $query = Pivot_User_Course::with([
            'chapter'=>function($query) { $query->with(['user','course']); }
        ])->where(['type'=>1,'user_id'=>$user->id])->where('content_id','<>',0);
        $total = $query->count();

        $draw  = isset($post_data['draw'])  ? $post_data['draw']  : 1;
        $skip  = isset($post_data['start'])  ? $post_data['start']  : 0;
        $limit = isset($post_data['length']) ? $post_data['length'] : 20;

        if(isset($post_data['order']))
        {
            $columns = $post_data['columns'];
            $order = $post_data['order'][0];
            $order_column = $order['column'];
            $order_dir = $order['dir'];

            $field = $columns[$order_column]["data"];
            $query->orderBy($field, $order_dir);
        }
        else $query->orderBy("id", "desc");

        if($limit == -1) $list = $query->get();
        else $list = $query->skip($skip)->take($limit)->get();

        foreach ($list as $k => $v)
        {
            $list[$k]->encode_id = encode($v->id);
            if($list[$k]->chapter)
            {
                $list[$k]->chapter->encode_id = encode($v->chapter->id);
                $list[$k]->chapter->course->encode_id = encode($v->chapter->course->id);
                $list[$k]->chapter->user->encode_id = encode($v->chapter->user->id);
            }
        }
        return datatable_response($list, $draw, $total);
    }
    // 删除【点赞】【课程】
    public function favor_chapter_delete($post_data)
    {
        $user = Auth::user();
        $id = decode($post_data["id"]);
        if(intval($id) !== 0 && !$id) return response_error([],"该课程不存在，刷新页面试试");

        $other = Pivot_User_Course::find($id);
        if($other->user_id != $user->id) return response_error([],"你没有操作权限");

        DB::beginTransaction();
        try
        {
            $content_id = $other->content_id;
            $content = Content::find($content_id);
            if($content)
            {
                $content->decrement('favor_num');
            }

            $bool = $other->delete();
            if(!$bool) throw new Exception("delete--other--fail");

            DB::commit();
            return response_success([]);
        }
        catch (Exception $e)
        {
            DB::rollback();
            return response_fail([],'删除失败，请重试');
        }

    }




}