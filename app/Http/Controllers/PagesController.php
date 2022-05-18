<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\Steps;
use App\Models\Solutions;
use App\Models\Questions;
use App\Models\Associates;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function about(Request $request)
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function contactSend(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'message' => 'required',
        ]);

        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message
        ];
        //Need to fix this email feature
        // \Mail::to('testmail@gmail.com')->send(new \App\Mail\MailContact($details));
        return redirect()->route('contact')->with('success', 'Message Sent Successfully');
    }

    public function faqs(Request $request)
    {
        return view('faqs');
    }

    public function complains(Request $request)
    {
        return view('complains');
    }

    public function terms(Request $request)
    {
        return view('terms');
    }

    public function solution_search_results()
    {
        $results = [];
        $search_term = request()->search;
        $solutions = Solutions::whereRaw('MATCH (solution_title, solution_description, tags) AGAINST (?)', array($search_term))->orderBy('id', 'DESC')->paginate(5);
        $questions = Questions::whereRaw('MATCH (question, description) AGAINST (?)', array($search_term))->orderBy('id', 'DESC')->paginate(5);

        $results = [
            'questions' => $questions,
            'solutions' => $solutions
        ];

        return view('search_results', ['results' => $results]);

    }

    /**
     * Show the user profile screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $get_profile = User::where('id', $id)->first();
        $association = Associates::where('user_id', Auth::id())->where('associate_id', $id)->whereNull('deleted_at')->first();
        return view('profile.show', [
            'user' => $get_profile,
            'association' => $association,
        ]);
    }

    public function edit()
    {
        return view('profile.edit');
    }
}
