<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Entity\Review;
use Soosuuke\IaPlatform\Repository\ReviewRepository;
use Soosuuke\IaPlatform\Repository\ClientRepository;
use Soosuuke\IaPlatform\Repository\ProviderRepository;

class ReviewFixtures
{
    public function load(): void
    {
        $reviewRepo = new ReviewRepository();
        $clientRepo = new ClientRepository();
        $providerRepo = new ProviderRepository();
        $clients = $clientRepo->findAll();
        $providers = $providerRepo->findAll();

        $reviewContents = [
            ['comment' => 'Excellent travail ! Très professionnel et livré dans les temps.', 'rating' => 5],
            ['comment' => 'Bon service, quelques ajustements nécessaires mais satisfait.', 'rating' => 4],
            ['comment' => 'Service de qualité exceptionnelle. Communication parfaite.', 'rating' => 5],
            ['comment' => 'Travail correct mais pas exceptionnel. Délais respectés.', 'rating' => 3],
            ['comment' => 'Prestation au-delà de mes attentes ! Très créatif.', 'rating' => 5],
            ['comment' => 'Bon rapport qualité-prix. Service fiable.', 'rating' => 4],
            ['comment' => 'Collaboration fluide et résultats impressionnants.', 'rating' => 5],
            ['comment' => 'Service décevant, plusieurs révisions nécessaires.', 'rating' => 2],
            ['comment' => 'Excellent suivi client et solution adaptée.', 'rating' => 5],
            ['comment' => 'Prestation satisfaisante, quelques améliorations possibles.', 'rating' => 3],
            ['comment' => 'Travail remarquable ! Expertise au rendez-vous.', 'rating' => 5],
            ['comment' => 'Bonne communication et livraison en temps voulu.', 'rating' => 4],
            ['comment' => 'Service professionnel avec attention aux détails.', 'rating' => 4],
            ['comment' => 'Expérience mitigée. Résultat correct.', 'rating' => 3],
            ['comment' => 'Parfait ! Dépassement de mes attentes.', 'rating' => 5],
            ['comment' => 'Bon travail, communication claire.', 'rating' => 4],
            ['comment' => 'Service de haute qualité avec approche personnalisée.', 'rating' => 5],
            ['comment' => 'Prestation correcte sans plus.', 'rating' => 3]
        ];

        $reviewIndex = 0;
        $totalReviews = 0;

        foreach ($providers as $provider) {
            foreach ($clients as $client) {
                $reviewData = $reviewContents[$reviewIndex % count($reviewContents)];

                $review = new Review(
                    $client->getId(),
                    $provider->getId(),
                    $reviewData['comment'],
                    $reviewData['rating']
                );

                $reviewRepo->save($review);
                $totalReviews++;
                $reviewIndex++;
            }
        }

        echo "✔ $totalReviews reviews créées\n";
    }
}
