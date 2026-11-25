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

    public function test_can_create_language_with_blank_values_when_no_duplication_requested()
    {
        $service = new LanguageService();

        $enPath = $this->tempLangPath . '/en';
        File::makeDirectory($enPath, 0755, true);
        File::put($enPath . '/auth.php', "<?php return ['customer_created' => 'Welcome customer', 'failed' => 'Failed msg'];");
        File::put($this->tempLangPath . '/en.json', json_encode([' - Your plan has expired' => ' - Your plan has expired', ' Days' => ' Days']));

        // When copy_from is 'none'
        $service->createLanguage('German', 'de', 'none');

        $this->assertDirectoryExists($this->tempLangPath . '/de');
        $this->assertFileExists($this->tempLangPath . '/de/auth.php');
        $this->assertFileExists($this->tempLangPath . '/de.json');

        // Check PHP array values are empty strings
        $deAuth = File::getRequire($this->tempLangPath . '/de/auth.php');
        $this->assertEquals([
            'customer_created' => '',
            'failed' => '',
        ], $deAuth);

        // Check JSON values are empty strings
        $deJson = json_decode(File::get($this->tempLangPath . '/de.json'), true);
        $this->assertEquals([
            ' - Your plan has expired' => '',
            ' Days' => '',
        ], $deJson);

        $service->deleteLanguage('de');
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

    public function test_ignores_and_purges_invalid_locales()
    {
        $service = new LanguageService();

        // Valid locales
        File::put($this->tempLangPath . '/en.json', json_encode(['Hello' => 'Hello']));
        File::put($this->tempLangPath . '/pt_BR.json', json_encode(['Hello' => 'Olá']));

        // Invalid rogue file and directory (such as translation sentences misnamed as filenames)
        $rogueFile = $this->tempLangPath . '/Attenzione!Alcune card design NFC sono in quantitÃ  inferiore a 10!.json';
        File::put($rogueFile, json_encode(['foo' => 'bar']));
        $rogueDir = $this->tempLangPath . '/Invalid Directory Name!';
        File::makeDirectory($rogueDir, 0755, true);

        // getLanguages should filter them out and purge them
        $languages = $service->getLanguages();

        $this->assertArrayHasKey('en', $languages);
        $this->assertArrayHasKey('pt_BR', $languages);
        $this->assertArrayNotHasKey('Attenzione!Alcune card design NFC sono in quantitÃ  inferiore a 10!', $languages);
        $this->assertArrayNotHasKey('Invalid Directory Name!', $languages);

        $this->assertFileDoesNotExist($rogueFile);
        $this->assertDirectoryDoesNotExist($rogueDir);
    }

    public function test_sync_missing_keys_stops_when_stop_callback_returns_true()
    {
        $reader = new TranslationReaderService();
        $writer = new TranslationWriterService();
        $missingService = new MissingTranslationService($reader, $writer);

        $enPath = $this->tempLangPath . '/en';
        File::makeDirectory($enPath, 0755, true);
        File::put($enPath . '/messages.php', "<?php return ['one' => 'One', 'two' => 'Two', 'three' => 'Three'];");

        $esPath = $this->tempLangPath . '/es';
        File::makeDirectory($esPath, 0755, true);
        File::put($esPath . '/messages.php', "<?php return [];");

        // Stop callback immediately returns true
        $completed = $missingService->syncMissingKeys('en', 'es', null, 50, fn() => true);

        // When stopped, it should return false
        $this->assertFalse($completed);
    }
}
