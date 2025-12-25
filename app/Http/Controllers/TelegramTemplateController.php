<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TelegramTemplate;
use App\Setting;

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

    public function telegramLinkUpdate(Request $request)
    {
        $request->validate([
            'telegram_link' => 'required|url'
        ]);
    
        Setting::updateOrCreate(
            ['key' => 'telegram_link'],
            ['value' => $request->telegram_link]
        );
    
        return redirect()->back()->with('status', [
            'success' => true,
            'msg' => __('Telegram Link updated successfully!')
        ]);
    }    

    public function test(){
        $user = auth()->user();
        return response()->json(["user: " => $user]);
    }
}