<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Helper;
use Illuminate\Http\Request;

class PostController extends Controller
{
  public function index()
  {
    $posts = Post::all();

    return view('admin.posts.index', compact('posts'));
  }

  public function create()
  {
    return view('admin.posts.create');
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'title'       => 'required|string|max:255',
      'meta_data'   => 'nullable|array',
      'status'      => 'required|boolean',
      'content'     => 'required|string',
      'priority'    => 'nullable|integer',
      'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:1004800',
      'description' => 'nullable|string',
    ]);

    if ($request->hasFile('thumbnail')) {
      $payload['thumbnail'] = Helper::uploadFile($request->file('thumbnail'), 'public');
    }

    $payload['slug'] = Post::generateSlug($payload['title']);

    $payload['meta_data'] = [
      'keywords' => $payload['meta_data']['keywords'] ?? null,
    ];

    $payload['content'] = Helper::htmlPurifier($payload['content']);

    Post::create(array_merge($payload, [
      'author_id'   => auth()->user()->id,
      'author_name' => auth()->user()->username,
    ]));

    Helper::addHistory('Thêm bài viết ' . $payload['title'], $payload);

    return response()->json([
      'status'  => true,
      'message' => 'Thêm bài viết thành công',
    ]);
  }

  public function show($id)
  {
    $post = Post::findOrFail($id);

    return view('admin.posts.show', compact('post'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'title'       => 'required|string|max:255',
      'status'      => 'required|boolean',
      'content'     => 'required|string',
      'priority'    => 'nullable|integer',
      'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:1004800',
      'meta_data'   => 'nullable|array',
      'description' => 'nullable|string',
    ]);

    $post = Post::findOrFail($request->id);

    if ($request->hasFile('thumbnail')) {
      $payload['thumbnail'] = Helper::uploadFile($request->file('thumbnail'), 'public');
    }

    $payload['slug'] = str()->slug($payload['title']);

    $payload['meta_data'] = [
      'keywords' => $payload['meta_data']['keywords'] ?? null,
    ];

    $payload['content'] = Helper::htmlPurifier($payload['content']);

    $post->update(array_merge($payload));

    Helper::addHistory('Cập nhật bài viết ' . $payload['title'], $payload);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật bài viết thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $request->validate([
      'id' => 'required|integer',
    ]);

    $post = Post::findOrFail($request->id);

    $post->delete();

    if ($post->thumbnail !== null) {
      Helper::deleteFile($post->thumbnail);
    }

    Helper::addHistory('Xoá bài viết ' . $post->title . ' thành công');

    return response()->json([
      'status'  => true,
      'message' => 'Xoá bài viết thành công',
    ]);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:posts,id',
      'priority' => 'required|integer',
    ]);

    $item = Post::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
