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
            ->addOption('items', 'i', InputOption::VALUE_OPTIONAL, 'Nombre d\'articles à créer', 20)
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Mot de passe commun pour tous les users', 'password')
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'Vider les tables avant de générer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbUsers    = max(1, (int) $input->getOption('users'));
        $nbItems    = max(0, (int) $input->getOption('items'));
        $password   = (string) $input->getOption('password');
        $clear      = (bool)   $input->getOption('clear');

        // ── Vider les tables ─────────────────────────────────────────────────
        if ($clear) {
            $io->warning('Suppression des données existantes…');
            $this->em->createQuery('DELETE FROM App\Entity\Item')->execute();
            $this->em->createQuery('DELETE FROM App\Entity\User')->execute();
            $io->text('Tables vidées.');
        }

        $io->title('Génération des fixtures');
        $io->text(sprintf(
            'Création de <info>%d utilisateur(s)</info> et <info>%d article(s)</info>…',
            $nbUsers, $nbItems
        ));

        // ── Utilisateurs ─────────────────────────────────────────────────────
        $users = [];

        for ($i = 1; $i <= $nbUsers; $i++) {
            $user = new User();
            $user->setEmail(sprintf('user%d@playmate.dev', $i));
            $user->setUsername(sprintf('%s%d', $this->randomUsername(), $i));
            $user->setFirstname($this->randomFirstname());
            $user->setLastname($this->randomLastname());
            $user->setPassword($this->hasher->hashPassword($user, $password));
            $user->setIsVerified(true);

            // ~20 % d'admins
            if ($i === 1 || random_int(1, 5) === 1) {
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            $this->em->persist($user);
            $users[] = $user;
        }

        $this->em->flush();
        $io->text(sprintf('  ✓ %d utilisateur(s) créé(s)', $nbUsers));

        // ── Articles ─────────────────────────────────────────────────────────
        $categories = ['carte', 'booster', 'display', 'autre'];
        $conditions = ['neuf', 'tres_bon', 'bon', 'correct'];

        for ($i = 1; $i <= $nbItems; $i++) {
            $item = new Item();
            $item->setName($this->randomItemName());
            $item->setPrice((string) (random_int(100, 50000) / 100));
            $item->setDescription($this->randomDescription());
            $item->setCategory($categories[array_rand($categories)]);
            $item->setCondition($conditions[array_rand($conditions)]);
            $item->setIsSold(random_int(0, 4) === 0); // ~20 % vendus
            $item->setImages([]);                      // pas de fichiers réels
            $item->setOwner($users[array_rand($users)]);

            $this->em->persist($item);

            // Flush par lots pour économiser la mémoire
            if ($i % 50 === 0) {
                $this->em->flush();
                $this->em->clear(Item::class);
            }
        }

        $this->em->flush();
        $io->text(sprintf('  ✓ %d article(s) créé(s)', $nbItems));

        // ── Résumé ────────────────────────────────────────────────────────────
        $io->success('Fixtures générées avec succès !');

        $io->table(
            ['', 'Valeur'],
            [
                ['Utilisateurs créés', $nbUsers],
                ['Articles créés',     $nbItems],
                ['Email',              'user1@playmate.dev … user' . $nbUsers . '@playmate.dev'],
                ['Mot de passe',       $password],
                ['Admin',              'user1@playmate.dev (+ ~20 % aléatoires)'],
            ]
        );

        return Command::SUCCESS;
    }

    // ── Données aléatoires ────────────────────────────────────────────────────

    private function randomUsername(): string
    {
        $names = ['Trainer', 'Collector', 'Master', 'Hunter', 'Champion', 'Dealer', 'Player', 'Seeker'];
        return $names[array_rand($names)];
    }

    private function randomFirstname(): string
    {
        $names = ['Alice', 'Bob', 'Clara', 'David', 'Emma', 'Félix', 'Gabriel', 'Hélène',
            'Inès', 'Jules', 'Karim', 'Laura', 'Marc', 'Nina', 'Oscar', 'Paula'];
        return $names[array_rand($names)];
    }

    private function randomLastname(): string
    {
        $names = ['Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit',
            'Durand', 'Leroy', 'Moreau', 'Simon', 'Laurent', 'Lefebvre', 'Michel'];
        return $names[array_rand($names)];
    }

    private function randomItemName(): string
    {
        $prefixes = ['Dracaufeu', 'Pikachu', 'Mewtwo', 'Lugia', 'Rayquaza', 'Arceus',
            'Tortank', 'Florizarre', 'Ronflex', 'Évoli', 'Artikodin', 'Electhor'];
        $suffixes = ['EX', 'GX', 'V', 'VMAX', 'VSTAR', 'Full Art', 'Rainbow Rare',
            'Holo', 'Reverse Holo', 'Secret Rare', 'Gold'];
        $types    = ['Carte', 'Booster', 'Display', 'Coffret', 'Promo'];

        $type = $types[array_rand($types)];

        if ($type === 'Carte') {
            return sprintf('%s %s', $prefixes[array_rand($prefixes)], $suffixes[array_rand($suffixes)]);
        }

        $sets = ['Écarlate et Violet', 'Évolutions Prismatiques', 'Obsidian Flames',
            'Paldea Evolved', 'Crown Zenith', 'Silver Tempest', 'Lost Origin'];

        return sprintf('%s %s', $type, $sets[array_rand($sets)]);
    }

    private function randomDescription(): ?string
    {
        // ~30 % sans description
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
}
