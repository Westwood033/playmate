<?php

namespace App\DataFixtures;

use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $items = [
            [
                'name' => 'Black Lotus - Édition Collector',
                'description' => 'Carte mythique très recherchée, protégée sous sleeve rigide.',
                'price' => '12500.00',
                'dateCreated' => '-1 day',
                'isSold' => false,
                'images' => ['/images/items/black-lotus.jpg'],
            ],
            [
                'name' => 'Mox Emerald',
                'description' => 'Très bel état général, parfaite pour collection haut de gamme.',
                'price' => '4200.00',
                'dateCreated' => '-2 days',
                'isSold' => false,
                'images' => ['/images/items/mox-emerald.jpg'],
            ],
            [
                'name' => 'Time Walk',
                'description' => 'Carte iconique vintage, idéalement conservée.',
                'price' => '3900.00',
                'dateCreated' => '-3 days',
                'isSold' => false,
                'images' => ['/images/items/time-walk.jpg'],
            ],
            [
                'name' => 'Underground Sea',
                'description' => 'Dual land indispensable pour formats éternels.',
                'price' => '850.00',
                'dateCreated' => '-4 days',
                'isSold' => false,
                'images' => ['/images/items/underground-sea.jpg'],
            ],
            [
                'name' => 'Volcanic Island',
                'description' => 'Très belle carte legacy, couleurs intenses.',
                'price' => '790.00',
                'dateCreated' => '-5 days',
                'isSold' => false,
                'images' => ['/images/items/volcanic-island.jpg'],
            ],
            [
                'name' => 'Force of Will',
                'description' => 'Staple bleu en excellent état, très recherchée.',
                'price' => '95.00',
                'dateCreated' => '-6 days',
                'isSold' => false,
                'images' => ['/images/items/force-of-will.jpg'],
            ],
            [
                'name' => 'Mana Drain',
                'description' => 'Contresort premium pour collectionneur averti.',
                'price' => '180.00',
                'dateCreated' => '-7 days',
                'isSold' => false,
                'images' => ['/images/items/mana-drain.jpg'],
            ],
            [
                'name' => 'Demonic Tutor',
                'description' => 'Tutor noir culte, état très propre.',
                'price' => '210.00',
                'dateCreated' => '-8 days',
                'isSold' => false,
                'images' => ['/images/items/demonic-tutor.jpg'],
            ],
            [
                'name' => 'Snapcaster Mage',
                'description' => 'Classique moderne, parfait pour deck ou collection.',
                'price' => '28.00',
                'dateCreated' => '-9 days',
                'isSold' => false,
                'images' => ['/images/items/snapcaster-mage.jpg'],
            ],
            [
                'name' => 'Liliana of the Veil',
                'description' => 'Planeswalker emblématique en très bon état.',
                'price' => '42.00',
                'dateCreated' => '-10 days',
                'isSold' => false,
                'images' => ['/images/items/liliana-of-the-veil.jpg'],
            ],
            [
                'name' => 'Tarmogoyf',
                'description' => 'Créature emblématique, usure légère sur les bords.',
                'price' => '24.00',
                'dateCreated' => '-11 days',
                'isSold' => false,
                'images' => ['/images/items/tarmogoyf.jpg'],
            ],
            [
                'name' => 'Jace, the Mind Sculptor',
                'description' => 'Version très propre, idéale pour vitrine.',
                'price' => '65.00',
                'dateCreated' => '-12 days',
                'isSold' => true,
                'images' => ['/images/items/jace-the-mind-sculptor.jpg'],
            ],
            [
                'name' => 'Polluted Delta',
                'description' => 'Fetch land très joué, bon état général.',
                'price' => '18.00',
                'dateCreated' => '-13 days',
                'isSold' => false,
                'images' => ['/images/items/polluted-delta.jpg'],
            ],
            [
                'name' => 'Breeding Pool',
                'description' => 'Shock land polyvalent pour plusieurs archétypes.',
                'price' => '14.00',
                'dateCreated' => '-14 days',
                'isSold' => true,
                'images' => ['/images/items/breeding-pool.jpg'],
            ],
        ];

        foreach ($items as $data) {
            $item = new Item();
            $item
                ->setName($data['name'])
                ->setDescription($data['description'])
                ->setPrice($data['price'])
                ->setDateCreated(new \DateTimeImmutable($data['dateCreated']))
                ->setIsSold($data['isSold'])
                ->setImages($data['images']);

            $manager->persist($item);
        }

        $manager->flush();
    }
}