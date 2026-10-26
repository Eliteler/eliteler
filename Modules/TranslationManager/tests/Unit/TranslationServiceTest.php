<?php

namespace Modules\TranslationManager\tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Modules\TranslationManager\app\Services\LanguageService;
use Modules\TranslationManager\app\Services\TranslationReaderService;
use Modules\TranslationManager\app\Services\TranslationWriterService;
use Modules\TranslationManager\app\Services\MissingTranslationService;

class TranslationServiceTest extends TestCase
{
    protected string $tempLangPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempLangPath = storage_path('app/temp_test_lang');
        if (File::exists($this->tempLangPath)) {
            File::deleteDirectory($this->tempLangPath);
        }
        File::makeDirectory($this->tempLangPath, 0755, true);

        config(['translation-manager.lang_path' => $this->tempLangPath]);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempLangPath)) {
            File::deleteDirectory($this->tempLangPath);
        }
        parent::tearDown();
    }

    public function test_can_flatten_and_unflatten_nested_arrays()
    {
        $reader = new TranslationReaderService();
        $writer = new TranslationWriterService();

        $nested = [
            'auth' => [
                'failed' => 'These credentials do not match.',
                'throttle' => [
                    'seconds' => 'Too many attempts.'
                ]
            ]
        ];

        $flat = $reader->flatten($nested);

        $this->assertEquals([
            'auth.failed' => 'These credentials do not match.',
            'auth.throttle.seconds' => 'Too many attempts.'
        ], $flat);

        $unflattened = $writer->unflatten($flat);
        $this->assertEquals($nested, $unflattened);
    }

    public function test_can_create_and_delete_language()
    {
        $service = new LanguageService();

        $enPath = $this->tempLangPath . '/en';
        File::makeDirectory($enPath, 0755, true);
        File::put($enPath . '/messages.php', "<?php return ['welcome' => 'Welcome'];");
        File::put($this->tempLangPath . '/en.json', json_encode(['Hello' => 'Hello']));

        $service->createLanguage('Tamil', 'ta', 'en');

        $this->assertDirectoryExists($this->tempLangPath . '/ta');
        $this->assertFileExists($this->tempLangPath . '/ta/messages.php');
        $this->assertFileExists($this->tempLangPath . '/ta.json');

        $service->deleteLanguage('ta');
        $this->assertDirectoryDoesNotExist($this->tempLangPath . '/ta');
        $this->assertFileDoesNotExist($this->tempLangPath . '/ta.json');
    }

    public function test_detects_missing_keys_between_locales()
    {
        $reader = new TranslationReaderService();
        $writer = new TranslationWriterService();
        $missingService = new MissingTranslationService($reader, $writer);

        $enPath = $this->tempLangPath . '/en';
        File::makeDirectory($enPath, 0755, true);
        File::put($enPath . '/messages.php', "<?php return ['welcome' => 'Welcome', 'farewell' => 'Goodbye'];");

        $esPath = $this->tempLangPath . '/es';
        File::makeDirectory($esPath, 0755, true);
        File::put($esPath . '/messages.php', "<?php return ['welcome' => 'Bienvenido'];");

        $missing = $missingService->detectMissingKeys('en', 'es');

        $this->assertArrayHasKey('messages', $missing);
        $this->assertArrayHasKey('farewell', $missing['messages']['keys']);
        $this->assertEquals('Goodbye', $missing['messages']['keys']['farewell']['source_value']);
    }
}
