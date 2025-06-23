<?php
namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\ContactMessageReceived;
class ContactController extends Controller
{
    public function store(Request $request)
    {
       $validated = $request->validate([
        'name' => 'required|string|max:100',
        'email' => 'required|email',
        'message' => 'required|string',
    ]);

    $contact = Contact::create($validated);

    // Gửi email
    Mail::to('Chithanh1622003@gmail.com')->send(new ContactMessageReceived($validated));

    return response()->json([
        'message' => 'Đã gửi liên hệ và email thành công',
        'data' => $contact
    ], \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }
}