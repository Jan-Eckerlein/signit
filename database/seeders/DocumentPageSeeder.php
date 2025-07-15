<?php

namespace Database\Seeders;

use App\Models\DocumentPage;
use Illuminate\Database\Seeder;

class DocumentPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentPage::factory()->count(10)->create();
    }
}
