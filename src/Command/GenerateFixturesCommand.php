<?php

namespace App\Command;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:fixtures:generate',
    description: 'Génère des utilisateurs et des articles fictifs pour le développement.',
)]
class GenerateFixturesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('users', 'u', InputOption::VALUE_OPTIONAL, 'Nombre d\'utilisateurs à créer', 5)
            ->addOption('items', 'i', InputOption::VALUE_OPTIONAL, 'Nombre total d\'articles à créer', 20)
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Mot de passe commun pour tous les utilisateurs', 'password')
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'Vider les tables avant de générer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbUsers = max(1, (int) $input->getOption('users'));
        $nbItems = max(0, (int) $input->getOption('items'));
        $password = (string) $input->getOption('password');
        $clear = (bool) $input->getOption('clear');

        if ($password === '') {
            $io->error('Le mot de passe ne peut pas être vide.');

            return Command::INVALID;
        }

        if ($clear) {
            $io->warning('Suppression des données existantes...');
            $this->em->createQuery('DELETE FROM App\Entity\Item i')->execute();
            $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
            $this->em->clear();
            $io->text('Tables vidées.');
        }

        $io->title('Génération des fixtures');
        $io->text(sprintf(
            'Création de %d utilisateur(s) et jusqu’à %d article(s)...',
            $nbUsers,
            $nbItems
        ));

        $users = [];

        for ($i = 1; $i <= $nbUsers; $i++) {
            $user = new User();
            $user->setEmail(sprintf('user%d@playmate.dev', $i));
            $user->setUsername(sprintf('%s%d', strtolower($this->randomUsername()), $i));
            $user->setFirstname($this->randomFirstname());
            $user->setLastname($this->randomLastname());
            $user->setPassword($this->hasher->hashPassword($user, $password));
            $user->setIsVerified(true);
            $user->setAddress($this->randomAddress());

            if ($i === 1 || random_int(1, 5) === 1) {
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $this->em->persist($user);
            $users[] = $user;
        }

        $this->em->flush();

        $io->text(sprintf('✓ %d utilisateur(s) créé(s)', $nbUsers));

        $fixedItems = $this->getFixedItems();
        $createdItems = 0;

        foreach (array_slice($fixedItems, 0, $nbItems) as $index => $data) {
            $item = new Item();
            $item
                ->setName($data['name'])
                ->setDescription($data['description'])
                ->setPrice($data['price'])
                ->setCreatedAt(new \DateTimeImmutable($data['createdAt']))
                ->setIsSold($data['isSold'])
                ->setImages($data['images'])
                ->setCategory($data['category'])
                ->setCondition($data['condition'])
                ->setOwner($users[$index % count($users)]);

            $this->em->persist($item);
            ++$createdItems;
        }

        $remaining = $nbItems - $createdItems;

        $categories = ['carte', 'booster', 'display', 'autre'];
        $conditions = ['neuf', 'tres_bon', 'bon', 'correct'];
        $imagePool = $this->getExistingImagePool();

        for ($i = 1; $i <= $remaining; $i++) {
            $item = new Item();
            $item->setName($this->randomItemName());
            $item->setPrice(number_format(random_int(100, 50000) / 100, 2, '.', ''));
            $item->setDescription($this->randomDescription());
            $item->setCategory($categories[array_rand($categories)]);
            $item->setCondition($conditions[array_rand($conditions)]);
            $item->setIsSold(random_int(0, 4) === 0);
            $item->setImages([$imagePool[array_rand($imagePool)]]);
            $item->setOwner($users[array_rand($users)]);

            $this->em->persist($item);

            if ($i % 50 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();

        $io->text(sprintf('✓ %d article(s) créé(s)', $nbItems));
        $io->success('Fixtures générées avec succès !');

        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Utilisateurs créés', (string) $nbUsers],
                ['Articles créés', (string) $nbItems],
                ['Articles fixes injectés', (string) min(count($fixedItems), $nbItems)],
                ['Emails', sprintf('user1@playmate.dev à user%d@playmate.dev', $nbUsers)],
                ['Mot de passe', $password],
                ['Admin principal', 'user1@playmate.dev'],
                ['Admins supplémentaires', '~20 % aléatoires'],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     description: string,
     *     price: string,
     *     createdAt: string,
     *     isSold: bool,
     *     images: array<int, string>,
     *     category: string,
     *     condition: string
     * }>
     */
    private function getFixedItems(): array
    {
        return [
            [
                'name' => 'Black Lotus - Édition Collector',
                'description' => 'Carte mythique très recherchée, protégée sous sleeve rigide.',
                'price' => '12500.00',
                'createdAt' => '-1 day',
                'isSold' => false,
                'images' => ['images/items/black-lotus.jpg'],
                'category' => 'carte',
                'condition' => 'neuf',
            ],
            [
                'name' => 'Mox Emerald',
                'description' => 'Très bel état général, parfaite pour collection haut de gamme.',
                'price' => '4200.00',
                'createdAt' => '-2 days',
                'isSold' => false,
                'images' => ['images/items/mox-emerald.jpg'],
                'category' => 'carte',
                'condition' => 'tres_bon',
            ],
            [
                'name' => 'Time Walk',
                'description' => 'Carte iconique vintage, idéalement conservée.',
                'price' => '3900.00',
                'createdAt' => '-3 days',
                'isSold' => false,
                'images' => ['images/items/time-walk.jpg'],
                'category' => 'carte',
                'condition' => 'tres_bon',
            ],
            [
                'name' => 'Underground Sea',
                'description' => 'Dual land indispensable pour formats éternels.',
                'price' => '850.00',
                'createdAt' => '-4 days',
                'isSold' => false,
                'images' => ['images/items/underground-sea.jpg'],
                'category' => 'carte',
                'condition' => 'bon',
            ],
            [
                'name' => 'Volcanic Island',
                'description' => 'Très belle carte legacy, couleurs intenses.',
                'price' => '790.00',
                'createdAt' => '-5 days',
                'isSold' => false,
                'images' => ['images/items/volcanic-island.jpg'],
                'category' => 'carte',
                'condition' => 'bon',
            ],
            [
                'name' => 'Force of Will',
                'description' => 'Staple bleu en excellent état, très recherchée.',
                'price' => '95.00',
                'createdAt' => '-6 days',
                'isSold' => false,
                'images' => ['images/items/force-of-will.jpg'],
                'category' => 'carte',
                'condition' => 'tres_bon',
            ],
            [
                'name' => 'Mana Drain',
                'description' => 'Contresort premium pour collectionneur averti.',
                'price' => '180.00',
                'createdAt' => '-7 days',
                'isSold' => false,
                'images' => ['images/items/mana-drain.jpg'],
                'category' => 'carte',
                'condition' => 'tres_bon',
            ],
            [
                'name' => 'Demonic Tutor',
                'description' => 'Tutor noir culte, état très propre.',
                'price' => '210.00',
                'createdAt' => '-8 days',
                'isSold' => false,
                'images' => ['images/items/demonic-tutor.jpg'],
                'category' => 'carte',
                'condition' => 'tres_bon',
            ],
            [
                'name' => 'Snapcaster Mage',
                'description' => 'Classique moderne, parfait pour deck ou collection.',
                'price' => '28.00',
                'createdAt' => '-9 days',
                'isSold' => false,
                'images' => ['images/items/snapcaster-mage.jpg'],
                'category' => 'carte',
                'condition' => 'bon',
            ],
            [
                'name' => 'Liliana of the Veil',
                'description' => 'Planeswalker emblématique en très bon état.',
                'price' => '42.00',
                'createdAt' => '-10 days',
                'isSold' => false,
                'images' => ['images/items/liliana-of-the-veil.jpg'],
                'category' => 'carte',
                'condition' => 'bon',
            ],
            [
                'name' => 'Tarmogoyf',
                'description' => 'Créature emblématique, usure légère sur les bords.',
                'price' => '24.00',
                'createdAt' => '-11 days',
                'isSold' => false,
                'images' => ['images/items/tarmogoyf.jpg'],
                'category' => 'carte',
                'condition' => 'correct',
            ],
            [
                'name' => 'Jace, the Mind Sculptor',
                'description' => 'Version très propre, idéale pour vitrine.',
                'price' => '65.00',
                'createdAt' => '-12 days',
                'isSold' => true,
                'images' => ['images/items/jace-the-mind-sculptor.jpg'],
                'category' => 'carte',
                'condition' => 'tres_bon',
            ],
            [
                'name' => 'Polluted Delta',
                'description' => 'Fetch land très joué, bon état général.',
                'price' => '18.00',
                'createdAt' => '-13 days',
                'isSold' => false,
                'images' => ['images/items/polluted-delta.jpg'],
                'category' => 'carte',
                'condition' => 'bon',
            ],
            [
                'name' => 'Breeding Pool',
                'description' => 'Shock land polyvalent pour plusieurs archétypes.',
                'price' => '14.00',
                'createdAt' => '-14 days',
                'isSold' => true,
                'images' => ['images/items/breeding-pool.jpg'],
                'category' => 'carte',
                'condition' => 'bon',
            ],
        ];
    }

    private function randomUsername(): string
    {
        $names = ['Trainer', 'Collector', 'Master', 'Hunter', 'Champion', 'Dealer', 'Player', 'Seeker'];

        return $names[array_rand($names)];
    }

    private function randomFirstname(): string
    {
        $names = [
            'Alice',
            'Bob',
            'Clara',
            'David',
            'Emma',
            'Félix',
            'Gabriel',
            'Hélène',
            'Inès',
            'Jules',
            'Karim',
            'Laura',
            'Marc',
            'Nina',
            'Oscar',
            'Paula',
        ];

        return $names[array_rand($names)];
    }

    private function randomLastname(): string
    {
        $names = [
            'Martin',
            'Bernard',
            'Dubois',
            'Thomas',
            'Robert',
            'Richard',
            'Petit',
            'Durand',
            'Leroy',
            'Moreau',
            'Simon',
            'Laurent',
            'Lefebvre',
            'Michel',
        ];

        return $names[array_rand($names)];
    }

    private function randomItemName(): string
    {
        $prefixes = [
            'Dracaufeu',
            'Pikachu',
            'Mewtwo',
            'Lugia',
            'Rayquaza',
            'Arceus',
            'Tortank',
            'Florizarre',
            'Ronflex',
            'Évoli',
            'Artikodin',
            'Electhor',
        ];

        $suffixes = [
            'EX',
            'GX',
            'V',
            'VMAX',
            'VSTAR',
            'Full Art',
            'Rainbow Rare',
            'Holo',
            'Reverse Holo',
            'Secret Rare',
            'Gold',
        ];

        $types = ['Carte', 'Booster', 'Display', 'Coffret', 'Promo'];

        $type = $types[array_rand($types)];

        if ($type === 'Carte') {
            return sprintf(
                '%s %s',
                $prefixes[array_rand($prefixes)],
                $suffixes[array_rand($suffixes)]
            );
        }

        $sets = [
            'Écarlate et Violet',
            'Évolutions Prismatiques',
            'Obsidian Flames',
            'Paldea Evolved',
            'Crown Zenith',
            'Silver Tempest',
            'Lost Origin',
        ];

        return sprintf('%s %s', $type, $sets[array_rand($sets)]);
    }

    private function randomDescription(): ?string
    {
        if (random_int(1, 10) <= 3) {
            return null;
        }

        $intros = [
            'Très belle carte en excellent état.',
            'Article issu de ma collection personnelle.',
            'Jamais joué, conservé sous sleeve et toploader.',
            'Légères traces d\'usure sur les bords, visibles en photo.',
            'Carte en parfait état, jamais sortie de son emballage.',
        ];

        $extras = [
            ' Envoi soigné en recommandé.',
            ' Possibilité de lot, contactez-moi.',
            ' Prix ferme, pas d\'échange.',
            ' Open à la négociation raisonnable.',
            '',
        ];

        return $intros[array_rand($intros)] . $extras[array_rand($extras)];
    }

    /**
     * @return array<int, string>
     */
    private function getExistingImagePool(): array
    {
        $images = [];

        foreach ($this->getFixedItems() as $item) {
            foreach ($item['images'] as $image) {
                $images[] = $image;
            }
        }

        return array_values(array_unique($images));
    }

    private function randomAddress(): string
    {
        $addresses = [
            "3 rue du Soleil, 55600 Lons",
            "782 Avenue de la Libération, 64230 Lescar",
            "1 Place Royale, 44000 Nantes",
            "6 rue Wilfrid Voynich, 14141 Prage"
        ];
        return $addresses[array_rand($addresses)];
    }
}
