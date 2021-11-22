<?php

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;

class IDVerificationStorer
{
    private $shop_finder;
    private $card_factory;

    public function __construct(ShopFinder $shop_finder, CardFactory $card_factory)
    {
        $this->shop_finder = $shop_finder;
        $this->card_factory = $card_factory;
    }

    public function store(IDVerification $id_verification): void
    {
        $id_verification->store();

        $this->updateUi($id_verification);
    }

    private function updateUi(IDVerification $id_verification)
    {
        $shop = $this->shop_finder->find($id_verification->shop_id);

        $card = $this->card_factory->makeVerificationFailedCard($shop->shop_id);

        if ($id_verification->verification_status === IDVerificationStatus::FAILED) {
            $card->createForShop($shop);
        } else {
            $card->markAsAddressed($shop);
        }
    }
}
