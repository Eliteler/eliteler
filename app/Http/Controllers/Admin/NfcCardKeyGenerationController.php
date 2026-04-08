<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\BusinessCard;
use App\Setting;
use App\NfcCardKey;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\NfcCardOrder;
use App\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Termwind\Components\Span;

class NfcCardKeyGenerationController extends Controller
{
    /**
     * Create a new controller instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        if ($request->ajax()) {
            // Get nfc card key generations
            $nfcCardKeys = NfcCardKey::where('status', '!=', 2)->orderBy('id', 'desc')->get();

            return DataTables::of($nfcCardKeys)
                ->addIndexColumn()
                ->editColumn('created_at', function ($nfcCardKey) {
                    return formatDateForUser($nfcCardKey->created_at);
                })
                ->editColumn('unqiue_key', function ($nfcCardKey) {
                    return '<span class="fw-bold">' . $nfcCardKey->unqiue_key . '</span>';
                })
                ->editColumn('card_id', function ($nfcCardKey) {
                    if (isset($nfcCardKey->card_id)) {
                        // Get card id wise business card
                        $businessCard = BusinessCard::where('card_id', $nfcCardKey->card_id)->first();

                        if (isset($businessCard)) {
                        return '<a href="' . url($businessCard->card_url) . '" target="_blank">' . trans($businessCard->title) . '</a>';
                        } else {
                            return '<span class="fw-bold">' . __('Unassigned') . '</span>';
                        }
                    } else {
                        return '<span class="fw-bold">' . __('Unassigned') . '</span>';
                    }
                })
                ->editColumn('link_status', function ($nfcCardKey) {
                    return $nfcCardKey->link_status == 'unlinked'
                        ? '<span class="badge bg-red text-white">' . __('Unlinked') . '</span>'
                        : '<span class="badge bg-green text-white">' . __('Linked') . '</span>';
                })
                ->editColumn('action', function ($nfcCardKey) {
                    // Get NFC order details
                    $nfcOrderDetails = NfcCardOrder::whereJsonContains('order_details->unique_key', $nfcCardKey->unqiue_key)->where('order_status', '!=', 'pending')->orWhere('order_status', '!=', 'cancelled')->orWhere('order_status', '!=', 'hold')->first();

                    // Greeting Letter
                    $actionBtn = '<a href="' . route('admin.key.greeting.letter', $nfcCardKey->unqiue_key) . '?type=key" class="dropdown-item">' . __('Greeting Letter') . '</a>';

                    // Write in NFC Card
                    $actionBtn .= '<a href="' . route('admin.key.write.to.nfc.card', $nfcCardKey->unqiue_key) . '?type=key" class="dropdown-item">' . __('Write in NFC Card') . '</a>';

                    // Link / Unlink
                    if ($nfcCardKey->link_status == 'linked') {
                        $actionBtn .= '<a onclick="updateStatus(\'' . $nfcCardKey->nfc_card_key_id . '\', \'unlink\'); return false;" class="dropdown-item">' . __('Unlink to NFC Card') . '</a>';
                    } else {
                        $actionBtn .= '<a href="' . route('admin.link.key', $nfcCardKey->nfc_card_key_id) . '" class="dropdown-item">' . __('Link to NFC Card') . '</a>';
                    }

                    // Delete
                    $actionBtn .= '<a onclick="deleteKey(\'' . $nfcCardKey->nfc_card_key_id . '\'); return false;" class="dropdown-item">' . __('Delete') . '</a>';

                    return '<a class="btn-action" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <div class="nav-item dropdown">
                                    ' . $actionBtn . '
                                </div>
                            </div>';
                })
                ->rawColumns(['created_at', 'unqiue_key', 'card_id', 'link_status', 'action'])
                ->make(true);
        }

        return view('admin.pages.nfc-card-key-generations.index', compact('settings', 'config'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        return view('admin.pages.nfc-card-key-generations.create', compact('settings', 'config'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'nfc_card_key_generations' => 'required|numeric|min:1|max:999',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->all()[0])->withInput();
        }

        // Generate keys
        $nfcCardKeyGenerations = $request->nfc_card_key_generations;

        // Queries
        $config = DB::table('config')->get();

        // Generate NFC card key 
        $characterLimit = (int) $config[92]->config_value;

        for ($i = 0; $i < $nfcCardKeyGenerations; $i++) {
            // Generate unqiue key
            $unqiueKey = Str::random($characterLimit);

            // Save
            $nfcCardKey = new NfcCardKey();
            $nfcCardKey->nfc_card_key_id = uniqid();
            $nfcCardKey->key_type = 'offline';
            $nfcCardKey->unqiue_key = $unqiueKey;
            $nfcCardKey->save();
        }

        return redirect()->route('admin.key.generations')->with('success', trans('Keys generated!'));
    }

    /** 
     * Link / Unlink the key generation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function link($id)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Get the key generation
        $nfcCardKey = NfcCardKey::where('nfc_card_key_id', $id)->first();
        
        // All customers
        $customers = User::where('role_id', 2)->get();

        return view('admin.pages.nfc-card-key-generations.link', compact('settings', 'config', 'nfcCardKey', 'customers'));
    }

    /** 
     * Update the link status of the key generation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateLinkKey(Request $request)
    {
        // Get data from request
        $nfcCardKeyId = $request->key_id;
        $cardId = $request->card_id;

        // Check if the key generation exists
        $nfcCardKey = NfcCardKey::where('nfc_card_key_id', $nfcCardKeyId)->first();

        // Check if the key generation exists
        if (!$nfcCardKey) {
            return redirect()->route('admin.key.generations')->with('failed', trans('Key not found!'));
        }

        // Update status
        NfcCardKey::where('nfc_card_key_id', $nfcCardKeyId)->update(['card_id' => $cardId, 'link_status' => 'linked', 'status' => 1, 'updated_at' => now()]);

        return redirect()->route('admin.key.generations')->with('success', trans('Link to NFC Card updated!'));
    }

    /**
     * Update the status of the key generation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function action(Request $request)
    {
        // Get data from request
        $nfcCardKeyId = $request->query('id');
        $cardId = $request->query('card_id');
        $nfcCardKeyStatus = $request->query('status');

        // Check status
        switch ($nfcCardKeyStatus) {
            case 'link':
                $linkStatus = 'linked';
                $status = 1;
                break;

            case 'delete':
                $linkStatus = 'unlinked';
                $status = 1;

                // Update status
                NfcCardKey::where('nfc_card_key_id', $nfcCardKeyId)->update(['card_id' => null, 'link_status' => $linkStatus, 'status' => $status, 'updated_at' => now()]);
                break;

            default:
                $linkStatus = 'unlinked';
                $status = 1;

                // Update status
                NfcCardKey::where('nfc_card_key_id', $nfcCardKeyId)->update(['card_id' => null, 'link_status' => $linkStatus, 'status' => $status, 'updated_at' => now()]);
                break;
        }

        return redirect()->route('admin.key.generations')->with('success', trans(':status to NFC Card updated!', ['status' => $linkStatus]));
    }

    // Write to NFC Card.
    public function keyWriteToNfcCard(Request $request, $key)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Get key details
        $keyDetails = NfcCardKey::where('unqiue_key', $key)->first();

        if(!$keyDetails) {
            return redirect()->back()->with('failed', trans('Not Found!'));
        }

        return view('admin.pages.nfc-card-key-generations.print', compact('keyDetails', 'settings', 'config'));
    }

    // Greeting Letter  (Key Generation)
    public function keyGreetingLetter(Request $request, $key)
    {
        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Check enable nfc card order system
        if ($config[76]->config_value == '0') {
            return abort(404);
        }

        // Get key details
        $keyDetails = NfcCardKey::where('unqiue_key', $key)->first();

        if(!$keyDetails) {
            return redirect()->back()->with('failed', trans('Not Found!'));
        }

        $template = "
        <div class='logo'>
            <img src=':logo' alt=':websitename'>
        </div>
        <h2 class='d-print-none'>Welcome to :websitename! 🎉</h2>
        <div class='content'>
            <h4>Dear <strong>:customername</strong>,</h4>
            <p>Your NFC Card is ready to help you share your contact details effortlessly. Activate your card and start networking with just a tap!</p>
            <h3>🔑 Your Activation Code</h3>
            <p class='activation-code'>:activationcode</p>
            <h3 class='mt-3'>📌 How to Activate Your NFC Card?</h3>
            <ol style='text-align: left;'>
                <li><strong>Log in</strong> to your account.</li>
                <li><strong>Go to</strong> the <strong>'Activate NFC Card'</strong> section.</li>
                <li><strong>Enter the activation code</strong> and submit.</li>
                <li>Your NFC card is now ready to use!</li>
            </ol>
            <div class='qr-code'>
                <h3>📲 Scan to Activate</h3>
                <canvas id='activateQrCode'></canvas>
            </div>
        </div>
        <div class='mt-3'>
            <span>Thank you for choosing <strong>:websitename</strong>!</span>
            <p>If you need any assistance, feel free to contact us at 
                <strong><a href='mailto::supportemail'>:supportemail</a></strong> 
                or call us at <strong>:supportphone</strong>.
            </p>
        </div>
        <div class='content'>
            <p>Best regards,</p>
            <h4>:websitename</h4>
        </div>
        ";

        return view('admin.pages.nfc-card-key-generations.greeting-letter', compact('keyDetails', 'template', 'settings', 'config'));
    }

    // Update greeting letter (Key Generation)
    public function updateKeyGreetingLetter(Request $request, $key)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'greeting_letter' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.key.greeting.letter')->with('failed', $validator->errors()->first());
        }

        // Update the greeting letter
        DB::table('config')->where('config_key', 'nfc_greetings')->update([
            'config_value' => $request->greeting_letter,
        ]);

        // Page redirect
        return redirect()->route('admin.key.greeting.letter', $key)->with('success', trans('Updated!'));
    }

    // Get customer cards
    public function getCustomerCards(Request $request)
    {
        // Get data from request
        $customerId = $request->customerId;

        // Get customer cards
        $customerCards = BusinessCard::where('user_id', $customerId)->where('card_status', 'activated')->get();

        // Return customer cards
        return response()->json($customerCards);
    }

    // Delete NFC card key
    public function delete(Request $request)
    {
        // Get data from request
        $nfcCardKeyId = $request->query('id');

        // Check if the key generation exists
        $nfcCardKey = NfcCardKey::where('nfc_card_key_id', $nfcCardKeyId)->first();

        // Check if the key generation exists
        if (!$nfcCardKey) {
            return redirect()->route('admin.key.generations')->with('failed', trans('Key not found!'));
        }

        // Check "card_id" is null
        if ($nfcCardKey->card_id != null) {
            return redirect()->route('admin.key.generations')->with('failed', trans('This key is linked to a card. Please unlink the card first.'));
        }

        // Update status
        NfcCardKey::where('nfc_card_key_id', $nfcCardKeyId)->update([
            'status' => 2,
        ]);

        return redirect()->route('admin.key.generations')->with('success', trans('Key deleted!'));
    }
}
