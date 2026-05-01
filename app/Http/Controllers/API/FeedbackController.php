<?php

namespace App\Http\Controllers\API;


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
                ->get();
                
            return FeedbackResource::collection($feedback);
        }

   
}
