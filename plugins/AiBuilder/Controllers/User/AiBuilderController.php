<?php

namespace Plugins\AiBuilder\Controllers\User;

use App\AiCredit;
use App\BusinessCard;
use App\Helpers\StorageHelper;
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
        $ai_credits = AiCredit::where('user_id', $user->user_id)->first();

        if (empty($ai_credits)) {
            $credits = 0;
        } else {
            // trial credits
            $trial_credits = (int) ($ai_credits->trial_credits ?? 0);

            // purchased credits
            $purchased_credits = (int) ($ai_credits->credits ?? 0);

            // credits
            $credits = (int) ($purchased_credits != -1
                ? ($trial_credits + $purchased_credits)
                : $purchased_credits);
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
        $cards = BusinessCard::where('user_id', $user->user_id)->where('card_type', 'vcard')->where('card_status', 'activated')->where('status', '1')->get();

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
        $ai_credits = AiCredit::where('user_id', $user->user_id)->first();

        // insert ai credits if not exists
        if (empty($ai_credits)) {
            $ai_credits = AiCredit::create([
                'user_id'       => $user->user_id,
                'credits'       => 0,
                'trial_credits' => 0,
            ]);
        }

        // trial credits
        $trial_credits = (int) ($ai_credits->trial_credits ?? 0);

        // purchased credits
        $purchased_credits = (int) ($ai_credits->credits ?? 0);

        // credits
        $credits = (int) ($purchased_credits != -1
            ? ($trial_credits + $purchased_credits)
            : $purchased_credits);

        // check ai credits
        if ($credits == 0) {
            return response()->json([
                'success' => false,
                'message' => trans('You do not have enough credits to generate a card.'),
            ], 422);
        }

        // Store image temporarily using StorageHelper
        $imagePath = StorageHelper::uploadImage($request->file('file'), 'profile-images');
        $storedImage = $imagePath ? public_path($imagePath) : null;

        // If image not found throw error
        if (! $storedImage || ! file_exists($storedImage)) {
            return response()->json([
                'success' => false,
                'message' => trans('Image not found. Please upload the image again.'),
            ], 422);
        }

        // Convert to base64
        $imageBase64 = base64_encode(file_get_contents($storedImage));

        try {
            // Check provider and generate AI card
            $vcard  = new GenerateWithAI();
            $result = $vcard->generate($imageBase64, $aibuilder_settings, $plan_details, $user->user_id);

            // Delete temporary image after processing
            StorageHelper::deleteFile($imagePath);

            if (! $result['success']) {
                return response()->json($result, 422);
            }

            // Reduce credits
            if ($trial_credits > 0) {
                AiCredit::where('user_id', $user->user_id)->update([
                    'trial_credits' => $trial_credits - 1,
                ]);
            } else {
                // Not unlimited user credits only allow to reduce credits
                if ($purchased_credits != -1) {
                    AiCredit::where('user_id', $user->user_id)->update([
                        'credits' => $purchased_credits - 1,
                    ]);
                }
            }

            // Success response
            return response()->json([
                'success' => true,
                'message' => $result['url'],
            ]);
        } catch (\Exception $e) {
            // Ensure temporary file is deleted even if process fails
            StorageHelper::deleteFile($imagePath);

            return response()->json([
                'success' => false,
                'message' => trans('Failed to create business card.'),
            ], 422);
        }
    }
}
