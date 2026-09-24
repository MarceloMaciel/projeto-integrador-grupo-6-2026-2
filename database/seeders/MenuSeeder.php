<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'name' => 'Carne',
                'slug' => 'carne',
                'description' => 'Pratos orientais com carne bovina selecionada e legumes frescos.',
                'order' => 1,
                'items' => [
                    [
                        'code' => '38',
                        'name' => 'Carnes c/ Legumes Especial',
                        'description' => 'Carne bovina, frango, acelga, couve-flor, cebola, brócolis, repolho, cenoura e ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 50.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 38.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '39',
                        'name' => 'Carne com Brócolis',
                        'description' => 'Carne bovina, brócolis, finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 53.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 38.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '40',
                        'name' => 'Carne com Brócolis Especial',
                        'description' => 'Carne bovina, brócolis, pimentão vermelho, champignon, finalizado com molho shoyu e molho de ostra.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 58.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '42',
                        'name' => 'Carne com Legumes',
                        'description' => 'Carne bovina, acelga, couve-flor, cebola, brócolis, cenoura, repolho, ovo de codorna, finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 58.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 40.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '43',
                        'name' => 'Carne com Cebola',
                        'description' => 'Carne bovina e cebola, finalizado com molho à base de shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 50.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '94',
                        'name' => 'Carne ao Molho Curry',
                        'description' => 'Carne bovina, cenoura, cebola, couve-flor e repolho. Finalizado com molho curry (Picante).',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 50.00, 'order' => 1],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Frutos do Mar',
                'slug' => 'frutos-do-mar',
                'description' => 'Especialidades orientais com pescados frescos e camarões selecionados.',
                'order' => 2,
                'items' => [
                    [
                        'code' => '48',
                        'name' => 'Filé de Peixe ao Molho de Gengibre',
                        'description' => 'Filé de pescada frita e empanada, finalizado com molho shoyu, gengibre e cebolinha.',
                        'variations' => [
                            ['name' => 'Padrão', 'price' => 60.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '50',
                        'name' => 'Filé de Peixe com Legumes',
                        'description' => 'Filé de pescada frita e empanada, brócolis, couve-flor, cebola, pimentão, acelga, finalizado com molho shoyu e saquê.',
                        'variations' => [
                            ['name' => 'Padrão', 'price' => 70.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '53',
                        'name' => 'Filé de Peixe ao Molho Apimentado',
                        'description' => 'Filé de pescada frita e empanada, gengibre, pimentão vermelho e cebolinha ao molho apimentado.',
                        'variations' => [
                            ['name' => 'Padrão', 'price' => 70.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '54',
                        'name' => 'Camarão ao Molho Apimentado',
                        'description' => 'Camarão, gengibre, pimentão, cebolinha, finalizado com molho de massa de tomate, pimenta e saquê.',
                        'variations' => [
                            ['name' => 'Padrão', 'price' => 95.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '65',
                        'name' => 'Camarão com Legumes',
                        'description' => 'Camarão, brócolis, couve-flor, pimentão, cebola, acelga, kani-kama, finalizado com molho shoyu e saquê.',
                        'variations' => [
                            ['name' => 'Padrão', 'price' => 150.00, 'order' => 1],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Bebidas',
                'slug' => 'bebidas',
                'description' => 'Refrigerantes, cervejas, sucos naturais em lata e águas.',
                'order' => 3,
                'items' => [
                    [
                        'code' => null,
                        'name' => 'Coca-Cola 2L',
                        'description' => 'Refrigerante Coca-Cola garrafa 2 Litros.',
                        'variations' => [
                            ['name' => '2 Litros', 'price' => 20.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Refrigerante Sabores 2L',
                        'description' => 'Guaraná Antarctica, Fanta Laranja, Fanta Uva ou Sprite (garrafa 2 Litros).',
                        'variations' => [
                            ['name' => '2 Litros', 'price' => 18.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Refrigerante 1L',
                        'description' => 'Coca-Cola Original ou Guaraná Antarctica garrafa 1 Litro.',
                        'variations' => [
                            ['name' => '1 Litro', 'price' => 15.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Refrigerante 600ml',
                        'description' => 'Coca-Cola ou Sprite garrafa de 600ml.',
                        'variations' => [
                            ['name' => '600ml', 'price' => 12.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Refrigerante Lata 350ml',
                        'description' => 'Coca-Cola, Guaraná Antarctica, Fanta Laranja, Fanta Uva ou Sprite (lata 350ml).',
                        'variations' => [
                            ['name' => 'Lata 350ml', 'price' => 9.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'H2OH! 500ml',
                        'description' => 'H2OH nos sabores Limão ou Limoneto (garrafa 500ml).',
                        'variations' => [
                            ['name' => '500ml', 'price' => 9.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Suco Lata Del Valle',
                        'description' => 'Sabores: Pêssego, Uva, Goiaba, Manga ou Maracujá.',
                        'variations' => [
                            ['name' => 'Lata 290ml', 'price' => 8.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Cerveja Garrafa 600ml',
                        'description' => 'Original ou Heineken garrafa 600ml.',
                        'variations' => [
                            ['name' => 'Garrafa 600ml', 'price' => 18.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Cerveja Lata 350ml',
                        'description' => 'Original ou Heineken lata 350ml.',
                        'variations' => [
                            ['name' => 'Lata 350ml', 'price' => 8.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Água com Gás 500ml',
                        'description' => 'Água mineral com gás 500ml.',
                        'variations' => [
                            ['name' => '500ml', 'price' => 7.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Água Mineral 500ml',
                        'description' => 'Água mineral sem gás 500ml.',
                        'variations' => [
                            ['name' => '500ml', 'price' => 5.00, 'order' => 1],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Adicionais',
                'slug' => 'adicionais',
                'description' => 'Molhos especiais e adicionais para acompanhar seu prato.',
                'order' => 4,
                'items' => [
                    [
                        'code' => null,
                        'name' => 'Molho Agridoce',
                        'description' => 'Porção extra de molho vermelho agridoce tradicional.',
                        'variations' => [
                            ['name' => 'Porção', 'price' => 5.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => null,
                        'name' => 'Molho Gengibre',
                        'description' => 'Porção extra de molho à base de shoyu e gengibre fresco.',
                        'variations' => [
                            ['name' => 'Porção', 'price' => 5.00, 'order' => 1],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Entradas e Porções',
                'slug' => 'entradas-e-porcoes',
                'description' => 'Petiscos crocantes, guiozas, bolinhos e porções para compartilhar.',
                'order' => 5,
                'items' => [
                    [
                        'code' => '10',
                        'name' => 'Guioza Suíno',
                        'description' => 'Recheio: lombo suíno, acelga e gengibre. Acompanha molho à base de shoyu, gengibre e cebolinha.',
                        'variations' => [
                            ['name' => 'Inteira (12 unidades)', 'price' => 40.00, 'description' => '12 unidades', 'order' => 1],
                            ['name' => '1/2 Porção (6 unidades)', 'price' => 30.00, 'description' => '6 unidades', 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '11',
                        'name' => 'Mandiopã',
                        'description' => 'Petisco frito crocante feito de fécula de mandioca.',
                        'variations' => [
                            ['name' => 'Inteira', 'price' => 25.00, 'order' => 1],
                            ['name' => '1/2 Porção', 'price' => 18.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => 'OV',
                        'name' => 'Ovo de Codorna Porção',
                        'description' => 'Ovo de codorna cozido e frito, finalizado com molho à base de shoyu.',
                        'variations' => [
                            ['name' => 'Inteira (18 unidades)', 'price' => 24.00, 'description' => '18 unidades', 'order' => 1],
                        ],
                    ],
                    [
                        'code' => 'BA',
                        'name' => 'Batata Frita',
                        'description' => 'Batata Palito (McCain).',
                        'variations' => [
                            ['name' => 'Inteira (500g in Natura)', 'price' => 32.00, 'description' => '500g in Natura', 'order' => 1],
                            ['name' => '1/2 Porção (250g in Natura)', 'price' => 23.00, 'description' => '250g in Natura', 'order' => 2],
                        ],
                    ],
                    [
                        'code' => 'BO',
                        'name' => 'Bolinho de Bacalhau',
                        'description' => 'Massa com base de batata e peixe bacalhau selecionado.',
                        'variations' => [
                            ['name' => 'Inteira (12 unidades)', 'price' => 40.00, 'description' => '12 unidades', 'order' => 1],
                            ['name' => '1/2 Porção (8 unidades)', 'price' => 30.00, 'description' => '8 unidades', 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '17',
                        'name' => 'Frango Frito (Porção)',
                        'description' => 'Filé de coxa desossada, sem pele, temperada e empanada.',
                        'variations' => [
                            ['name' => 'Inteira', 'price' => 53.00, 'order' => 1],
                            ['name' => '1/2 Porção', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '34',
                        'name' => 'Lombinho Agridoce (Porção)',
                        'description' => 'Carne suína temperada, empanada e finalizada com molho vermelho agridoce.',
                        'variations' => [
                            ['name' => 'Inteira', 'price' => 55.00, 'order' => 1],
                            ['name' => '1/2 Porção', 'price' => 38.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '35',
                        'name' => 'Lombinho Frito (Porção)',
                        'description' => 'Carne suína temperada e empanada.',
                        'variations' => [
                            ['name' => 'Inteira', 'price' => 53.00, 'order' => 1],
                            ['name' => '1/2 Porção', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Yakisoba',
                'slug' => 'yakisoba',
                'description' => 'Autêntico yakisoba japonês com massas artesanais, carnes e legumes grelhados.',
                'order' => 6,
                'items' => [
                    [
                        'code' => '99',
                        'name' => 'Yakisoba Clássico',
                        'description' => 'Macarrão, frango, carne bovina, brócolis, cenoura, couve-flor, acelga, ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 50.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '97',
                        'name' => 'Yakisoba Especial',
                        'description' => 'Macarrão, camarão, frango, carne bovina, brócolis, cenoura, couve-flor, acelga, ovo de codorna, champignon. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 70.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 47.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '99C',
                        'name' => 'Yakisoba Carne',
                        'description' => 'Macarrão, carne bovina, brócolis, cenoura, couve-flor, acelga, ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 53.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '99F',
                        'name' => 'Yakisoba Frango',
                        'description' => 'Macarrão, frango, brócolis, cenoura, couve-flor, acelga, ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 45.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 35.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '98',
                        'name' => 'Yakisoba Camarão',
                        'description' => 'Macarrão, camarão, brócolis, cenoura, couve-flor, acelga, ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 65.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 45.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '96',
                        'name' => 'Yakisoba Vegetariano',
                        'description' => 'Macarrão, brócolis, cenoura, couve-flor, acelga, ovo de codorna, champignon. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 45.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 32.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '95',
                        'name' => 'Yakisoba Sem Legumes',
                        'description' => 'Macarrão, frango, carne bovina, ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 45.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 32.00, 'order' => 2],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Frango',
                'slug' => 'frango',
                'description' => 'Pratos com cortes selecionados de frango, molhos e temperos orientais.',
                'order' => 7,
                'items' => [
                    [
                        'code' => '12',
                        'name' => 'Frango ao Molho de Gengibre e Brócolis',
                        'description' => 'Filé de coxa desossada, sem pele e empanada com brócolis. Finalizado com molho à base de shoyu, limão, gengibre e cebolinha.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 65.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 45.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '15',
                        'name' => 'Frango c/ Gengibre',
                        'description' => 'Filé de coxa desossada, sem pele e empanada. Finalizado com molho base de shoyu, gengibre e cebolinha.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 58.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 42.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '16',
                        'name' => 'Filé de Frango c/ Brócolis e Champignon',
                        'description' => 'Filé de peito de frango, brócolis, champignons e pimentão vermelho. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 53.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '17',
                        'name' => 'Frango Frito (Prato Principal)',
                        'description' => 'Filé de coxa desossada, sem pele, temperada e empanada.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 53.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '20',
                        'name' => 'Frango com Legumes',
                        'description' => 'Cubos de peito de frango, acelga, cenoura, couve-flor, cebola, repolho, ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 48.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 35.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '22',
                        'name' => 'Frango ao Molho Curry',
                        'description' => 'Cubos de peito de frango, cenoura, couve-flor, cebola, repolho. Finalizado com molho curry picante.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 45.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '23',
                        'name' => 'Frango Xadrez',
                        'description' => 'Cubos de peito de frango, salsão, pimentão, cebola, ovo de codorna, amendoim. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 53.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Lombo',
                'slug' => 'lombo',
                'description' => 'Pratos com lombo suíno de alta qualidade, preparados com legumes e molhos orientais.',
                'order' => 8,
                'items' => [
                    [
                        'code' => '26',
                        'name' => 'Lombinho ao Molho Missô',
                        'description' => 'Carne suína fatiada, pimentão vermelho, repolho, finalizado com molho missô (agridoce).',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 47.00, 'order' => 1],
                            ['name' => 'Médio', 'price' => 35.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '27',
                        'name' => 'Lombinho Picante',
                        'description' => 'Carne suína, salsão, pimentão. Finalizado com molho shoyu (levemente apimentado).',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 46.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '31',
                        'name' => 'Lombo Xadrez',
                        'description' => 'Carne suína, salsão, cebola e pimentões, finalizado com molho shoyu (levemente apimentado).',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 50.00, 'order' => 1],
                            ['name' => 'Médio', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '32',
                        'name' => 'Lombo e Frango Xadrez ao Molho Missô',
                        'description' => 'Carne suína, frango, salsão, cebola, pimentão, finalizado com molho missô (agridoce).',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 50.00, 'order' => 1],
                        ],
                    ],
                    [
                        'code' => '34',
                        'name' => 'Lombinho Agridoce (Prato Principal)',
                        'description' => 'Carne suína temperada, empanada e finalizada com molho vermelho agridoce.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 55.00, 'order' => 1],
                            ['name' => 'Médio', 'price' => 38.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '35',
                        'name' => 'Lombinho Frito (Prato Principal)',
                        'description' => 'Carne suína temperada e empanada.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 53.00, 'order' => 1],
                            ['name' => 'Médio', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Arroz',
                'slug' => 'arroz',
                'description' => 'Porções de arroz frito yakimeshi e gohan tradicional.',
                'order' => 9,
                'items' => [
                    [
                        'code' => '93',
                        'name' => 'Yakimeshi Vegetariano',
                        'description' => 'Arroz branco japonês temperado com cenoura em cubos, ovo e cebolinha.',
                        'variations' => [
                            ['name' => 'Média', 'price' => 36.00, 'order' => 1],
                            ['name' => 'Pequena', 'price' => 26.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '89',
                        'name' => 'Yakimeshi Camarão',
                        'description' => 'Arroz branco japonês temperado com camarão, cenoura em cubos, ovo e cebolinha.',
                        'variations' => [
                            ['name' => 'Média', 'price' => 46.00, 'order' => 1],
                            ['name' => 'Pequena', 'price' => 37.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '88',
                        'name' => 'Yakimeshi Frango',
                        'description' => 'Arroz branco japonês temperado com lascas de frango, cenoura em cubos, ovo e cebolinha.',
                        'variations' => [
                            ['name' => 'Média', 'price' => 38.00, 'order' => 1],
                            ['name' => 'Pequena', 'price' => 30.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '87',
                        'name' => 'Yakimeshi Carne',
                        'description' => 'Arroz branco japonês temperado com carne bovina moída, cenoura em cubos, ovo e cebolinha.',
                        'variations' => [
                            ['name' => 'Média', 'price' => 40.00, 'order' => 1],
                            ['name' => 'Pequena', 'price' => 30.00, 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '86',
                        'name' => 'Gohan',
                        'description' => 'Arroz branco japonês cozido no ponto tradicional.',
                        'variations' => [
                            ['name' => 'Chawan', 'price' => 13.00, 'order' => 1],
                            ['name' => 'Média', 'price' => 35.00, 'order' => 2],
                            ['name' => 'Pequena', 'price' => 25.00, 'order' => 3],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Vegetariano',
                'slug' => 'vegetariano',
                'description' => 'Opções saudáveis e saborosas sem carne.',
                'order' => 10,
                'items' => [
                    [
                        'code' => '06',
                        'name' => 'Harumaki Vegetariano',
                        'description' => 'Recheio de repolho, cenoura e cebolinha. Acompanha molho vermelho agridoce.',
                        'variations' => [
                            ['name' => '5 Unidades', 'price' => 45.00, 'description' => '5 unidades', 'order' => 1],
                            ['name' => '3 Unidades', 'price' => 32.00, 'description' => '3 unidades', 'order' => 2],
                        ],
                    ],
                    [
                        'code' => '36',
                        'name' => 'Mix de Legumes',
                        'description' => 'Acelga, couve-flor, brócolis, cenoura, repolho, champignon, ovo de codorna. Finalizado com molho shoyu.',
                        'variations' => [
                            ['name' => 'Grande', 'price' => 47.00, 'order' => 1],
                        ],
                    ],
                ],
            ],
        ];

        $productCount = 0;
        $variationCount = 0;

        foreach ($categoriesData as $catData) {
            $category = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'order' => $catData['order'],
                    'is_active' => true,
                ]
            );

            $itemOrder = 1;
            foreach ($catData['items'] as $itemData) {
                // Generate a unique slug based on category and name or code
                $slugBase = $itemData['code']
                    ? Str::slug($catData['slug'] . '-' . $itemData['code'] . '-' . $itemData['name'])
                    : Str::slug($catData['slug'] . '-' . $itemData['name']);

                $product = Product::updateOrCreate(
                    ['slug' => $slugBase],
                    [
                        'category_id' => $category->id,
                        'code' => $itemData['code'],
                        'name' => $itemData['name'],
                        'description' => $itemData['description'],
                        'order' => $itemOrder++,
                        'is_active' => true,
                    ]
                );

                // Attach to category_product pivot table
                $product->categories()->syncWithoutDetaching([$category->id]);

                $productCount++;

                // If Yakimeshi Vegetariano or Yakisoba Vegetariano, attach to Vegetariano category as well
                if ($itemData['name'] === 'Yakimeshi Vegetariano' && $catData['slug'] === 'arroz') {
                    $vegCategory = Category::where('slug', 'vegetariano')->first();
                    if ($vegCategory) {
                        $product->categories()->syncWithoutDetaching([$vegCategory->id]);
                    }
                }

                // Add variations
                foreach ($itemData['variations'] as $varData) {
                    ProductVariation::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'name' => $varData['name'],
                        ],
                        [
                            'code' => $varData['code'] ?? null,
                            'price' => $varData['price'],
                            'description' => $varData['description'] ?? null,
                            'order' => $varData['order'] ?? 1,
                            'is_active' => true,
                        ]
                    );
                    $variationCount++;
                }
            }
        }

        // Secondary category linkages
        $vegCategory = Category::where('slug', 'vegetariano')->first();
        if ($vegCategory) {
            $yakisobaVeg = Product::where('code', '96')->first();
            if ($yakisobaVeg) {
                $yakisobaVeg->categories()->syncWithoutDetaching([$vegCategory->id]);
            }
        }
    }
}
