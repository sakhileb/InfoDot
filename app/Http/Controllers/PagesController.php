<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use App\Models\Steps;
use App\Models\Solutions;
use App\Models\Questions;
use App\Models\Associates;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class PagesController extends Controller
{
    /**
     * Display the about page
     */
    public function about(Request $request): View
    {
        return view('about');
    }

    /**
     * Display the contact page
     */
    public function contact(): View
    {
        return view('contact');
    }

    /**
     * Handle contact form submission
     */
    public function contactSend(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ];

        try {
            // Send email to admin
            Mail::to(config('mail.admin_email', 'admin@infodot.com'))
                ->send(new ContactMail($details));
            
            Log::info('Contact form email sent successfully', [
                'sender_email' => $request->email,
                'sender_name' => $request->name,
            ]);
            
            return redirect()->route('contact')
                ->with('success', 'Message sent successfully! We will get back to you soon.');
        } catch (\Exception $e) {
            Log::error('Failed to send contact form email', [
                'error' => $e->getMessage(),
                'sender_email' => $request->email,
                'sender_name' => $request->name,
            ]);
            
            return redirect()->route('contact')
                ->with('error', 'Sorry, there was an error sending your message. Please try again later.');
        }
    }

    /**
     * Display the FAQs page
     */
    public function faqs(Request $request): View
    {
        return view('faqs');
    }

    /**
     * Display the complaints page
     */
    public function complains(Request $request): View
    {
        return view('complains');
    }

    /**
     * Display the terms page
     */
    public function terms(Request $request): View
    {
        $terms = \Illuminate\Support\Str::markdown(file_get_contents(resource_path('markdown/terms.md')));
        return view('terms', ['terms' => $terms]);
    }
    
    /**
     * Display the policy page
     */
    public function policy(Request $request): View
    {
        $policy = \Illuminate\Support\Str::markdown(file_get_contents(resource_path('markdown/policy.md')));
        return view('policy', ['policy' => $policy]);
    }

    /**
     * Display search results for solutions and questions
     */
    public function solution_search_results(): View
    {
        $search_term = request()->search;
        
        // Search solutions using Scout with FULLTEXT fallback
        $solutions = $this->searchWithFallback(Solutions::class, $search_term, 5);
        
        // Search questions using Scout with FULLTEXT fallback
        $questions = $this->searchWithFallback(Questions::class, $search_term, 5);

        $results = [
            'questions' => $questions,
            'solutions' => $solutions,
        ];

        return view('search_results', ['results' => $results]);
    }

    /**
     * Search a model using Scout if available, otherwise fallback to MySQL FULLTEXT.
     *
     * @param string $modelClass
     * @param string $query
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function searchWithFallback(string $modelClass, string $query, int $perPage)
    {
        try {
            // Try to use Scout first
            if (config('scout.driver') !== null && config('scout.driver') !== 'null') {
                return $modelClass::search($query)
                    ->paginate($perPage);
            }
        } catch (\Exception $e) {
            // Scout not available or error occurred, fall through to FULLTEXT
            \Log::debug('Scout search failed, falling back to FULLTEXT: ' . $e->getMessage());
        }

        // Fallback to MySQL FULLTEXT search
        return $modelClass::query()
            ->searchFulltext($query)
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    /**
     * Show the user profile screen
     */
    public function show(int $id): View
    {
        $get_profile = User::findOrFail($id);
        $association = Associates::where('user_id', Auth::id())
            ->where('associate_id', $id)
            ->whereNull('deleted_at')
            ->first();
        
        return view('profile.show', [
            'user' => $get_profile,
            'association' => $association,
        ]);
    }

    /**
     * Show the profile edit page
     */
    public function edit(): View
    {
        return view('profile.edit');
    }
}
