<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Profile;
// Logのために下記追加（LogとCarbon）
use App\Models\Log;

use Carbon\Carbon;

class ProfileController extends Controller
{
    // 以下を追記
    public function add()
    {
        return view('admin.profile.create');
    }
    public function create(Request $request)
    {
        // 以下を追記
        // Validationを行う
        $this->validate($request, Profile::$rules);

        $profile = new Profile;
        $form = $request->all();

        // フォームから送信されてきた_tokenを削除する
        unset($form['_token']);

        // データベースに保存する
        $profile->fill($form);
        $profile->save();
        
        return redirect('admin/profile');
    }
    //以下を追記 2023-05-25
    public function index(Request $request)
    {
        $cond_title = $request->cond_title;
        if($cond_title != ''){
            // 検索されたら検索結果を取得する
            $posts = Profile::where('title', $cond_title)->get();
        } else {
            // それ以外はすべてのニュースを取得する
            $posts = Profile::all();
        }
        return view('admin.profile.index', ['posts'=> $posts, 'cond_title' => $cond_title]);
    }
    public function edit(Request $request)
    {
        // News Modelからデータを削除する
        $profile = Profile::find($request->id);
        if (empty($profile)){
            abort(404);
        }
        return view('admin.profile.edit', ['profile_form' => $profile]);
    }

    public function update(Request $request)
    {
        // Validation をかける
        $this->validate($request, Profile::$rules);
        // News Modelからデータを取得する
        $profile = Profile::find($request->id);
        // 送信されてきたフォームデータを格納する
        $profile_form = $request->all();
        
        unset($profile_form['remove']);
        unset($profile_form['_token']);
        
        // 該当するデータを上書きして保存する
        $profile->fill($profile_form)->save();
        
        //Logを追加
        $log = new Log();
        $log->profile_id = $profile->id;
        $log->edited_at = Carbon::now();
        $log->save();
        
        return redirect('admin/profile');
    }
    
    // 以下を追加2023-05-25
    public function delete(Request $request)
    {
        // 該当するNews Modelを取得
        $profile = Profile::find($request->id);

        // 削除する
        $profile->delete();

        return redirect('admin/profile/');
    }
}
