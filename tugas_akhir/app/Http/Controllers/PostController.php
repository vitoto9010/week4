<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Post;
use App\Tag;
use Auth;

class PostController extends Controller
{
    //construct function
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    private $table = 'posts';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$posts = DB::table($this->table)->get(); //SELECT * FROM table
        $user = Auth::user();
        $posts = $user->posts;
        //dd($posts);
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());

        $request->validate([
            'title' => 'required|unique:posts',
            'body' => 'required'
        ]);

        $tags_arr = explode(',' , $request["tags"]);
        $tag_ids = [];
        //dd($tags_arr);

        foreach($tags_arr as $tag_name)
        {
            $tag = Tag::where('tag_name', $tag_name)->first();
            if ($tag){
                $tag_ids[] = $tag->id;
            } else{
                $new_tag = Tag::create(['tag_name' => $tag_name]);
                $tag_ids[] = $new_tag->id;

            }
        }
        //dd($tag_ids);

        $post = Post::create([
            "title" => $request["title"],
            "body" => $request["body"]
        ]);

        $post->tags()->sync($tag_ids);
        $user = Auth::user();
        $user->posts()->save($post);

        return redirect('/posts')->with('success', 'Post Berhasil Disimpan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $post = DB::table($this->table)->where('id', $id)->first(); //SELECT * FROM posts WHERE id
        //dd($post);
        $post = Post::find($id);
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $post = DB::table($this->table)->where('id', $id)->first(); //SELECT * FROM

        return view('posts.edit', compact('post'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // $query = DB::table($this->table)
        //             ->where('id', $id)
        //             ->update([
        //                 'title' => $request['title'],
        //                 'body' => $request['body']
        //             ]);

        $update = Post::where('id', $id)->update([
            "title" => $request["title"],
            "body" => $request["body"]
        ]);

        return redirect('/posts')->with('success', 'Berhasil Update post!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //$query = DB::table($this->table)->where('id', $id)->delete();
        Post::destroy($id);
        return redirect('/posts')->with('success', 'Post Berhasil dihapus');
    }
}
