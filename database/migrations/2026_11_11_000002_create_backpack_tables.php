<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('default_backpack_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100)->index();
            $table->string('item_name', 255);
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        Schema::create('tour_backpack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
            $table->string('category', 100)->index();
            $table->string('item_name', 255);
            $table->boolean('is_packed')->default(false);
            $table->boolean('is_custom')->default(false);
            $table->timestamps();

            $table->index(['tour_id', 'category']);
        });

        // Seed the 20 Categories and their default 8 items from the Reference PDF
        $catalog = [
            'Beach & Island Tours' => [
                'Swimsuit',
                'Flip-flops',
                'Sunscreen',
                'Waterproof phone pouch',
                'Sunglasses',
                'Hat',
                'Beach towel',
                'Water bottle',
            ],
            'Mountain & Hiking Tours' => [
                'Hiking backpack',
                'First-aid kit',
                'Hiking boots',
                'Water bottle',
                'Trekking poles',
                'Energy bars',
                'Warm jacket',
                'Raincoat',
            ],
            'Adventure & Extreme Sports Tours' => [
                'Safety helmet',
                'First-aid kit',
                'Protective gloves',
                'Water bottle',
                'Sports shoes',
                'Action camera',
                'Knee pads',
                'Energy snacks',
            ],
            'Cultural & Heritage Tours' => [
                'Comfortable clothes',
                'Notebook',
                'Walking shoes',
                'Water bottle',
                'Camera',
                'Small backpack',
                'Sunglasses',
                'Travel guidebook',
            ],
            'Historical & Archaeological Tours' => [
                'Comfortable shoes',
                'Water bottle',
                'Camera',
                'Notebook',
                'Hat',
                'Power bank',
                'Sunscreen',
                'Small backpack',
            ],
            'Wildlife & Safari Tours' => [
                'Binoculars',
                'Sunscreen',
                'Camera',
                'Insect repellent',
                'Neutral-colored clothes',
                'Water bottle',
                'Hat',
                'Comfortable shoes',
            ],
            'City Sightseeing Tours' => [
                'Comfortable shoes',
                'Wallet',
                'Smartphone',
                'Water bottle',
                'Power bank',
                'Small backpack',
                'Sunglasses',
                'Travel map',
            ],
            'Food & Culinary Tours' => [
                'Comfortable clothes',
                'Camera',
                'Hand sanitizer',
                'Notebook',
                'Wet wipes',
                'Comfortable shoes',
                'Water bottle',
                'Personal medication',
            ],
            'Camping & Nature Tours' => [
                'Tent',
                'Portable stove',
                'Sleeping bag',
                'First-aid kit',
                'Camping mat',
                'Insect repellent',
                'Flashlight',
                'Water bottle',
            ],
            'Religious & Spiritual Tours' => [
                'Modest clothing',
                'Personal toiletries',
                'Prayer mat',
                'Water bottle',
                'Prayer clothes',
                'Small bag',
                'Comfortable sandals',
                'Travel documents',
            ],
            'Road Trips & Scenic Tours' => [
                'Travel pillow',
                'Water bottle',
                'Sunglasses',
                'Camera',
                'Power bank',
                'First-aid kit',
                'Snacks',
                'Comfortable clothes',
            ],
            'Luxury & Resort Tours' => [
                'Formal outfits',
                'Toiletries',
                'Casual clothes',
                'Swimwear',
                'Sunglasses',
                'Travel documents',
                'Skincare products',
                'Camera',
            ],
            'Cruise & Sea Tours' => [
                'Passport',
                'Sunglasses',
                'Swimwear',
                'Formal outfit',
                'Sunscreen',
                'Comfortable shoes',
                'Seasickness medication',
                'Waterproof bag',
            ],
            'Winter & Snow Tours' => [
                'Thermal wear',
                'Snow boots',
                'Winter jacket',
                'Scarf',
                'Gloves',
                'Beanie',
                'Woolen socks',
                'Moisturizer',
            ],
            'Family & Theme Park Tours' => [
                'Comfortable shoes',
                'Sunscreen',
                'Extra clothes',
                'Power bank',
                'Snacks',
                'Wet wipes',
                'Water bottles',
                'Small first-aid kit',
            ],
            'Scuba Diving & Snorkeling Tours' => [
                'Diving mask',
                'Waterproof camera',
                'Snorkel',
                'Quick-dry towel',
                'Swimwear',
                'Sunscreen',
                'Diving fins',
                'Waterproof bag',
            ],
            'Cycling & Mountain Biking Tours' => [
                'Cycling helmet',
                'Repair kit',
                'Cycling gloves',
                'Spare tube',
                'Sportswear',
                'Water bottle',
                'Cycling shoes',
                'Energy snacks',
            ],
            'Photography & Nature Tours' => [
                'DSLR or mirrorless camera',
                'Memory cards',
                'Extra lenses',
                'Camera cleaning kit',
                'Tripod',
                'Power bank',
                'Extra batteries',
                'Weatherproof backpack',
            ],
            'Desert & Dune Safari Tours' => [
                'Lightweight clothing',
                'Scarf',
                'Sunglasses',
                'Comfortable boots',
                'Sunscreen',
                'Water bottle',
                'Wide-brim hat',
                'Lip balm',
            ],
            'Skiing & Snowboarding Tours' => [
                'Ski jacket',
                'Thermal base layers',
                'Ski pants',
                'Ski socks',
                'Ski goggles',
                'Helmet',
                'Snow gloves',
                'Waterproof boots',
            ],
        ];

        $now = now();
        $rows = [];
        foreach ($catalog as $cat => $items) {
            foreach ($items as $index => $item) {
                $rows[] = [
                    'category' => $cat,
                    'item_name' => $item,
                    'order_index' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('default_backpack_items')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_backpack_items');
        Schema::dropIfExists('default_backpack_items');
    }
};
