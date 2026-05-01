<?php

namespace App\Http\Controllers\Admin;


use App\Models\Feedback;
use Illuminate\Http\Request;
use App\Http\Resources\FeedbackResource;
use App\Http\Resources\FeedbackCollection;
use App\Http\Controllers\Controller;


class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
        {
            $feedback = Feedback::
               orderBy('created_at', 'desc')
                ->paginate(8);
                
            return FeedbackResource::collection($feedback);
        }
    /**
     * Show the form for creating a new resource.
     */
      public function store(Request $request)
    {
        $request->validate([
            'screenshot' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $imagePath = $request->file('screenshot')->store('feedback', 'public');

        $feedback = Feedback::create([
            'screenshot_path' => $imagePath
        ]);

        return new FeedbackResource($feedback);
    }


   public function destroy($id)
    {
        $feedback =  Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json(null, 204);
    }

   
}
