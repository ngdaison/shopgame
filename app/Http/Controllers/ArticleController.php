<?php

namespace App\Http\Controllers;

use App\Models\Post;

class ArticleController extends Controller
{
  public function index()
  {
    $articles = Post::where('status', true)->orderBy('priority', 'desc')->orderBy('id', 'desc')->paginate(10);

    return view('article.index', [
      'pageTitle' => 'Tin Tức Mới',
    ], compact('articles'));
  }

  public function show($slug)
  {
    $article = Post::where('status', true)->where('slug', $slug)->firstOrFail();

    return view('article.show', [
      'pageTitle' => $article->title,
    ], compact('article'));
  }
}
