<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TelegramTemplate;
use App\Setting;
use App\BusinessLocation;

class TelegramTemplateController extends Controller
{
    //
    public function index(){
        $business_id = auth()->user()->business_id;

        $templates = TelegramTemplate::where("business_id", $business_id)->get();
        
        return view('E_Commerce.telegram_template.index', compact("templates"));
    }

    public function update(Request $request)
    {
        $templates = $request->input('templates', []);

        foreach ($templates as $id => $data) {
            $template = TelegramTemplate::find($id);
            if ($template) {
                $template->greeting  = $data['greeting'] ?? '';
                $template->body      = $data['body'] ?? '';
                $template->footer    = $data['footer'] ?? '';
                $template->auto_send = isset($data['auto_send']) ? 1 : 0;
                $template->save();
            }
        }

        $output = [
                'success' => true,
                'msg' => __('Telegram template update successfully!'),
            ];

        return redirect()->back()->with('status', $output);
    }

    public function telegramLink(){
        $business_id = auth()->user()->business_id;

        $telegram_username = BusinessLocation::where('business_id', $business_id)->value('custom_field1');
        return view('E_Commerce.telegram.index', compact('telegram_username'));
    }

    public function telegramLinkUpdate(Request $request)
    {
        $request->validate([
            'telegram_username' => 'required|string|max:255',
        ]);
    
        $business_id = auth()->user()->business_id;
    
        BusinessLocation::where('business_id', $business_id)
            ->update([
                'custom_field1' => $request->telegram_username
            ]);
    
        return redirect()->back()->with('status', [
            'success' => true,
            'msg' => __('Telegram Username updated successfully!')
        ]);
    }  

    public function test(){
        $user = auth()->user();
        return response()->json(["user: " => $user]);
    }
}