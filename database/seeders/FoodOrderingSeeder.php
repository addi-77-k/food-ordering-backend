<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FoodOrderingSeeder extends Seeder
{
    /**
     * Run the food ordering database seeds with Filipino delicacies and Peso currency.
     */
    public function run(): void
    {
        // 1. Seed Default Administrator
        Admin::firstOrCreate(
            ['email' => 'admin@foodordering.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
            ]
        );

        // Reset existing customer and menu catalogs so fresh Filipino data is cleanly applied
        Order::query()->delete();
        MenuItem::query()->delete();
        Category::query()->delete();
        Customer::query()->delete();

        // 2. Seed Filipino Food Categories
        $categoriesData = [
            [
                'name' => 'Mga Pangunahing Ulam',
                'slug' => 'mga-ulam',
                'description' => 'Tradisyonal at paboritong lutong-bahay na nilaga, sinabawan, at pinakulong ulam na may tunay na lasang Pinoy.',
                'image_url' => 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?w=800',
                'status' => 'active',
            ],
            [
                'name' => 'Inasal, Ihaw-Ihaw at Lechon',
                'slug' => 'inasal-at-ihaw',
                'description' => 'Mga paboritong inihaw sa nagbabagang uling na binabad sa espesyal na timpla, may garlic annatto oil at sarsa.',
                'image_url' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800',
                'status' => 'active',
            ],
            [
                'name' => 'Pancit at Merienda Espesyal',
                'slug' => 'pancit-at-merienda',
                'description' => 'Pampahaba ng buhay na pansit at mga paboritong meriendang Pinoy para sa pamilya at barkada.',
                'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?w=800',
                'status' => 'active',
            ],
            [
                'name' => 'Silog All-Day Meals',
                'slug' => 'silog-meals',
                'description' => 'Mainit na sinangag na may bawang, malasadong pritong itlog, at masasarap na ulam pampagana buong araw.',
                'image_url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800',
                'status' => 'active',
            ],
            [
                'name' => 'Mga Panghimagas',
                'slug' => 'mga-panghimagas',
                'description' => 'Mga katakam-takam at tradisyonal na minatamis na paborito ng bawat pamilyang Pilipino.',
                'image_url' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800',
                'status' => 'active',
            ],
            [
                'name' => 'Pampalamig at Inumin',
                'slug' => 'pampalamig-at-inumin',
                'description' => 'Sariwang buko juice, palamig, at matapang na barakong kape para pawiin ang uhaw at init.',
                'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=800',
                'status' => 'active',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::create($cat);
        }

        // 3. Seed Authentic Filipino Delicacies (Menu Items in Philippine Pesos)
        $menuItemsData = [
            // Mga Ulam
            [
                'category_slug' => 'mga-ulam',
                'name' => 'Sinigang na Baboy sa Sampalok',
                'description' => 'Malambot na liempo at tadyang na pinakuluan sa asim-kilig na sariwang sabaw ng sampalok na may kangkong, labanos, at siling haba.',
                'price' => 320.00,
                'image_url' => 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?w=800',
                'availability' => true,
                'preparation_time' => 20,
            ],
            [
                'category_slug' => 'mga-ulam',
                'name' => 'Special Chicken & Pork Adobo',
                'description' => 'Pambansang paborito! Manok at baboy na dahan-dahang pinakuluan sa toyo, sukang Iloko, bawang, paminta, at dahon ng laurel.',
                'price' => 295.00,
                'image_url' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=800',
                'availability' => true,
                'preparation_time' => 18,
            ],
            [
                'category_slug' => 'mga-ulam',
                'name' => 'Kare-Kareng Baka at Tuwalya',
                'description' => 'Katakam-takam na nilagang baka at tuwalya sa malapot na sarsang mani at atsuete, may kasamang talong, sitaw, pechay, at ginisang alamang.',
                'price' => 420.00,
                'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800',
                'availability' => true,
                'preparation_time' => 25,
            ],
            [
                'category_slug' => 'mga-ulam',
                'name' => 'Crispy Pata Espesyal (Pampamilya)',
                'description' => 'Malutong at ginintuang pata ng baboy na may malinamnam na laman sa loob, sinamahan ng maanghang na sawsawan na toyo at suka.',
                'price' => 680.00,
                'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800',
                'availability' => true,
                'preparation_time' => 25,
            ],

            // Inasal, Ihaw & Lechon
            [
                'category_slug' => 'inasal-at-ihaw',
                'name' => 'Bacolod Chicken Inasal (Paa)',
                'description' => 'Inihaw na hita ng manok na binabad sa sinamak vinegar, tanglad, at kalamansi, pinahiran ng mabangong chicken oil habang iniihaw.',
                'price' => 245.00,
                'image_url' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=800',
                'availability' => true,
                'preparation_time' => 15,
            ],
            [
                'category_slug' => 'inasal-at-ihaw',
                'name' => 'Inihaw na Liempo Plate',
                'description' => 'Makapal at makatas na hiwa ng liempo na inihaw sa uling na may matamis at maalat na timpla, may kasamang atchara.',
                'price' => 280.00,
                'image_url' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?w=800',
                'availability' => true,
                'preparation_time' => 15,
            ],
            [
                'category_slug' => 'inasal-at-ihaw',
                'name' => 'Pork Barbecue Skewers (3 Sticks)',
                'description' => 'Kalsada-style na pork barbecue na may matamis na sarsa at inihaw sa mabangong uling hanggang mag-caramelize.',
                'price' => 180.00,
                'image_url' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800',
                'availability' => true,
                'preparation_time' => 12,
            ],
            [
                'category_slug' => 'inasal-at-ihaw',
                'name' => 'Crispy Lechon Kawali',
                'description' => 'Napakalutong na balat at malambot na laman ng piniritong liempo, inihahain kasama ang orihinal na Mang Tomas sarsa.',
                'price' => 340.00,
                'image_url' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=800',
                'availability' => true,
                'preparation_time' => 15,
            ],

            // Pancit at Merienda
            [
                'category_slug' => 'pancit-at-merienda',
                'name' => 'Pancit Palabok Espesyal',
                'description' => 'Bihon noodles na binalot sa manamis-namis at malinamnam na sarsa ng hipon, tinapa flakes, dinurog na chicharon, at itlog.',
                'price' => 220.00,
                'image_url' => 'https://images.unsplash.com/photo-1612927601601-6638404737ce?w=800',
                'availability' => true,
                'preparation_time' => 14,
            ],
            [
                'category_slug' => 'pancit-at-merienda',
                'name' => 'Pancit Canton Guisado',
                'description' => 'Ginisang egg noodles na may sariwang gulay, hiwa ng baboy, hipon, at atay na niluto sa masarap na sabaw at toyo.',
                'price' => 210.00,
                'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=800',
                'availability' => true,
                'preparation_time' => 12,
            ],
            [
                'category_slug' => 'pancit-at-merienda',
                'name' => 'Crispy Lumpiang Shanghai (10 pcs)',
                'description' => 'Gintong malutong na spring rolls na pinalamanan ng giniling na baboy, karot, at singkamas, may sweet and sour dip.',
                'price' => 190.00,
                'image_url' => 'https://images.unsplash.com/photo-1541529086526-db283c563270?w=800',
                'availability' => true,
                'preparation_time' => 10,
            ],
            [
                'category_slug' => 'pancit-at-merienda',
                'name' => 'Tokwa\'t Baboy Plate',
                'description' => 'Piniritong tokwa at pinakuluang pisngi ng baboy na nilubog sa maanghang na sawsawan ng toyo, suka, sibuyas, at siling labuyo.',
                'price' => 175.00,
                'image_url' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800',
                'availability' => true,
                'preparation_time' => 10,
            ],

            // Silog All-Day Meals
            [
                'category_slug' => 'silog-meals',
                'name' => 'Classic Beef Tapsilog',
                'description' => 'Marinated beef sirloin tapa na may timplang toyo at bawang, kasama ang bawang sinangag at pritong itlog.',
                'price' => 195.00,
                'image_url' => 'https://images.unsplash.com/photo-1513442542250-854d436a73f2?w=800',
                'availability' => true,
                'preparation_time' => 12,
            ],
            [
                'category_slug' => 'silog-meals',
                'name' => 'Daing na Bangsilog',
                'description' => 'Boneless Dagupan bangus na binabad sa bawang at suka bago iprito nang malutong, kasama ang garlic rice at itlog.',
                'price' => 215.00,
                'image_url' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=800',
                'availability' => true,
                'preparation_time' => 12,
            ],
            [
                'category_slug' => 'silog-meals',
                'name' => 'Pampanga Sweet Tocilog',
                'description' => 'Malambot at manamis-namis na tocino ng Pampanga na pinalambot at pinirito, inihain kasama ang mainit na sinangag at itlog.',
                'price' => 185.00,
                'image_url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800',
                'availability' => true,
                'preparation_time' => 10,
            ],
            [
                'category_slug' => 'silog-meals',
                'name' => 'Lucban Garlic Longsilog',
                'description' => 'Mababangong longganisang Lucban na sagana sa bawang at paminta, kasama ang mainit na sinangag, itlog, at sukang maanghang.',
                'price' => 190.00,
                'image_url' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=800',
                'availability' => true,
                'preparation_time' => 10,
            ],

            // Mga Panghimagas
            [
                'category_slug' => 'mga-panghimagas',
                'name' => 'Halo-Halo Espesyal with Leche Flan & Ube',
                'description' => 'Pinong yelo na may minatamis na saging, kamote, kaong, nata de coco, pinipig, gatas, ube halaya, at isang slice ng leche flan.',
                'price' => 165.00,
                'image_url' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800',
                'availability' => true,
                'preparation_time' => 8,
            ],
            [
                'category_slug' => 'mga-panghimagas',
                'name' => 'Creamy Caramel Leche Flan',
                'description' => 'Tradisyonal at pino-pinong custard na gawa sa sariwang dilaw ng itlog at gatas na pinatungan ng ginintuang arnibal.',
                'price' => 140.00,
                'image_url' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=800',
                'availability' => true,
                'preparation_time' => 5,
            ],
            [
                'category_slug' => 'mga-panghimagas',
                'name' => 'Special Bibingka Galapong',
                'description' => 'Bagong lutong bibingka na may itlog na maalat, budbod ng sariwang niyog, at pinahiran ng mantikilya at asukal.',
                'price' => 150.00,
                'image_url' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?w=800',
                'availability' => true,
                'preparation_time' => 10,
            ],
            [
                'category_slug' => 'mga-panghimagas',
                'name' => 'Turon with Jackfruit & Langka (3 pcs)',
                'description' => 'Piniritong saging na saba na binalot sa lumpia wrapper kasama ang manamis-namis na langka at binudburan ng asukal na pula.',
                'price' => 110.00,
                'image_url' => 'https://images.unsplash.com/photo-1624353365286-3f8d62daad51?w=800',
                'availability' => true,
                'preparation_time' => 6,
            ],

            // Pampalamig at Inumin
            [
                'category_slug' => 'pampalamig-at-inumin',
                'name' => 'Sago\'t Gulaman Palamig',
                'description' => 'Malamig na palamig na gawa sa arnibal ng asukal na pula, pandan essence, malambot na sago, at pinitas na gulaman.',
                'price' => 75.00,
                'image_url' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=800',
                'availability' => true,
                'preparation_time' => 4,
            ],
            [
                'category_slug' => 'pampalamig-at-inumin',
                'name' => 'Buko Pandan Fresh Juice',
                'description' => 'Sariwang sabaw at kinayod na laman ng buko na may bango ng dahon ng pandan, inihain nang yelo-lamig.',
                'price' => 85.00,
                'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=800',
                'availability' => true,
                'preparation_time' => 4,
            ],
            [
                'category_slug' => 'pampalamig-at-inumin',
                'name' => 'Fresh Calamansi Iced Juice',
                'description' => 'Pinigang sariwang kalamansi na may pulot-pukyutan at yelo, mayaman sa bitamina C at pampasigla ng katawan.',
                'price' => 70.00,
                'image_url' => 'https://images.unsplash.com/photo-1622597467836-f3285f2131b7?w=800',
                'availability' => true,
                'preparation_time' => 4,
            ],
            [
                'category_slug' => 'pampalamig-at-inumin',
                'name' => 'Batangas Kapeng Barako (Mainit o Malamig)',
                'description' => 'Matapang at mabangong kapeng Liberica mula sa mga taniman ng Batangas, nilagang tradisyonal sa takuri.',
                'price' => 95.00,
                'image_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=800',
                'availability' => true,
                'preparation_time' => 5,
            ],
        ];

        $createdMenuItems = [];
        foreach ($menuItemsData as $item) {
            $category = $categories[$item['category_slug']] ?? null;
            if ($category) {
                $createdMenuItems[] = MenuItem::create([
                    'category_id' => (string) $category->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'image_url' => $item['image_url'],
                    'availability' => $item['availability'],
                    'preparation_time' => $item['preparation_time'],
                ]);
            }
        }

        // 4. Seed Authentic Filipino Customers
        $customersData = [
            [
                'name' => 'Juan dela Cruz',
                'email' => 'juan.delacruz@example.ph',
                'password' => Hash::make('password123'),
                'phone' => '+63 917 123 4567',
                'address' => 'Unit 12B, Salcedo Village, Makati City, Metro Manila',
                'status' => 'active',
            ],
            [
                'name' => 'Maria Clara Santos',
                'email' => 'maria.santos@example.ph',
                'password' => Hash::make('password123'),
                'phone' => '+63 918 234 5678',
                'address' => '45 Katipunan Avenue, Loyola Heights, Quezon City',
                'status' => 'active',
            ],
            [
                'name' => 'Jose Rizalino Bautista',
                'email' => 'jose.bautista@example.ph',
                'password' => Hash::make('password123'),
                'phone' => '+63 920 345 6789',
                'address' => '88 Escolta Street, Binondo, Manila',
                'status' => 'active',
            ],
            [
                'name' => 'Angelica Dimagiba',
                'email' => 'angelica.dimagiba@example.ph',
                'password' => Hash::make('password123'),
                'phone' => '+63 927 456 7890',
                'address' => '24 Emerald Avenue, Ortigas Center, Pasig City',
                'status' => 'active',
            ],
            [
                'name' => 'Bayani Fernando',
                'email' => 'bayani.fernando@example.ph',
                'password' => Hash::make('password123'),
                'phone' => '+63 935 567 8901',
                'address' => '15 Gorordo Avenue, Lahug, Cebu City',
                'status' => 'active',
            ],
        ];

        $customers = [];
        foreach ($customersData as $cData) {
            $customers[] = Customer::create($cData);
        }

        // 5. Seed Sample Orders with Filipino Pricing (PHP)
        $orderStatuses = [
            'pending',
            'confirmed',
            'preparing',
            'ready',
            'out_for_delivery',
            'completed',
            'completed',
            'completed',
            'cancelled',
            'pending',
        ];

        if (! empty($createdMenuItems) && ! empty($customers)) {
            foreach ($orderStatuses as $idx => $targetStatus) {
                $customer = $customers[$idx % count($customers)];
                $item1 = $createdMenuItems[$idx % count($createdMenuItems)];
                $item2 = $createdMenuItems[($idx + 3) % count($createdMenuItems)];

                $orderNumber = 'ORD-2026-'.sprintf('%04d', $idx + 1);

                $qty1 = ($idx % 2) + 1;
                $qty2 = (($idx + 1) % 2) + 1;

                $snap1 = [
                    'menu_item_id' => (string) $item1->id,
                    'name' => $item1->name,
                    'quantity' => $qty1,
                    'unit_price' => (float) $item1->price,
                    'subtotal' => round($item1->price * $qty1, 2),
                ];

                $snap2 = [
                    'menu_item_id' => (string) $item2->id,
                    'name' => $item2->name,
                    'quantity' => $qty2,
                    'unit_price' => (float) $item2->price,
                    'subtotal' => round($item2->price * $qty2, 2),
                ];

                $subtotal = $snap1['subtotal'] + $snap2['subtotal'];
                $deliveryFee = 49.00; // ₱49 standard delivery fee
                $discount = ($idx % 3 === 0) ? 50.00 : 0.00; // ₱50 discount
                $total = max(0.0, round($subtotal + $deliveryFee - $discount, 2));

                $paymentMethod = ($idx % 2 === 0) ? 'card' : 'cash_on_delivery';
                $paymentStatus = in_array($targetStatus, ['completed', 'out_for_delivery', 'ready'], true) ? 'paid' : 'pending';

                // Build status lifecycle history
                $statusFlow = ['pending'];
                if ($targetStatus !== 'pending') {
                    if ($targetStatus === 'cancelled') {
                        $statusFlow[] = 'cancelled';
                    } else {
                        $steps = ['confirmed', 'preparing', 'ready', 'out_for_delivery', 'completed'];
                        foreach ($steps as $s) {
                            $statusFlow[] = $s;
                            if ($s === $targetStatus) {
                                break;
                            }
                        }
                    }
                }

                $history = [];
                foreach ($statusFlow as $stepIdx => $s) {
                    $history[] = [
                        'from' => $stepIdx === 0 ? null : $statusFlow[$stepIdx - 1],
                        'status' => $s,
                        'comment' => "Order transitioned to {$s}",
                        'created_at' => now()->subMinutes((count($statusFlow) - $stepIdx) * 15)->toIso8601String(),
                        'actor' => $stepIdx === 0 ? 'customer' : 'admin',
                    ];
                }

                $filipinoNotes = [
                    'Paki-iwan po sa may lobby guard, tatawagan ko po pagbaba.',
                    'Pakidagdagan po ng sili at kalamansi.',
                    'Pakibilisan po at gutom na ang mga bata, salamat!',
                    'Paki-buzz po ang doorbell sa gate pagdating.',
                    'Pakisabi sa rider mag-ingat sa daan, salamat po.',
                ];

                Order::create([
                    'customer_id' => (string) $customer->id,
                    'order_number' => $orderNumber,
                    'items' => [$snap1, $snap2],
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount' => $discount,
                    'total' => $total,
                    'delivery_address' => $customer->address,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'order_status' => $targetStatus,
                    'notes' => $filipinoNotes[$idx % count($filipinoNotes)],
                    'status_history' => $history,
                ]);
            }
        }
    }
}
