<?php

namespace Barryosull\TestingPain\DBSeeding\Model;

use Barryosull\TestingPain\DBSeeding\AdvisoryCard\CardFactory;

class VerificationCodeStorer
{
    private $account_finder;
    private $card_factory;

    public function __construct(AccountFinder $account_finder, CardFactory $card_factory)
    {
        $this->account_finder = $account_finder;
        $this->card_factory = $card_factory;
    }

    public function store(VerificationCode $verification_code): void
    {
        $verification_code->store();

        $this->updateUi($verification_code);
    }

    private function updateUi(VerificationCode $id_verification)
    {
        $account = $this->account_finder->find($id_verification->account_id);

        $card = $this->card_factory->makeVerificationFailedCard($account->account_id);

        if ($id_verification->verification_status === VerificationStatus::FAILED) {
            $card->createForAccount($account);
        } else {
            $card->markAsAddressed($account);
        }
    }
}
