<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Restaurant products
            ['name_en' => 'Cheeseburger', 'name_ar' => 'برجر الجبن', 'desc_en' => 'Delicious cheeseburger', 'desc_ar' => 'برجر بالجبن لذيذ'],
            ['name_en' => 'Pizza Margherita', 'name_ar' => 'بيتزا مارجريتا', 'desc_en' => 'Classic Italian pizza', 'desc_ar' => 'بيتزا إيطالية كلاسيكية'],
            ['name_en' => 'Grilled Chicken', 'name_ar' => 'دجاج مشوي', 'desc_en' => 'Tender grilled chicken', 'desc_ar' => 'دجاج مشوي طري'],

            // Perfume products
            ['name_en' => 'Eau de Cologne', 'name_ar' => 'أو دو كولن', 'desc_en' => 'Fresh fragrance', 'desc_ar' => 'رائحة منعشة'],
            ['name_en' => 'Perfume Spray', 'name_ar' => 'عطر بخاخ', 'desc_en' => 'Lasting fragrance', 'desc_ar' => 'رائحة دائمة'],
            ['name_en' => 'Essential Oil', 'name_ar' => 'زيت عطري', 'desc_en' => 'Pure essential oil', 'desc_ar' => 'زيت عطري نقي'],

            // Clothing products
            ['name_en' => 'Cotton T-Shirt', 'name_ar' => 'تي شيرت قطن', 'desc_en' => 'Comfortable cotton shirt', 'desc_ar' => 'تي شيرت قطن مريح'],
            ['name_en' => 'Blue Jeans', 'name_ar' => 'جينز أزرق', 'desc_en' => 'Classic blue jeans', 'desc_ar' => 'جينز أزرق كلاسيكي'],
            ['name_en' => 'Black Hoodie', 'name_ar' => 'هودي أسود', 'desc_en' => 'Warm hoodie', 'desc_ar' => 'هودي دافئ'],

            // Electronics products
            ['name_en' => 'USB Cable', 'name_ar' => 'كابل يو إس بي', 'desc_en' => 'Quality USB cable', 'desc_ar' => 'كابل يو إس بي عالي الجودة'],
            ['name_en' => 'Phone Charger', 'name_ar' => 'شاحن هاتف', 'desc_en' => 'Fast charger', 'desc_ar' => 'شاحن سريع'],
            ['name_en' => 'Screen Protector', 'name_ar' => 'حماية الشاشة', 'desc_en' => 'Tempered glass', 'desc_ar' => 'زجاج مقسى'],
        ];

        foreach ($products as $product) {
            Product::create([
                'name_en' => $product['name_en'],
                'name_ar' => $product['name_ar'],
                'description_en' => $product['desc_en'],
                'description_ar' => $product['desc_ar'],
            ]);
        }

        $this->command->info('Products created successfully!');
    }
}
