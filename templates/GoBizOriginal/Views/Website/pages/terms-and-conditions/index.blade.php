@extends('GoBizOriginal::Website.layouts.index', ['nav' => true, 'banner' => false, 'footer' => true, 'cookie' => true, 'setting' => true, 'title' => __('Terms and Conditions')])

@section('content')
    {{-- Terms and condition page --}}
    <section class="pt-12 lg:pb-20 lg:px-24 overflow-hidden">
        <div class="container mx-auto px-4">
            <div class="max-w-full mx-auto mb-20">
                <h2 class="mb-4 font-heading font-semibold text-center text-6xl sm:text-7xl text-gray-900 break-words">
                    {{ __($termsPage[0]->section_content) }}</h2>
            </div>
            <div class="flex flex-wrap -m-6 mb-12">
                @if (
                    !empty($termsPage[1]->section_content) &&
                        !empty($termsPage[2]->section_content) &&
                        !empty($termsPage[3]->section_content) &&
                        !empty($termsPage[4]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[1]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[2]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[3]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[4]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($termsPage[5]->section_content) &&
                        !empty($termsPage[6]->section_content) &&
                        !empty($termsPage[7]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[5]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[6]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[7]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (!empty($termsPage[8]->section_content) && !empty($termsPage[9]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[8]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[9]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($termsPage[10]->section_content) &&
                        !empty($termsPage[11]->section_content) &&
                        !empty($termsPage[12]->section_content) &&
                        !empty($termsPage[13]->section_content) &&
                        !empty($termsPage[14]->section_content) &&
                        !empty($termsPage[15]->section_content) &&
                        !empty($termsPage[16]->section_content) &&
                        !empty($termsPage[17]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[10]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[11]->section_content) }}</p>
                            <p class="text-base text-black mb-4"><strong>{{ __($termsPage[12]->section_content) }}</strong>
                            </p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[13]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[14]->section_content) }}</p>
                            <p class="text-base text-black mb-4"><strong>{{ __($termsPage[15]->section_content) }}</strong>
                            </p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[16]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[17]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($termsPage[18]->section_content) &&
                        !empty($termsPage[19]->section_content) &&
                        !empty($termsPage[20]->section_content) &&
                        !empty($termsPage[21]->section_content) &&
                        !empty($termsPage[22]->section_content) &&
                        !empty($termsPage[23]->section_content) &&
                        !empty($termsPage[24]->section_content) &&
                        !empty($termsPage[25]->section_content) &&
                        !empty($termsPage[26]->section_content) &&
                        !empty($termsPage[27]->section_content) &&
                        !empty($termsPage[28]->section_content) &&
                        !empty($termsPage[29]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[18]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[19]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[20]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[21]->section_content) }}</p>
                            <p class="text-base text-black mb-4"><strong>{{ __($termsPage[22]->section_content) }}</strong>
                            </p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[23]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[24]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[25]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[26]->section_content) }}</p>
                            <p class="text-base text-black mb-4"><strong>{{ __($termsPage[27]->section_content) }}</strong>
                            </p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[28]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[29]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (!empty($termsPage[30]->section_content) && !empty($termsPage[31]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[30]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[31]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (!empty($termsPage[32]->section_content) && !empty($termsPage[33]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[32]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[33]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (!empty($termsPage[34]->section_content) && !empty($termsPage[35]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[34]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[35]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($termsPage[36]->section_content) &&
                        !empty($termsPage[37]->section_content) &&
                        !empty($termsPage[38]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[36]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[37]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[38]->section_content) }}</p>
                        </div>
                    </div>
                @endif
                @if (
                    !empty($termsPage[39]->section_content) &&
                        !empty($termsPage[40]->section_content) &&
                        !empty($termsPage[41]->section_content) &&
                        !empty($termsPage[42]->section_content) &&
                        !empty($termsPage[43]->section_content))
                    <div class="w-full p-6">
                        <div class="md:max-w-full">
                            <h2 class="mb-4 font-heading font-medium text-2xl text-gray-900 break-words">
                                {{ __($termsPage[39]->section_content) }}</h2>
                            <p class="text-base text-black mb-3">{{ __($termsPage[40]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[41]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[42]->section_content) }}</p>
                            <p class="text-base text-black mb-3">{{ __($termsPage[43]->section_content) }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
