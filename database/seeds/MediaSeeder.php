<?php

use App\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creating media items...');

        $mediaItems = [
            // Digital Devices/Media
            [
                'name' => 'Smartphone',
                'description' => 'Mobile phone with internet connectivity and apps',
                'properties' => json_encode(['color' => '#FF6B6B', 'category' => 'digital']),
            ],
            [
                'name' => 'Tablet',
                'description' => 'Touch screen tablet device',
                'properties' => json_encode(['color' => '#4ECDC4', 'category' => 'digital']),
            ],
            [
                'name' => 'Laptop',
                'description' => 'Portable computer',
                'properties' => json_encode(['color' => '#45B7D1', 'category' => 'digital']),
            ],
            [
                'name' => 'Desktop Computer',
                'description' => 'Stationary personal computer',
                'properties' => json_encode(['color' => '#96CEB4', 'category' => 'digital']),
            ],
            [
                'name' => 'Smart TV',
                'description' => 'Internet-connected television',
                'properties' => json_encode(['color' => '#FFEAA7', 'category' => 'digital']),
            ],
            [
                'name' => 'Gaming Console',
                'description' => 'Video game console (PlayStation, Xbox, Nintendo Switch)',
                'properties' => json_encode(['color' => '#DDA0DD', 'category' => 'digital']),
            ],
            [
                'name' => 'Smart Speaker',
                'description' => 'Voice-activated speaker (Alexa, Google Home)',
                'properties' => json_encode(['color' => '#FFB6C1', 'category' => 'digital']),
            ],
            [
                'name' => 'E-Reader',
                'description' => 'Electronic book reader (Kindle, etc.)',
                'properties' => json_encode(['color' => '#87CEEB', 'category' => 'digital']),
            ],

            // Traditional Media
            [
                'name' => 'Radio',
                'description' => 'Traditional radio receiver',
                'properties' => json_encode(['color' => '#F0E68C', 'category' => 'traditional']),
            ],
            [
                'name' => 'Television',
                'description' => 'Traditional television set',
                'properties' => json_encode(['color' => '#CD853F', 'category' => 'traditional']),
            ],
            [
                'name' => 'Books',
                'description' => 'Physical printed books',
                'properties' => json_encode(['color' => '#8FBC8F', 'category' => 'traditional']),
            ],
            [
                'name' => 'Magazines',
                'description' => 'Print magazines and periodicals',
                'properties' => json_encode(['color' => '#DEB887', 'category' => 'traditional']),
            ],
            [
                'name' => 'Newspapers',
                'description' => 'Daily or weekly newspapers',
                'properties' => json_encode(['color' => '#A9A9A9', 'category' => 'traditional']),
            ],

            // Audio Equipment
            [
                'name' => 'Headphones',
                'description' => 'Personal audio headphones',
                'properties' => json_encode(['color' => '#FF69B4', 'category' => 'audio']),
            ],
            [
                'name' => 'Bluetooth Speaker',
                'description' => 'Portable wireless speaker',
                'properties' => json_encode(['color' => '#20B2AA', 'category' => 'audio']),
            ],
            [
                'name' => 'Hi-Fi System',
                'description' => 'High-fidelity audio system',
                'properties' => json_encode(['color' => '#B8860B', 'category' => 'audio']),
            ],

            // Work/Study Tools (for entity-based studies)
            [
                'name' => 'Notebook',
                'description' => 'Physical writing notebook',
                'properties' => json_encode(['color' => '#F4A460', 'category' => 'work']),
            ],
            [
                'name' => 'Whiteboard',
                'description' => 'Writing and planning whiteboard',
                'properties' => json_encode(['color' => '#FFFFFF', 'category' => 'work']),
            ],
            [
                'name' => 'Sticky Notes',
                'description' => 'Post-it notes for reminders',
                'properties' => json_encode(['color' => '#FFFF99', 'category' => 'work']),
            ],
            [
                'name' => 'Desk Organizer',
                'description' => 'Desktop organization system',
                'properties' => json_encode(['color' => '#D2B48C', 'category' => 'work']),
            ],

            // Food Items (for food entity studies)
            [
                'name' => 'Coffee',
                'description' => 'Hot coffee beverage',
                'properties' => json_encode(['color' => '#8B4513', 'category' => 'beverage']),
            ],
            [
                'name' => 'Tea',
                'description' => 'Hot tea beverage',
                'properties' => json_encode(['color' => '#9ACD32', 'category' => 'beverage']),
            ],
            [
                'name' => 'Water',
                'description' => 'Plain water',
                'properties' => json_encode(['color' => '#00BFFF', 'category' => 'beverage']),
            ],
            [
                'name' => 'Snacks',
                'description' => 'Light snacks and finger foods',
                'properties' => json_encode(['color' => '#FFD700', 'category' => 'food']),
            ],
            [
                'name' => 'Fruits',
                'description' => 'Fresh fruits',
                'properties' => json_encode(['color' => '#FF6347', 'category' => 'food']),
            ],
            [
                'name' => 'Vegetables',
                'description' => 'Fresh vegetables',
                'properties' => json_encode(['color' => '#32CD32', 'category' => 'food']),
            ],
            [
                'name' => 'Sandwich',
                'description' => 'Prepared sandwich',
                'properties' => json_encode(['color' => '#F5DEB3', 'category' => 'food']),
            ],
            [
                'name' => 'Pasta',
                'description' => 'Pasta dishes',
                'properties' => json_encode(['color' => '#FFEBCD', 'category' => 'food']),
            ],
            [
                'name' => 'Rice',
                'description' => 'Rice-based meals',
                'properties' => json_encode(['color' => '#FFF8DC', 'category' => 'food']),
            ],
            [
                'name' => 'Soup',
                'description' => 'Hot soup meals',
                'properties' => json_encode(['color' => '#FFA07A', 'category' => 'food']),
            ],

            // Wearable Technology
            [
                'name' => 'Smartwatch',
                'description' => 'Wearable smart device',
                'properties' => json_encode(['color' => '#483D8B', 'category' => 'wearable']),
            ],
            [
                'name' => 'Fitness Tracker',
                'description' => 'Activity monitoring device',
                'properties' => json_encode(['color' => '#00CED1', 'category' => 'wearable']),
            ],

            // Transportation Media
            [
                'name' => 'Car Audio',
                'description' => 'In-vehicle entertainment system',
                'properties' => json_encode(['color' => '#2F4F4F', 'category' => 'transport']),
            ],
            [
                'name' => 'Public Transport Screen',
                'description' => 'Digital displays in buses/trains',
                'properties' => json_encode(['color' => '#708090', 'category' => 'transport']),
            ],
        ];

        foreach ($mediaItems as $media) {
            // Check if media already exists to avoid duplicates
            $existing = Media::where('name', $media['name'])->first();
            if (! $existing) {
                Media::create($media);
                $this->command->info("Created media: {$media['name']}");
            } else {
                $this->command->info("Media already exists: {$media['name']}");
            }
        }

        $this->command->info('Media seeding completed!');
    }
}
