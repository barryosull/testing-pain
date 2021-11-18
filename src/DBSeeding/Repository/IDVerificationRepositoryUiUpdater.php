<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Repository;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;
use Barryosull\TestingPain\DBSeeding\Model\IDVerification;
use Barryosull\TestingPain\DBSeeding\Model\IDVerificationStatus;
use Barryosull\TestingPain\DBSeeding\Model\ShopFinder;

class IDVerificationRepositoryUiUpdater implements IDVerificationRepository
{
    private $id_verification_repository;
    private $shop_finder;
    private $card_factory;

    public function __construct(
        IDVerificationRepository $id_verification_repository,
        ShopFinder $shop_finder,
        CardFactory $card_factory
    )
    {
        $this->id_verification_repository = $id_verification_repository;
        $this->shop_finder = $shop_finder;
        $this->card_factory = $card_factory;
    }

    public function store(IDVerification $id_verification): void
    {
        $this->id_verification_repository->store($id_verification);

        $shop = $this->shop_finder->find($id_verification->shop_id);

        $card = $this->card_factory->makeVerificationFailedCard($id_verification->shop_id);

        if ($id_verification->verification_status === IDVerificationStatus::FAILED) {
            $card->createForShop($shop);
        } else {
            $card->markAsAddressed($shop);
        }
    }
}
