<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\TranslationUnitManager;
use App\Models\TranslationUnit;
use App\Models\TranslationUnitVersion;

class TranslationUnitManagerTest extends TestCase
{
    use RefreshDatabase;

    /** @var TranslationUnitManager */
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->manager = new TranslationUnitManager();
    }

    public function test_can_add_a_new_translation_unit()
    {
        $data = [
            'document_id' => 42,
            'segment_index' => 1,
            'source_text' => 'Test segment',
            'source_locale' => 'en',
            'target_locale' => 'fr',
        ];

        $unit = $this->manager->add($data);

        $this->assertInstanceOf(TranslationUnit::class, $unit);
        $this->assertDatabaseHas('translation_units', [
            'id' => $unit->id,
            'document_id' => 42,
            'segment_index' => 1,
            'source_text' => 'Test segment',
            'source_locale' => 'en',
            'target_locale' => 'fr',
        ]);
    }

    public function test_can_retrieve_a_translation_unit_by_id_with_versions()
    {
        $unit = TranslationUnit::factory()->create([
            'source_text' => 'Hello',
            'source_locale' => 'en',
            'target_locale' => 'es',
        ]);
        TranslationUnitVersion::factory()->create([
            'translation_unit_id' => $unit->id,
            'translated_text' => 'Hola',
            'edited_by' => 1,
            'version_number' => 1,
        ]);

        $retrieved = $this->manager->getById($unit->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals('Hello', $retrieved->source_text);
        $this->assertCount(1, $retrieved->versions);
        $this->assertEquals('Hola', $retrieved->versions->first()->translated_text);
    }

    public function test_can_update_a_translation_unit_and_keep_history()
    {
        $unit = TranslationUnit::factory()->create([
            'source_text' => 'Original',
            'source_locale' => 'en',
            'target_locale' => 'de',
        ]);

        $v1 = $this->manager->update($unit->id, 'Erstes', 1);
        $this->assertEquals(1, $v1->version_number);
        $this->assertDatabaseHas('translation_unit_versions', [
            'id' => $v1->id,
            'translated_text' => 'Erstes',
            'edited_by' => 1,
            'version_number' => 1,
        ]);

        $v2 = $this->manager->update($unit->id, 'Zweites', 2);
        $this->assertEquals(2, $v2->version_number);
        $this->assertDatabaseHas('translation_unit_versions', [
            'id' => $v2->id,
            'translated_text' => 'Zweites',
            'edited_by' => 2,
            'version_number' => 2,
        ]);
    }

    public function test_update_returns_null_for_nonexistent_unit()
    {
        $result = $this->manager->update(9999, 'Nothing', 1);
        $this->assertNull($result);
    }
}
