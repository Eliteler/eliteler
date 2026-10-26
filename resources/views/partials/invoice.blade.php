<div class="p-2" id="invoice">
    <div class="card-body">

        <div class="row mb-4">
            <div class="col-6">

                <img src="{{ asset($settings->site_logo) }}" class="img-fluid" alt="{{ config('app.name') }}" width="170px"><br>

                <span class="h4">{{ $fromName }}</span><br>

                <span>
                    @if ($fromAddress)
                        {{ $fromAddress }},
                    @endif
                    @if ($fromCity)
                        {{ $fromCity }},
                    @endif
                    @if ($fromState)
                        {{ $fromState }}
                    @endif
                    @if ($fromCountry)
                        {{ $fromCountry }} <br>
                    @endif
                </span>

                <span><strong>{{ __('Email') }}</strong>: {{ $fromEmail }}</span><br>

                @if (!empty($fromPhone))
                    <span><strong>{{ __('Phone') }}</strong>: {{ $fromPhone }}</span><br>
                @endif

                @if (!empty($fromVat))
                    <span><strong>{{ __('Tax Number') }}</strong>: {{ $fromVat }}</span><br>
                @endif

            </div>

            <div class="col-6 text-end">
                <h1>{{ __('INVOICE') }}</h1>
                <h4>#{{ $transaction->invoice_prefix }}{{ $transaction->invoice_number }}</h4>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <h4 class="text-muted">{{ __('Bill To') }}</h4>

                <span class="h4">{{ $toName }}</span><br>

                <span>
                    @if ($toAddress)
                        {{ $toAddress }},
                    @endif
                    @if ($toCity)
                        {{ $toCity }},
                    @endif
                    @if ($toState)
                        {{ $toState }}
                    @endif
                    @if ($toCountry)
                        {{ $toCountry }} <br>
                    @endif
                </span>

                <span><strong>{{ __('Email') }}</strong>: {{ $toEmail }}</span><br>

                @if (!empty($toPhone))
                    <span><strong>{{ __('Phone') }}</strong>: {{ $toPhone }}</span><br>
                @endif

                @if (!empty($toVat))
                    <span><strong>{{ __('Tax Number') }}</strong>: {{ $toVat }}</span><br>
                @endif
            </div>

            <div class="col-6 text-end">
                <p><strong>{{ __('Date') }}</strong>:
                    {{ date('M d, Y', strtotime($transaction->created_at)) }}</p>
                <p><strong>{{ __('Payment Terms') }}</strong>:
                    {{ __('-') }}</p>
                <h5><strong>{{ __('Balance Due') }}</strong>:
                    {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}0.00
                </h5>
            </div>
        </div>

        <table class="table table-borderless">
            <thead class="border-bottom">
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th class="text-end">{{ __('Quantity') }}</th>
                    <th class="text-end">{{ __('Rate') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                </tr>
            </thead>

            <tbody>
                <tr class="fw-bold">
                    <td>{{ __($planDetails->plan_name) }} -
                        {{ $planDetails->no_of_ai_credits }} {{ __('AI Credit') }}
                    </td>
                    <td class="text-end">1</td>
                    <td class="text-end">
                        {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $planDetails->plan_price }}
                    </td>
                    <td class="text-end">
                        {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $planDetails->plan_price }}
                    </td>
                </tr>
            </tbody>

            <tfoot class="border-top fw-bold">

                <tr>
                    <td colspan="3" class="text-end">{{ __('Subtotal') }}</td>
                    <td class="text-end">
                        {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['subtotal'] ?? 0 }}
                    </td>
                </tr>

                @if (($billing['tax_amount'] ?? 0) > 0)
                    <tr>
                        <td colspan="3" class="text-end">{{ __($billing['tax_name'] ?? '') }}
                            ({{ $billing['tax_value'] ?? 0 }}%)</td>
                        <td class="text-end">
                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['tax_amount'] ?? 0 }}
                        </td>
                    </tr>
                @endif

                @if (!empty($billing['applied_coupon']))
                    <tr>
                        <td colspan="3" class="text-end">{{ __('Before Discount') }}</td>
                        <td class="text-end">
                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}
                            {{ ($billing['subtotal'] ?? 0) + ($billing['tax_amount'] ?? 0) }}
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" class="font-weight-bold text-end">
                            <strong>{{ __('Applied Coupon') }}:
                                {{ $billing['applied_coupon'] }}</strong>
                        </td>
                        <td class="font-weight-bold text-end">
                            -
                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['discounted_price'] ?? 0 }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td colspan="3" class="text-end">{{ __('After Tax') }}</td>
                        <td class="text-end">
                            {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}
                            {{ ($billing['subtotal'] ?? 0) + ($billing['tax_amount'] ?? 0) }}
                        </td>
                    </tr>
                @endif

                <tr>
                    <td colspan="3" class="text-end"><strong>{{ __('Total') }}</strong></td>
                    <td class="text-end">
                        <strong>{{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['invoice_amount'] ?? 0 }}</strong>
                    </td>
                </tr>

                <tr>
                    <td colspan="3" class="text-end">{{ __('Amount Paid') }}</td>
                    <td class="text-end">
                        {{ $currencies->firstWhere('iso_code', $transaction->currency)->symbol ?? '' }}{{ $billing['invoice_amount'] ?? 0 }}
                    </td>
                </tr>

            </tfoot>
        </table>

        <p class="mt-5">
            <strong>{{ __('Notes') }}</strong>:<br>
            <span class="text-muted">
                {{ __('Payment from ') }}{{ __($transaction->payment_method) }}<br>
                {{ __('Transaction ID: ') }} {{ $transaction->payment_transaction_id }}
            </span>
        </p>

        <p class="text-center text-muted mt-5">
            {{ __($config[29]->config_value ?? 'Thank you for your business') }}
        </p>

    </div>
</div>
