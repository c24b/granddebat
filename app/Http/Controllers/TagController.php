<?php

namespace App\Http\Controllers;


use App\Models\Question;
use App\Models\Response;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    private $apiTagController;

    public function __construct(\App\Http\Controllers\Api\TagController $apiTagController)
    {
        $this->apiTagController = $apiTagController;
    }

    public function create(Request $request, Question $question)
    {
        $this->authorize('create', [Tag::class, $question]);
        $user = $request->user();
        return view('tags.create', compact('question', 'user'));
    }

    public function store(Question $question, Request $request)
    {
        $this->authorize('create', [Tag::class, $question]);
        $this->apiTagController->doCreate($request->user(), $question, $request->input('name'));
        return redirect('questions/' . $question->id);
    }

    public function show(Request $request, Tag $tag)
    {
        $responses = Response::whereHas('actions', function ($query) use ($tag) {
            $query->where('tag_id', $tag->id);
        })->inRandomOrder()->limit(20)->get();
        $question = $tag->question;
        $user = $request->user();
        return view('tags.show', compact('tag', 'responses', 'question', 'user'));
    }

    public function edit(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);
        $question = $tag->question;
        $user = $request->user();
        return view('tags.edit', compact('question', 'tag', 'user'));
    }

    public function update(Tag $tag, Request $request)
    {
        $this->authorize('update', $tag);
        $this->validate($request, [
            'name' => 'required',
        ]);
        $tag->name = $request->input('name');
        $tag->save();
        return redirect('questions/' . $tag->question->id);
    }

    public function delete(Request $request, Tag $tag)
    {
        $this->authorize('delete', $tag);
        $question = $tag->question;
        $tag->actions()->delete();
        $tag->delete();
        $request->user()->refreshScore();
        return redirect('questions/' . $question->id);
    }

    public function showDelete(Tag $tag)
    {
        $this->authorize('delete', $tag);
        $question = $tag->question;
        return view('tags.delete', compact('question', 'tag'));
    }

}
