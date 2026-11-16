<?php

namespace Tests\Feature;

use App\Entry;
use Tests\TestCase;

/**
 * Regression tests for the Entry model's case relationship.
 *
 * Entry::cases() declared its foreign key as 'cases_id' while the entries table
 * has 'case_id', so the relation silently resolved to null. These tests pin the
 * relation to the real column and the entry -> case -> project path.
 *
 * Ported from Helge's mart-web tests.
 */
class EntryRelationsTest extends TestCase
{
    /** @test */
    public function it_resolves_the_case_relation_via_the_case_id_column()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);

        $this->assertNotNull(
            $entry->cases,
            'Entry::cases() resolved to null - check the relation uses case_id.'
        );
        $this->assertSame($this->case->id, $entry->cases->id);
    }

    /** @test */
    public function it_uses_case_id_as_the_relations_foreign_key()
    {
        $this->assertSame('case_id', (new Entry)->cases()->getForeignKeyName());
    }

    /** @test */
    public function it_reaches_the_project_through_the_case()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);

        $this->assertSame($this->project->id, $entry->cases->project->id);
    }

    /** @test */
    public function it_is_the_inverse_of_the_cases_entries_relation()
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id]);

        $this->assertTrue(
            $this->case->entries->contains($entry),
            'Cases::entries() and Entry::cases() disagree about the foreign key.'
        );
    }
}
