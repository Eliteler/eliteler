<?php

namespace Plugins\AiBuilder\Controllers\User;

use App\BusinessCard;
use App\Http\Controllers\Controller;
use App\Services\GoBizCommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Plugins\AiBuilder\Classes\GenerateWithAI;

class AiBuilderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // user
        $user = Auth::user();

        // plan details
        $plan_details = json_decode($user->plan_details, true);

        // settings
        $settings = GoBizCommonService::settings();

        // config
        $config = GoBizCommonService::config();

        // aibuilder settings
        $aibuilder_settings = DB::table('aibuilder_settings')->first();

        // check ai generator boolean 
        $is_ai_generator_enabled = 0;

        // check ai builder settings
        if (empty($aibuilder_settings)) {
            $is_ai_generator_enabled = 0;
        } else {
            // check ai builder enabled
            $is_ai_generator_enabled = $aibuilder_settings->aibuilder;
        }

        // get credits
        $ai_credits = DB::table('ai_credits')->where('user_id', $user->user_id)->first();

        if (empty($ai_credits)) {
            $credits = 0;
        } else {
            $credits = $ai_credits->credits;
        }

        // check ai generator enabled
        if ($is_ai_generator_enabled == 0) {
            return redirect()->route('user.cards')->with('failed', trans('This feature is not avaialble for your plan.'));
        }

        // return view
        return view()->file(
            base_path('plugins/AiBuilder/Views/User/index.blade.php'),
            compact('settings', 'config', 'aibuilder_settings', 'credits')
        );
    }

    public function generate(Request $request)
    {
        // validate file
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:' . env('SIZE_LIMIT'),
        ]);

        // validation failed
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => trans($validator->errors()->first()),
            ], 422);
        }

        // ai builder settings
        $aibuilder_settings = DB::table('aibuilder_settings')->first();

        // check ai builder configured
        if (empty($aibuilder_settings)) {
            return response()->json([
                'success' => false,
                'message' => trans('AI Builder is not configured!'),
            ], 422);
        }

        // check key
        if (empty($aibuilder_settings->key_1) || empty($aibuilder_settings->provider) || empty($aibuilder_settings->model)) {
            return response()->json([
                'success' => false,
                'message' => trans('AI Builder is not configured!'),
            ], 422);
        }

        // user
        $user = Auth::user();

        // plan details
        $plan_details = json_decode($user->plan_details, true);

        // created cards count
        $cards = BusinessCard::where('user_id', $user->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->get();

        // No of cards
        if ($plan_details['no_of_vcards'] == 999) {
            $no_cards = 999999;
        } else {
            $no_cards = $plan_details['no_of_vcards'];
        }

        // if limit exceeded return error
        if (count($cards) >= $no_cards) {
            return response()->json([
                'success' => false,
                'message' => trans('Maximum card creation limit is exceeded, Please upgrade your plan to add more card(s)!'),
            ], 422);
        }

        // ai credits
        $ai_credits = DB::table('ai_credits')->where('user_id', $user->user_id)->first();

        // insert ai credits if not exists
        if (empty($ai_credits)) {
            $ai_credits = DB::table('ai_credits')->insert([
                'user_id' => $user->user_id,
                'credits' => 0,
            ]);

            // get ai credits
            $ai_credits = DB::table('ai_credits')
                ->where('user_id', $user->user_id)
                ->first();
        }

        // check ai credits
        if ($ai_credits->credits == 0) {
            return response()->json([
                'success' => false,
                'message' => trans('You do not have enough credits to generate a card.'),
            ], 422);
        }

        // store image temporary
        $file = $request->file('file');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $image = $file->storeAs('profile-images', $fileName, 'public');

        // base64 image
        $stored_image = storage_path('app/public/' . $image);

        // if image not found throw error
        if (!file_exists($stored_image)) {
            return response()->json([
                'success' => false,
                'message' => trans('Image not found. Please upload the image again.'),
            ], 422);
        }

        // convert to base64
        $imageBase64 = base64_encode(file_get_contents($stored_image));

        try {
            // check provider
            $vcard = new GenerateWithAI();
            $result = $vcard->generate($imageBase64, $aibuilder_settings, $plan_details, $user->user_id);

            // delete image after processed
            try {
                Storage::disk('public')->delete($image);
            } catch (\Exception $e) {
            }

            if (!$result['success']) {
                return response()->json($result, 422);
            }

            // reduce credits
            $ai_credits = DB::table('ai_credits')
                ->where('user_id', $user->user_id)
                ->first();

            if ($ai_credits->credits < 999) {
                DB::table('ai_credits')
                    ->where('user_id', $user->user_id)
                    ->update([
                        'credits' => $ai_credits->credits - 1,
                    ]);
            }

            // success
            return response()->json([
                'success' => true,
                'message' => $result['url']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('Failed to create business card.'),
            ], 422);
        }
    }
}
